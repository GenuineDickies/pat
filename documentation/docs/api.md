# API Reference

The project includes an OpenAPI (Swagger) specification describing the REST endpoints used by the platform.

Files

- `openapi.yaml` — the canonical OpenAPI 3.0 spec for the API (included in this docs bundle).

How to view the API locally

1. Install Redoc or use Swagger UI. For a quick local view with Redoc:

   - Install `redoc-cli` (`npm install -g redoc-cli`) and run:

     redoc-cli serve openapi.yaml

   - Or use the online Swagger Editor: https://editor.swagger.io and paste the contents of `openapi.yaml`.

Authentication

All protected endpoints require a JWT in the `Authorization` header: `Bearer <token>`. The `/login` endpoint returns a token on successful authentication.

Examples

- List customers: GET /api/customers
- Create service request: POST /api/requests

Error responses follow the `Error` object defined in the spec.

Notes for integrators

- Update the server URL in `openapi.yaml` before using in production.
- Rate limits and webhook details are described in the Integration Guide.
