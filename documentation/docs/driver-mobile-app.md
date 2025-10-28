# Driver Mobile App Documentation

This document describes the driver mobile app workflow and integration points.

Overview

Drivers use the mobile app to:
- Receive and accept assignments
- Update status (available, busy, offline)
- Navigate to service locations
- Report completion and upload notes/photos

API integration

- Mobile apps should authenticate via `/login` and include JWT in `Authorization` header.
- Endpoints used commonly by the driver app:
  - GET `/drivers/{id}` — profile and status
  - GET `/requests?status=assigned` — assigned requests
  - POST `/requests/{id}/update` — update status or add notes (if implemented)

Push notifications

- If push is implemented, document provider (Firebase/FCM or APNs) keys and how to register device tokens.

Offline behavior

- Cache assigned requests locally and sync status changes when network is available.

Driver troubleshooting

- App login fails: ensure correct credentials and that JWT secret matches the mobile config
- GPS/location issues: ensure app has location permissions
- Missing assignments: check driver status and filters on the server

Notes for implementers

- Keep API responses compact; prefer paginated lists for large datasets
- Use optimistic UI updates for better driver experience
