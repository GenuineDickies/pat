# Troubleshooting

Where to look

- Application logs: `logs/` directory
- PHP logs: php-fpm or webserver logs
- Database connectivity: try connecting with `mysql` client

Common issues

1. 500 Internal Server Error
   - Check PHP and webserver logs for stack traces
   - Check for missing environment variables or DB credentials

2. Database connection failures
   - Verify DB is running and credentials in `config.php` are correct
   - Ensure proper network/firewall configuration

3. Tests failing locally
   - Run `vendor/bin/phpunit -v` and inspect failing tests
   - Check for missing test fixtures or environment variables

4. Auth / JWT problems
   - Ensure JWT secret is set and consistent between apps and API
   - Check token expiry and clock skew

5. Frontend asset 404s
   - Rebuild assets: `npm run build` or ensure published assets are present in `assets/`

Log levels & debug

- For local debugging, set debug mode and check stack traces. Never enable debug in production.

Recovery steps

- Roll back to previous working release if issue is due to a new deploy
- Reapply DB patch or restore backup if migration caused data loss

Collecting useful error reports

- Capture timestamps, request IDs (if present), user ID, and relevant log snippets
- Reproduce issue in staging before applying fixes to production
