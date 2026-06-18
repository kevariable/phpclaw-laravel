# Browser control

The package can drive a real, logged-in Chrome browser through a bundled extension — navigate, click, type, read — like PHPClaw's browser control.

## How it works

```
agent  ──run browser_control──►  BrowserBridge (cache-backed queue)
                                      ▲            │ enqueue
   Chrome extension  ──polls──────────┘            │
        GET  /phpclaw/browser/pending   ◄──────────┘
        POST /phpclaw/browser/result    ──► result ──► tool returns to the model
```

`browser_control`'s `run()` enqueues a command on the `BrowserBridge` (`CacheBrowserBridge`, cross-process via the cache) and blocks until a result arrives or it times out. The Chrome extension polls the package's token-guarded routes, executes the DOM operation in the page, and posts the result back.

## Routes

Registered by the package, guarded by `VerifyBrowserToken` (bearer token):

| Method | Path | Purpose |
|---|---|---|
| GET | `/phpclaw/browser/pending` | extension fetches the next command |
| POST | `/phpclaw/browser/result` | extension posts a command result |
| GET | `/phpclaw/browser/status` | connection status |

## Setup

```bash
# .env
PHPCLAW_BROWSER_TOKEN=some-long-random-string

php artisan vendor:publish --tag="phpclaw-extension"   # -> base_path('phpclaw-extension')
# chrome://extensions -> Developer mode -> Load unpacked -> select that folder
# In the popup: set the server URL (e.g. http://localhost:8000) and the token.
```

Add `Kevariable\PhpclawLaravel\Tools\BrowserControlTool::class` to `phpclaw.tools` (opt-in, because it needs the extension running).

## Config

`phpclaw.browser`: `token`, `await_attempts`, `poll_interval_ms`, `connected_ttl`.

The bridge enqueues commands and the agent blocks for the result, so the web server serving the extension's polls must run alongside the agent process. If no browser is connected, `browser_control` returns a timeout message instead of hanging forever.

## Developing the extension

The extension is a TypeScript project (Vite + `@crxjs/vite-plugin`) under `resources/extension`. `vendor:publish` ships the pre-built `dist/`, so end users need no build step. To modify it:

```bash
cd resources/extension
bun install        # or npm install
bun run dev        # HMR development build
bun run build      # production build -> dist/ (committed, shipped by vendor:publish)
bun run typecheck  # tsc --noEmit
```

Sources: `src/background.ts` (service worker), `src/content.ts` (page polling + DOM ops), `src/popup.ts` (connect UI). The manifest is generated from `manifest.config.ts`.
