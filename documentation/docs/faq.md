# FAQ

Q: Where are logs stored?
A: Local logs are in `logs/`. In production, configure a centralized log aggregator.

Q: How do I run tests?
A: `vendor/bin/phpunit --configuration phpunit.xml`.

Q: How do I view the API spec?
A: Use `documentation/docs/openapi.yaml` with Redoc or Swagger Editor.

Q: How do I add a new driver?
A: Use the admin dashboard or POST to `/api/drivers` if endpoint is implemented.

Q: Where are frontend assets built?
A: Check `assets/js` and run `npm run build` to regenerate production assets.
