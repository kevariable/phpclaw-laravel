# Tasks

Run the agent in the background on Laravel's queue.

- `TaskStore` contract + `CacheTaskStore` — track `pending` / `completed` / `failed` with the result or error.
- `RunAgentJob` (`ShouldQueue`) — runs the agent and records the outcome; failures are stored, not thrown.
- `TaskDispatcher` — creates the task record and dispatches the job, returning the task id.

```bash
php artisan phpclaw:run reasoning "long job…" --queue   # prints the task id
php artisan phpclaw:tasks                                # id, status, role, model
php artisan phpclaw:task:show <id>                        # status + result/error
```

Tasks use your app's default queue connection, so run a worker (`php artisan queue:work`) to process them. The job records `completed` with the answer + model, or `failed` with the error message.
