/**
 * PHPClaw Browser Control - Content Script
 *
 * This is the brain of the extension. Runs on every page, never gets suspended.
 * Polls the server for commands and executes DOM operations directly.
 * For tab-level operations (navigate, new_tab, screenshot, cookies),
 * sends a quick message to the service worker.
 */

(() => {
  // Prevent double-injection
  if (window.__phpclaw_active) return;
  window.__phpclaw_active = true;

  let polling = false;
  let loopHandle = null;
  let serverUrl = '';
  let apiToken = '';

  // ─── Startup: check if we should be polling ──────────────────────────────

  // Auto-start polling if this is the controlled tab (or no controlled tab is set yet)
  chrome.runtime.sendMessage({ type: 'amIControlled' }, (resp) => {
    if (chrome.runtime.lastError) return;
    if (resp && resp.shouldPoll) {
      serverUrl = resp.serverUrl || 'http://localhost:8000';
      apiToken = resp.apiToken || '';
      startLoop();
    }
  });

  // Listen for start/stop from popup via service worker
  chrome.runtime.onMessage.addListener((msg, sender, sendResponse) => {
    if (msg.type === 'phpclaw-start') {
      serverUrl = msg.serverUrl || serverUrl;
      apiToken = msg.apiToken || apiToken;
      startLoop();
      sendResponse({ ok: true });
    } else if (msg.type === 'phpclaw-stop') {
      stopLoop();
      sendResponse({ ok: true });
    } else if (msg.type === 'phpclaw-active') {
      showBorder(true);
    } else if (msg.type === 'phpclaw-done') {
      showBorder(false);
    } else if (msg.type === 'phpclaw-release') {
      hideBorder();
      unmarkTitle();
    }
  });

  // ─── Command loop ────────────────────────────────────────────────────────

  function startLoop() {
    if (polling) return;
    polling = true;
    commandLoop();
  }

  function stopLoop() {
    polling = false;
  }

  async function commandLoop() {
    while (polling) {
      try {
        const resp = await fetchWithAuth('GET', '/phpclaw/browser/pending');

        if (resp.status === 204) {
          // No command — update connection status, brief pause, retry
          chrome.storage.local.set({ connected: true, lastPoll: Date.now() });
          await sleep(200);
          continue;
        }

        if (resp.ok) {
          chrome.storage.local.set({ connected: true, lastPoll: Date.now() });
          const command = await resp.json();
          if (command && command.id) {
            showBorder(true);
            let result;
            try {
              result = await executeCommand(command);
            } catch (e) {
              result = { success: false, error: e.message };
            }
            showBorder(false);

            // Post result
            try {
              await fetchWithAuth('POST', '/phpclaw/browser/result', {
                id: command.id,
                ...result,
              });
            } catch (e) {}

            continue; // Check for next command immediately
          }
        } else if (resp.status === 401) {
          chrome.storage.local.set({ connected: false, lastError: 'Auth failed' });
          polling = false;
          break;
        }
      } catch (err) {
        chrome.storage.local.set({ connected: false, lastError: err.message });
        await sleep(2000);
      }
    }
  }

  // ─── Command execution ───────────────────────────────────────────────────

  async function executeCommand(command) {
    const { action, args } = command;

    switch (action) {
      // --- Tab-level ops: delegate to service worker ---
      case 'navigate':
      case 'new_tab':
      case 'close_tab':
      case 'switch_tab':
      case 'get_tabs':
      case 'go_back':
      case 'go_forward':
      case 'reload':
      case 'screenshot':
      case 'get_cookies':
      case 'get_url':
      case 'release':
        return await delegateToWorker(action, args);

      // --- DOM ops: execute directly ---
      case 'snapshot':       return cmdSnapshot();
      case 'click':          return cmdClick(args);
      case 'type':           return cmdType(args);
      case 'read_text':      return cmdReadText(args);
      case 'read_html':      return cmdReadHtml(args);
      case 'get_links':      return cmdGetLinks(args);
      case 'get_forms':      return cmdGetForms(args);
      case 'fill_form':      return cmdFillForm(args);
      case 'submit_form':    return cmdSubmitForm(args);
      case 'execute_js':     return cmdExecuteJs(args);
      case 'select':         return cmdSelect(args);
      case 'hover':          return cmdHover(args);
      case 'scroll':         return cmdScroll(args);
      case 'wait_for':       return cmdWaitFor(args);
      case 'smart_login':    return cmdSmartLogin(args);

      default:
        return { success: false, error: `Unknown action: ${action}` };
    }
  }

  // Quick message to service worker for chrome.tabs operations
  async function delegateToWorker(action, args) {
    return new Promise((resolve) => {
      chrome.runtime.sendMessage({ type: 'tabAction', action, args }, (resp) => {
        if (chrome.runtime.lastError) {
          resolve({ success: false, error: chrome.runtime.lastError.message });
        } else {
          resolve(resp || { success: false, error: 'No response from worker' });
        }
      });
    });
  }

  // ─── Accessibility tree / snapshot ───────────────────────────────────────

  function buildSnapshot() {
    const elements = [];
    let idx = 0;

    function getLabel(el) {
      if (el.id) { const l = document.querySelector(`label[for="${el.id}"]`); if (l) return l.textContent.trim(); }
      const p = el.closest('label'); if (p) { const t = p.textContent.replace(el.value || '', '').trim(); if (t) return t; }
      if (el.getAttribute('aria-label')) return el.getAttribute('aria-label');
      if (el.title) return el.title;
      if (el.placeholder) return el.placeholder;
      if (el.tagName === 'BUTTON' || el.tagName === 'A') return (el.textContent || '').trim().substring(0, 80);
      if (el.type === 'submit' || el.type === 'button') return el.value || '';
      if (el.name) return el.name;
      return '';
    }

    function isVisible(el) {
      if (el.type === 'hidden') return false;
      const style = getComputedStyle(el);
      if (style.display === 'none' || style.visibility === 'hidden' || style.opacity === '0') return false;
      const rect = el.getBoundingClientRect();
      return !(rect.width === 0 && rect.height === 0);
    }

    const selectors = [
      'a[href]', 'button', 'input:not([type="hidden"])', 'select', 'textarea',
      '[role="button"]', '[role="link"]', '[role="tab"]', '[role="menuitem"]',
      '[contenteditable="true"]', 'summary',
    ];

    const seen = new Set();
    for (const sel of selectors) {
      for (const el of document.querySelectorAll(sel)) {
        if (seen.has(el) || !isVisible(el)) continue;
        seen.add(el);

        const tag = el.tagName.toLowerCase();
        const type = el.type || el.getAttribute('role') || '';
        const label = getLabel(el);
        const value = (tag === 'input' || tag === 'textarea' || tag === 'select')
          ? (el.type === 'password' ? '***' : (el.value || '').substring(0, 50)) : null;

        let display = '';
        if (tag === 'a') display = `link "${label}"`;
        else if (tag === 'button' || type === 'button' || type === 'submit') display = `button "${label}"`;
        else if (tag === 'select') display = `dropdown "${label}"`;
        else if (tag === 'textarea') display = `textarea "${label}"`;
        else if (type === 'checkbox') display = `checkbox "${label}" [${el.checked ? 'checked' : 'unchecked'}]`;
        else if (type === 'radio') display = `radio "${label}" [${el.checked ? 'selected' : 'unselected'}]`;
        else if (type === 'password') display = `password field "${label}"`;
        else if (type === 'email') display = `email field "${label}"`;
        else if (type === 'search') display = `search field "${label}"`;
        else if (tag === 'input') display = `text field "${label}"`;
        else display = `${type || tag} "${label}"`;
        if (value && type !== 'password') display += ` = "${value}"`;

        idx++;
        elements.push({ ref: idx, display, el, tag, type, label });
        if (idx >= 75) break;
      }
      if (idx >= 75) break;
    }

    return elements;
  }

  // Keep last snapshot for ref lookups
  let lastSnapshot = [];

  function cmdSnapshot() {
    lastSnapshot = buildSnapshot();
    const tree = lastSnapshot.map(e => `[${e.ref}] ${e.display}`).join('\n');
    const text = (document.body.innerText || '').substring(0, 500).trim();
    return { success: true, data: { url: location.href, title: document.title, elements: tree, text_preview: text, element_count: lastSnapshot.length } };
  }

  // Resolve a ref to a DOM element
  function resolveRef(ref) {
    if (!ref && ref !== 0) return null;
    const str = String(ref);

    // Number → snapshot index
    if (/^\d+$/.test(str)) {
      const idx = parseInt(str, 10);
      // Rebuild snapshot to ensure refs are current
      lastSnapshot = buildSnapshot();
      const entry = lastSnapshot.find(e => e.ref === idx);
      return entry ? entry.el : null;
    }

    // CSS selector
    if (/^[#.\[]|[>:]/.test(str)) {
      return document.querySelector(str);
    }

    // Text fuzzy match
    lastSnapshot = buildSnapshot();
    const query = str.toLowerCase();
    let best = null, bestScore = 0;
    for (const e of lastSnapshot) {
      const label = e.label.toLowerCase();
      if (label === query) return e.el;
      let score = 0;
      if (label.includes(query)) score = query.length / label.length;
      else if (query.includes(label) && label.length > 2) score = label.length / query.length * 0.6;
      if (score > bestScore) { bestScore = score; best = e.el; }
    }
    return bestScore > 0.3 ? best : null;
  }

  function availableElements() {
    if (lastSnapshot.length === 0) lastSnapshot = buildSnapshot();
    return lastSnapshot.slice(0, 20).map(e => `[${e.ref}] ${e.display}`).join('\n');
  }

  // ─── DOM commands ────────────────────────────────────────────────────────

  function cmdClick(args) {
    const el = resolveRef(args.ref || args.selector);
    if (!el) return { success: false, error: `Element not found: ${args.ref || args.selector}`, data: { available: availableElements() } };
    el.click();
    return { success: true, data: { action: 'clicked', tag: el.tagName.toLowerCase(), text: (el.textContent || '').trim().substring(0, 100) } };
  }

  function cmdType(args) {
    const el = resolveRef(args.ref || args.selector || args.field);
    if (!el) return { success: false, error: `Field not found: ${args.ref || args.selector || args.field}`, data: { available: availableElements() } };
    if (args.clear) el.value = '';
    el.focus();
    el.value = (el.value || '') + (args.text || '');
    el.dispatchEvent(new Event('input', { bubbles: true }));
    el.dispatchEvent(new Event('change', { bubbles: true }));
    return { success: true, data: { action: 'typed', value: el.value } };
  }

  function cmdReadText(args) {
    const el = args.selector ? document.querySelector(args.selector) : document.body;
    if (!el) return { success: false, error: `Element not found: ${args.selector}` };
    const text = el.innerText.substring(0, 50000);
    return { success: true, data: { text, length: text.length } };
  }

  function cmdReadHtml(args) {
    const el = args.selector ? document.querySelector(args.selector) : document.documentElement;
    if (!el) return { success: false, error: `Element not found: ${args.selector}` };
    const html = (args.outer ? el.outerHTML : el.innerHTML).substring(0, 100000);
    return { success: true, data: { html, length: html.length } };
  }

  function cmdGetLinks(args) {
    const container = args.selector ? document.querySelector(args.selector) : document;
    if (!container) return { success: false, error: `Container not found: ${args.selector}` };
    const links = Array.from(container.querySelectorAll('a[href]')).slice(0, args.limit || 100).map(a => ({
      href: a.href, text: a.textContent.trim().substring(0, 200),
    }));
    return { success: true, data: { links, count: links.length } };
  }

  function cmdGetForms(args) {
    const container = args.selector ? document.querySelector(args.selector) : document;
    if (!container) return { success: false, error: `Container not found` };
    const forms = Array.from(container.querySelectorAll('form')).map((form, i) => ({
      index: i, id: form.id || null, action: form.action, method: form.method,
      fields: Array.from(form.elements).map(el => ({
        tag: el.tagName.toLowerCase(), type: el.type || null, name: el.name || null,
        id: el.id || null, placeholder: el.placeholder || null,
      })).filter(f => f.type !== 'hidden'),
    }));
    return { success: true, data: { forms, count: forms.length } };
  }

  function cmdFillForm(args) {
    const filled = [];
    for (const [sel, value] of Object.entries(args.fields || {})) {
      const el = resolveRef(sel);
      if (!el) { filled.push({ selector: sel, success: false }); continue; }
      el.focus(); el.value = value;
      el.dispatchEvent(new Event('input', { bubbles: true }));
      el.dispatchEvent(new Event('change', { bubbles: true }));
      filled.push({ selector: sel, success: true });
    }
    return { success: true, data: { filled } };
  }

  function cmdSubmitForm(args) {
    const form = args.selector ? document.querySelector(args.selector) : document.querySelector('form');
    if (!form) return { success: false, error: 'Form not found' };
    form.submit();
    return { success: true, data: { submitted: true } };
  }

  function cmdExecuteJs(args) {
    try {
      const result = eval(args.code);
      return { success: true, data: { result } };
    } catch (e) {
      return { success: false, error: e.message };
    }
  }

  function cmdSelect(args) {
    const el = resolveRef(args.ref || args.selector);
    if (!el) return { success: false, error: `Element not found: ${args.ref || args.selector}`, data: { available: availableElements() } };
    el.value = args.value || '';
    el.dispatchEvent(new Event('change', { bubbles: true }));
    return { success: true, data: { value: el.value } };
  }

  function cmdHover(args) {
    const el = resolveRef(args.ref || args.selector);
    if (!el) return { success: false, error: `Element not found: ${args.ref || args.selector}` };
    el.dispatchEvent(new MouseEvent('mouseenter', { bubbles: true }));
    el.dispatchEvent(new MouseEvent('mouseover', { bubbles: true }));
    return { success: true, data: { action: 'hovered' } };
  }

  function cmdScroll(args) {
    const el = args.selector ? document.querySelector(args.selector) : window;
    const px = args.amount || 500;
    const dir = args.direction || 'down';
    if (dir === 'up') el === window ? window.scrollBy(0, -px) : (el.scrollTop -= px);
    else if (dir === 'down') el === window ? window.scrollBy(0, px) : (el.scrollTop += px);
    else if (dir === 'top') el === window ? window.scrollTo(0, 0) : (el.scrollTop = 0);
    else if (dir === 'bottom') el === window ? window.scrollTo(0, document.body.scrollHeight) : (el.scrollTop = el.scrollHeight);
    return { success: true, data: { scrolled: dir } };
  }

  async function cmdWaitFor(args) {
    const timeout = (args.timeout || 10) * 1000;
    const start = Date.now();
    while (Date.now() - start < timeout) {
      if (document.querySelector(args.selector)) return { success: true, data: { found: true, elapsed_ms: Date.now() - start } };
      await sleep(250);
    }
    return { success: false, error: `Timeout waiting for: ${args.selector}` };
  }

  function cmdSmartLogin(args) {
    const forms = Array.from(document.querySelectorAll('form'));
    for (const form of forms) {
      const fields = Array.from(form.elements);
      let userField = fields.find(f => (f.type === 'text' || f.type === 'email') &&
        (/user|email|login/i.test(f.name || '') || /user|email|login/i.test(f.id || '') || /user|email/i.test(f.placeholder || '')));
      const passField = fields.find(f => f.type === 'password');
      if (!userField) userField = fields.find(f => f.type === 'text' || f.type === 'email');

      if (userField && passField) {
        userField.focus(); userField.value = args.username || '';
        userField.dispatchEvent(new Event('input', { bubbles: true }));
        passField.focus(); passField.value = args.password || '';
        passField.dispatchEvent(new Event('input', { bubbles: true }));

        if (args.submit !== false) form.submit();
        return { success: true, data: { filled: true, submitted: args.submit !== false } };
      }
    }
    return { success: false, error: 'No login form found', data: { available: availableElements() } };
  }

  // ─── HTTP helper ─────────────────────────────────────────────────────────

  async function fetchWithAuth(method, path, body) {
    const headers = { 'Content-Type': 'application/json' };
    if (apiToken) headers['Authorization'] = `Bearer ${apiToken}`;
    const opts = { method, headers };
    if (body) opts.body = JSON.stringify(body);
    return fetch(serverUrl + path, opts);
  }

  function sleep(ms) { return new Promise(r => setTimeout(r, ms)); }

  // ─── Visual border ───────────────────────────────────────────────────────

  let borderEl = null, labelEl = null, hideTimer = null, originalTitle = null, controlled = false, titleInterval = null;

  function markTitle() {
    if (originalTitle === null) originalTitle = document.title;
    if (!document.title.startsWith('\u26A1 ')) document.title = '\u26A1 ' + (originalTitle || document.title);
  }

  function unmarkTitle() {
    if (originalTitle !== null) { document.title = originalTitle; originalTitle = null; }
    if (titleInterval) { clearInterval(titleInterval); titleInterval = null; }
  }

  function createOverlay() {
    if (borderEl) return;
    borderEl = document.createElement('div');
    borderEl.id = 'phpclaw-border';
    borderEl.innerHTML = `<style>
      #phpclaw-border { position:fixed; inset:0; z-index:2147483647; pointer-events:none; border:3px solid #00d4ff; box-shadow:inset 0 0 12px rgba(0,212,255,0.3); transition:opacity 0.3s; opacity:0; }
      #phpclaw-border.active { opacity:1; }
      #phpclaw-border.idle { border-color:rgba(0,212,255,0.3); }
      #phpclaw-label { position:fixed; top:6px; left:50%; transform:translateX(-50%); z-index:2147483647; pointer-events:none; background:rgba(0,212,255,0.9); color:#000; font:700 11px -apple-system,sans-serif; padding:3px 12px; border-radius:0 0 6px 6px; opacity:0; transition:opacity 0.3s; }
      #phpclaw-label.active { opacity:1; }
      #phpclaw-label .dot { display:inline-block; width:6px; height:6px; background:#000; border-radius:50%; margin-right:6px; animation:phpclaw-pulse 1s infinite; }
      @keyframes phpclaw-pulse { 0%,100%{opacity:1} 50%{opacity:0.3} }
    </style>`;
    document.documentElement.appendChild(borderEl);
    labelEl = document.createElement('div');
    labelEl.id = 'phpclaw-label';
    labelEl.innerHTML = '<span class="dot"></span>PHPClaw';
    document.documentElement.appendChild(labelEl);
  }

  function showBorder(active) {
    createOverlay();
    if (hideTimer) { clearTimeout(hideTimer); hideTimer = null; }
    controlled = true;
    markTitle();
    if (!titleInterval) titleInterval = setInterval(() => { if (controlled && !document.title.startsWith('\u26A1 ')) { originalTitle = document.title; document.title = '\u26A1 ' + originalTitle; } }, 500);
    if (active) { borderEl.className = 'active'; labelEl.className = 'active'; }
    else { borderEl.className = 'active idle'; labelEl.className = 'active'; hideTimer = setTimeout(() => { borderEl.className = ''; labelEl.className = ''; }, 3000); }
  }

  function hideBorder() {
    if (borderEl) borderEl.className = '';
    if (labelEl) labelEl.className = '';
    controlled = false;
  }
})();
