# Administrator Manual

This manual is for administrators of the Patone platform.

Access & roles

- Admins can manage customers, drivers, service requests, and reports.
- Use the admin dashboard (verify URL in `index.php` or `DASHBOARD_QUICK_START.md`).

Common tasks

- Managing customers: create, edit, deactivate
- Managing drivers: onboarding, suspend, change availability
- Viewing service requests: filter by status, priority, or date
- Generating reports: daily and monthly reports available under Reports

Monitoring & logs

- Application logs: `logs/` folder local; configure centralized logging for production
- PHP error logs: check PHP-FPM and webserver logs
- Audit logs: if present, review user action trails for security events

Settings

- System configuration is in `config.php` (do not store secrets in repo)
- Update `manifest.json` for app metadata and service worker (`service-worker.js`)

Emergency procedures

- If service is down: escalate to on-call operator, check DB connectivity, check webserver and PHP-FPM status, review recent deploys
- Data restoration: follow DB backup restore steps (noted in org-runbook)

Backups

- Regular DB backups and filesystem backups for uploads and critical configs

Notes

- For specific UI walkthroughs, add screen-capture videos or reference `DASHBOARD_VISUAL_OVERVIEW.md` in the repo.
