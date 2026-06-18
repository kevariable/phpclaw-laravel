# Security Policy

If you discover a security vulnerability in PHPClaw for Laravel, please report it privately rather than opening a public issue.

- Email: kevariable@gmail.com
- Or use GitHub's private vulnerability reporting on the repository's Security tab.

Please include enough detail to reproduce the issue. You will get an acknowledgement and, once a fix is released, a note in the changelog.

## Notes on the agent's powerful tools

This package can register tools that read the filesystem and make HTTP requests. The destructive/system tools (shell execution, process control, raw database queries, file writes/deletes) are intentionally **not** shipped enabled — they are only available if you add them yourself. Treat any agent that can run tools as a privileged actor and scope the `phpclaw.tools_root`, the registered tools, and the browser token accordingly.
