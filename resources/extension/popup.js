const serverUrlInput = document.getElementById('serverUrl');
const apiTokenInput = document.getElementById('apiToken');
const toggleBtn = document.getElementById('toggleBtn');
const statusBar = document.getElementById('statusBar');
const statusText = document.getElementById('statusText');
const statusDetail = document.getElementById('statusDetail');
const controlledTabEl = document.getElementById('controlledTab');

let refreshTimer = null;

// Load saved settings and start live refresh
chrome.storage.local.get(['serverUrl', 'apiToken'], (data) => {
  serverUrlInput.value = data.serverUrl || 'http://localhost:8000';
  apiTokenInput.value = data.apiToken || '';
});

// Refresh UI from storage every 500ms while popup is open
refreshUI();
refreshTimer = setInterval(refreshUI, 500);

// Clean up on popup close
window.addEventListener('unload', () => {
  if (refreshTimer) clearInterval(refreshTimer);
});

// Also react immediately to storage changes
chrome.storage.onChanged.addListener(() => refreshUI());

function refreshUI() {
  chrome.storage.local.get(['polling', 'connected', 'lastPoll', 'lastError'], (data) => {
    const { polling, connected, lastPoll, lastError } = data;

    if (!polling) {
      setUI('disconnected', 'Disconnected', '', false);
    } else if (connected) {
      const ago = lastPoll ? timeSince(lastPoll) : '';
      setUI('connected', 'Connected to PHPClaw', ago ? `Last poll: ${ago}` : '', true);
    } else {
      setUI('connecting', 'Connecting...', lastError || '', true);
    }
  });

  // Show which tab is being controlled
  chrome.runtime.sendMessage({ type: 'getControlledTab' }, (resp) => {
    if (chrome.runtime.lastError || !resp || !resp.tabId) {
      controlledTabEl.className = 'controlled-tab';
      return;
    }
    const title = resp.title || 'Untitled';
    const display = title.length > 35 ? title.substring(0, 35) + '...' : title;
    controlledTabEl.textContent = '\u26A1 Controlling: ' + display;
    controlledTabEl.className = 'controlled-tab visible';
  });
}

function setUI(state, text, detail, isPolling) {
  statusBar.className = `status-bar ${state}`;
  statusText.textContent = text;
  statusDetail.textContent = detail;
  statusDetail.className = state === 'connecting' && detail ? 'status-detail error' : 'status-detail';

  toggleBtn.textContent = isPolling ? 'Disconnect' : 'Connect';
  toggleBtn.className = isPolling ? 'btn-disconnect' : 'btn-connect';

  serverUrlInput.disabled = isPolling;
  apiTokenInput.disabled = isPolling;
}

toggleBtn.addEventListener('click', async () => {
  const data = await chrome.storage.local.get(['polling']);

  if (data.polling) {
    chrome.runtime.sendMessage({ type: 'disconnect' });
  } else {
    // Save settings before connecting
    await chrome.storage.local.set({
      serverUrl: serverUrlInput.value,
      apiToken: apiTokenInput.value,
    });
    chrome.runtime.sendMessage({ type: 'connect' });
  }

  // Small delay then refresh
  setTimeout(refreshUI, 100);
});

function timeSince(ts) {
  const sec = Math.floor((Date.now() - ts) / 1000);
  if (sec < 2) return 'just now';
  if (sec < 60) return `${sec}s ago`;
  const min = Math.floor(sec / 60);
  return `${min}m ago`;
}
