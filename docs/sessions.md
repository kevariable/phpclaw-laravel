# Sessions

A session is a named, persisted chat transcript. It lets a conversation carry context across turns.

- `SessionStore` contract — `src/Contracts/SessionStore.php`.
- `CacheSessionStore` — cache-backed implementation (`src/Sessions/CacheSessionStore.php`).

```bash
php artisan phpclaw:chat --session=research   # creates/resumes a session, persists each turn
php artisan phpclaw:sessions                   # id, name, turn count
php artisan phpclaw:session:show <id>          # full transcript
```

When `--session` is given, `phpclaw:chat` loads the prior transcript and feeds it to the model as context, then appends the user prompt and the assistant reply after each successful turn. Passing an existing session id resumes it; any other value starts a new session under that name.

Programmatically the store exposes `create`, `exists`, `append`, `transcript`, `list`, and `forget`.
