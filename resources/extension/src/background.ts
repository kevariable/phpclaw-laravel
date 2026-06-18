/**
 * PHPClaw Browser Control - Service Worker (thin layer)
 *
 * The content script handles all polling and DOM operations.
 * This service worker ONLY handles:
 * - Tab-level chrome.tabs/scripting API calls (navigate, new_tab, screenshot, etc.)
 * - Startup: telling content scripts to begin polling
 * - Popup connect/disconnect
 *
 * It can safely be suspended — the content script keeps running.
 */

const DEFAULT_SERVER = 'http://localhost:8000';

// ─── Startup ─────────────────────────────────────────────────────────────────

chrome.runtime.onInstalled.addListener(() => {
  chrome.storage.local.get(['serverUrl'], (data) => {
    if (!data.serverUrl) chrome.storage.local.set({ serverUrl: DEFAULT_SERVER });
  });
});

chrome.runtime.onStartup.addListener(() => restorePolling());
restorePolling();

async function restorePolling() {
  const data = await chrome.storage.local.get(['polling']);
  updateBadge(false);
  if (data.polling) {
    // Tell all content scripts to start polling
    broadcastToContentScripts('phpclaw-start');
  }
}

// ─── Start / Stop ────────────────────────────────────────────────────────────

async function startPolling() {
  const data = await chrome.storage.local.get(['serverUrl', 'apiToken']);
  await chrome.storage.local.set({ polling: true, connected: false, lastPoll: null, lastError: null });
  chrome.action.setBadgeText({ text: '...' });
  chrome.action.setBadgeBackgroundColor({ color: '#FFA500' });

  const config = {
    serverUrl: data.serverUrl || DEFAULT_SERVER,
    apiToken: data.apiToken || '',
  };

  // Inject content script into any viable tab and start it.
  // Content scripts only auto-inject on page load, so existing tabs need manual injection.
  const tabs = await chrome.tabs.query({});
  let injected = false;

  for (const tab of tabs) {
    if (!tab.url || tab.url.startsWith('chrome://') || tab.url.startsWith('chrome-extension://') || tab.url.startsWith('about:')) continue;
    try {
      await chrome.scripting.executeScript({ target: { tabId: tab.id }, files: ['content.js'] });
      await chrome.tabs.sendMessage(tab.id, { type: 'phpclaw-start', ...config });
      injected = true;
      break; // Only need one tab polling
    } catch (e) {}
  }

  if (!injected) {
    // No viable tabs — create one
    const tab = await chrome.tabs.create({ url: 'https://www.google.com', active: false });
    // Wait for page load so content script auto-injects
    await new Promise((resolve) => {
      const listener = (tabId, info) => {
        if (tabId === tab.id && info.status === 'complete') {
          chrome.tabs.onUpdated.removeListener(listener);
          resolve();
        }
      };
      chrome.tabs.onUpdated.addListener(listener);
      setTimeout(() => { chrome.tabs.onUpdated.removeListener(listener); resolve(); }, 10000);
    });
    chrome.tabs.sendMessage(tab.id, { type: 'phpclaw-start', ...config }).catch(() => {});
  }
}

async function stopPolling() {
  await chrome.storage.local.set({ polling: false, connected: false });
  broadcastToContentScripts('phpclaw-stop');
  updateBadge(false);
}

async function broadcastToContentScripts(type, extra = {}) {
  const tabs = await chrome.tabs.query({});
  for (const tab of tabs) {
    if (tab.url && !tab.url.startsWith('chrome://') && !tab.url.startsWith('chrome-extension://')) {
      chrome.tabs.sendMessage(tab.id, { type, ...extra }).catch(() => {});
    }
  }
}

function updateBadge(connected) {
  if (connected) {
    chrome.action.setBadgeText({ text: 'ON' });
    chrome.action.setBadgeBackgroundColor({ color: '#00D464' });
  } else {
    chrome.storage.local.get(['polling'], (data) => {
      if (data.polling) {
        chrome.action.setBadgeText({ text: '...' });
        chrome.action.setBadgeBackgroundColor({ color: '#FFA500' });
      } else {
        chrome.action.setBadgeText({ text: '' });
      }
    });
  }
}

// ─── Controlled tab tracking ─────────────────────────────────────────────────

let controlledTabId = null;

chrome.storage.local.get(['controlledTabId'], (data) => {
  if (data.controlledTabId) controlledTabId = data.controlledTabId;
});

function setControlledTab(tabId) {
  if (controlledTabId && controlledTabId !== tabId) {
    chrome.tabs.sendMessage(controlledTabId, { type: 'phpclaw-release' }).catch(() => {});
  }
  controlledTabId = tabId;
  chrome.storage.local.set({ controlledTabId: tabId });
}

chrome.tabs.onRemoved.addListener((tabId) => {
  if (tabId === controlledTabId) {
    controlledTabId = null;
    chrome.storage.local.remove('controlledTabId');
  }
});

// ─── Message handling ────────────────────────────────────────────────────────

chrome.runtime.onMessage.addListener((msg, sender, sendResponse) => {
  // Popup: connect
  if (msg.type === 'connect') {
    startPolling().then(() => sendResponse({ ok: true }));
    return true;
  }
  // Popup: disconnect
  if (msg.type === 'disconnect') {
    stopPolling().then(() => sendResponse({ ok: true }));
    return true;
  }
  // Popup: get controlled tab info
  if (msg.type === 'getControlledTab') {
    if (controlledTabId) {
      chrome.tabs.get(controlledTabId).then(tab => {
        sendResponse({ tabId: tab.id, title: tab.title, url: tab.url });
      }).catch(() => sendResponse({ tabId: null }));
    } else {
      sendResponse({ tabId: null });
    }
    return true;
  }
  // Content script: am I the controlled tab? Should I poll?
  if (msg.type === 'amIControlled') {
    chrome.storage.local.get(['polling', 'serverUrl', 'apiToken'], (data) => {
      const tabId = sender.tab?.id;
      // Poll if: polling is on AND (this is the controlled tab OR no controlled tab is set)
      const shouldPoll = data.polling && (tabId === controlledTabId || !controlledTabId);
      if (shouldPoll && !controlledTabId) {
        // Claim this tab as controlled
        controlledTabId = tabId;
        chrome.storage.local.set({ controlledTabId: tabId });
      }
      sendResponse({
        shouldPoll,
        serverUrl: data.serverUrl || DEFAULT_SERVER,
        apiToken: data.apiToken || '',
      });
    });
    return true;
  }
  // Content script: tab-level action
  if (msg.type === 'tabAction') {
    handleTabAction(msg.action, msg.args, sender.tab).then(sendResponse).catch(e => {
      sendResponse({ success: false, error: e.message });
    });
    return true;
  }
  // Content script: connection status update
  if (msg.type === 'pollStatus') {
    chrome.storage.local.set({ connected: msg.connected, lastPoll: Date.now(), lastError: msg.error || null });
    updateBadge(msg.connected);
    return;
  }
});

// Watch for connection state changes to update badge
chrome.storage.onChanged.addListener((changes) => {
  if (changes.connected) updateBadge(changes.connected.newValue);
});

// ─── Tab-level actions ───────────────────────────────────────────────────────

async function handleTabAction(action, args, senderTab) {
  switch (action) {
    case 'navigate': return await tabNavigate(args, senderTab);
    case 'new_tab': return await tabNewTab(args);
    case 'close_tab': return await tabCloseTab(args);
    case 'switch_tab': return await tabSwitchTab(args);
    case 'get_tabs': return await tabGetTabs();
    case 'go_back': return await tabGoBack(senderTab);
    case 'go_forward': return await tabGoForward(senderTab);
    case 'reload': return await tabReload(senderTab);
    case 'screenshot': return await tabScreenshot(senderTab);
    case 'get_cookies': return await tabGetCookies(args, senderTab);
    case 'get_url': return tabGetUrl(senderTab);
    case 'release': return tabRelease();
    default: return { success: false, error: `Unknown tab action: ${action}` };
  }
}

function tabGetUrl(senderTab) {
  return { success: true, data: { url: senderTab?.url || '', title: senderTab?.title || '' } };
}

async function tabNavigate(args, senderTab) {
  const tabId = controlledTabId || senderTab?.id;
  if (!tabId) return { success: false, error: 'No tab to navigate' };
  await chrome.tabs.update(tabId, { url: args.url });
  setControlledTab(tabId);
  // Don't wait for load — the content script on the new page will start polling
  return { success: true, data: { url: args.url, navigating: true } };
}

async function tabNewTab(args) {
  const tab = await chrome.tabs.create({ url: args.url || 'about:blank', active: true });
  setControlledTab(tab.id);
  // Don't wait for load — content script will start on the new page
  return { success: true, data: { id: tab.id, url: args.url || 'about:blank' } };
}

async function tabCloseTab(args) {
  const tabId = args.tab_id || controlledTabId;
  if (!tabId) return { success: false, error: 'No tab to close' };
  if (tabId === controlledTabId) { controlledTabId = null; chrome.storage.local.remove('controlledTabId'); }
  await chrome.tabs.remove(tabId);
  return { success: true, data: { closed: tabId } };
}

async function tabSwitchTab(args) {
  setControlledTab(args.tab_id);
  const tab = await chrome.tabs.get(args.tab_id);
  chrome.tabs.sendMessage(tab.id, { type: 'phpclaw-active' }).catch(() => {});
  return { success: true, data: { id: tab.id, url: tab.url, title: tab.title } };
}

async function tabGetTabs() {
  const tabs = await chrome.tabs.query({});
  return { success: true, data: { tabs: tabs.map(t => ({ id: t.id, url: t.url, title: t.title, active: t.active })), count: tabs.length } };
}

async function tabGoBack(senderTab) {
  const tabId = controlledTabId || senderTab?.id;
  if (tabId) await chrome.tabs.goBack(tabId);
  return { success: true, data: { action: 'back' } };
}

async function tabGoForward(senderTab) {
  const tabId = controlledTabId || senderTab?.id;
  if (tabId) await chrome.tabs.goForward(tabId);
  return { success: true, data: { action: 'forward' } };
}

async function tabReload(senderTab) {
  const tabId = controlledTabId || senderTab?.id;
  if (tabId) await chrome.tabs.reload(tabId);
  return { success: true, data: { action: 'reload' } };
}

async function tabScreenshot(senderTab) {
  const tabId = controlledTabId || senderTab?.id;
  if (!tabId) return { success: false, error: 'No tab' };
  const tab = await chrome.tabs.get(tabId);
  const dataUrl = await chrome.tabs.captureVisibleTab(tab.windowId, { format: 'png' });
  return { success: true, data: { image: dataUrl, format: 'png' } };
}

async function tabGetCookies(args, senderTab) {
  const url = args.url || senderTab?.url;
  if (!url) return { success: false, error: 'No URL' };
  const cookies = await chrome.cookies.getAll({ url });
  return { success: true, data: { cookies: cookies.map(c => ({ name: c.name, value: c.value, domain: c.domain })), count: cookies.length } };
}

function tabRelease() {
  if (controlledTabId) {
    chrome.tabs.sendMessage(controlledTabId, { type: 'phpclaw-release' }).catch(() => {});
    controlledTabId = null;
    chrome.storage.local.remove('controlledTabId');
  }
  return { success: true, data: { released: true } };
}
