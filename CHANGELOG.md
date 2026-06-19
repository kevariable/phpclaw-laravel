# Changelog

All notable changes to `phpclaw-laravel` will be documented in this file.

## 0.1.0

Initial release.

- Role-based model routing with fallback; provider/model driven by env (`PHPCLAW_PROVIDER`, `PHPCLAW_MODEL`, …) so it is not tied to any one LLM.
- Model-agnostic `LlmDriver` port; ships a Laravel AI SDK driver, with Prism and bring-your-own examples.
- Tool-using agent core (18 tools), the `Tool` contract, and a `make:phpclaw-tool` generator.
- Modules / tool-router, sessions, long-term memory + compaction, queued tasks.
- REST chat API and a TypeScript Chrome browser-control extension.
- Dangerous tools (shell/file/db) shipped but prohibited by default behind `DangerousTools`.
- CQRS command/query bus; 17 `phpclaw:*` artisan commands.
- 100% test coverage, PHPStan level 5 + Larastan, Pint.
