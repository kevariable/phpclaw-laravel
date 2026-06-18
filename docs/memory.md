# Memory

Long-term memory is a flat list of notes the agent can write and recall across runs.

- `MemoryStore` contract — `src/Contracts/MemoryStore.php`.
- `CacheMemoryStore` — cache-backed; `compact($keep)` trims to the most recent `$keep` notes and returns how many were removed (`src/Memory/CacheMemoryStore.php`).

Two tools (shipped in the default tool set) expose memory to the model:

| Tool | Purpose |
|---|---|
| `memory_write` | Save a note |
| `memory_read` | Recall all notes |

```bash
php artisan phpclaw:memory:show
php artisan phpclaw:memory:compact --keep=20
```

Config: `phpclaw.memory.max_notes` (default 50) is the default `--keep` value for compaction. Compaction here is recency-based (keep the newest N); swap in an LLM-summarising `MemoryStore` if you want semantic compaction.
