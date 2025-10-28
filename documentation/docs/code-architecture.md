# Code Architecture

This section provides a high-level overview of the application's architecture.

Project layout (high-level)

- `backend/` — PHP controllers, models, and routing
  - `controllers/` — controller classes (ApiController, AuthController, etc.)
  - `models/` — domain models and DB access
  - `config/` — database and environment configuration
- `frontend/` — UI pages and JS
- `assets/` — compiled JS/CSS/images
- `database/` — schema and migrations
- `tests/` — PHPUnit tests and test fixtures

Controller flow

- `Router.php` dispatches requests to controller actions.
- Controllers invoke models for DB access and return JSON responses for API routes.

Key design notes

- Authentication is JWT-based.
- Keep business logic in models/services instead of controllers to enable unit testing.
- Use dependency injection where possible for easier mocking in tests.

Suggested next steps (small improvements)

- Add typed DTOs for request/response payloads
- Introduce a service layer and move complex logic out of controllers
- Add unit tests for services and integration tests for controller endpoints

References

- See `backend/controllers/` for examples of controllers and current PHPDoc coverage.
