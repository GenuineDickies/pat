# Integration Guide for Third Parties

This guide explains how third-party integrators can authenticate and interact with the Patone API.

Authentication

- Obtain API credentials from the platform administrator.
- Authenticate via `/login` to retrieve a JWT token.
- Include `Authorization: Bearer <token>` in all subsequent API requests.

Common integration flows

- Onboard a new partner: create a user, generate keys, configure webhooks.
- Query customer information: GET `/customers` with search.
- Create service requests on behalf of customers: POST `/requests`.

Webhooks

- If the platform provides webhook notifications (e.g., request status changes), document payloads, signing secrets, retry behavior, and expected response codes.

Rate limits & throttling

- The current system does not specify global rate limits in the OpenAPI file. Before production use, agree on a rate limit policy with platform operators.

Sample requests

- Example: Create request

  POST /api/requests
  Content-Type: application/json
  Authorization: Bearer <token>

  {
    "customer_id": 1,
    "service_type_id": 2,
    "location_address": "123 Main St",
    "location_city": "City",
    "location_state": "ST"
  }

Error handling

- Check for HTTP 4xx and 5xx responses and parse the `Error` schema from the OpenAPI spec.

Onboarding checklist

- Provide contact and technical point-of-contact
- Provide test account and production credentials
- Agree on SLA and supported endpoints

Notes

- Keep integrations robust to schema changes by using API versioning and contract tests.
