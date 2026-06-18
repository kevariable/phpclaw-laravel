# REST API

The package exposes a token-guarded HTTP endpoint to run the agent.

```
POST /phpclaw/chat
Authorization: Bearer <PHPCLAW_API_TOKEN>
Content-Type: application/json

{ "prompt": "Explain CQRS in one sentence.", "role": "reasoning" }
# or: { "prompt": "...", "module": "coding" }
```

Response:

```json
{ "response": "…", "model": "gemini-2.5-flash" }
```

- Guarded by `VerifyToken` against `phpclaw.api.token` (`PHPCLAW_API_TOKEN`). Missing/invalid token → `401`.
- Missing `prompt` → `422` (validation).
- Unknown role/module or all-candidates-failed → `422` with `{ "error": "…" }`.

The same `VerifyToken` middleware guards the browser-control routes (`phpclaw.browser.token`) — see [browser.md](browser.md).
