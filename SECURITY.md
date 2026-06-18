# Security Policy

If you discover a security vulnerability in PHPClaw for Laravel, please report it privately rather than opening a public issue.

- Email: kevariable@gmail.com
- Or use GitHub's private vulnerability reporting on the repository's Security tab.

Please include enough detail to reproduce the issue. You will get an acknowledgement and, once a fix is released, a note in the changelog.

## Notes on the agent's powerful tools

This package can register tools that read the filesystem and make HTTP requests. The dangerous tools (shell execution, file writes/appends/deletes, directory creation, file moves, raw database queries) ship registered but are **prohibited by default** — every call is gated and throws unless you explicitly call `Kevariable\PhpclawLaravel\DangerousTools::allow()`. Enable them only in environments you trust. Treat any agent that can run tools as a privileged actor and scope the `phpclaw.tools_root`, the registered tools, and the API/browser tokens accordingly.
