# Database Schema

This file documents the database schema and provides guidance on how to inspect and update it.

Source files

- `database/schema.sql` — canonical schema used for initial DB creation
- `database/migrations/` — application migrations (if present)

How to view schema

- Open `database/schema.sql` to see the CREATE TABLE statements.
- Use `mysqldump --no-data dbname` to export only schema from an existing DB.

High-level tables

- `customers` — stores customer profile information
- `requests` — service requests submitted by customers
- `drivers` — driver profiles and availability
- `service_types` — catalogue of service types
- `reports` / materialized views — daily/monthly reporting data (implementation dependent)

Example: describe a table

mysql> USE pat_database;
mysql> DESCRIBE customers;

Foreign keys & indexes

- `requests.customer_id` -> `customers.id`
- `requests.driver_id` -> `drivers.id` (nullable)
- Index common queries: status, created_at, customer_id

Updating schema

- If the project uses migrations, add a migration in `database/migrations/` and run the migration tool used by the app.
- Otherwise, update `database/schema.sql` and run `ALTER TABLE` statements in a safe maintenance window.

Data export/import

- Sample import file: `database/sample_customers_import.csv` (CSV headers must match expected fields in import script).

Notes

- Keep `database/schema.sql` in sync with migrations and CI checks. Consider adding a CI job that validates the schema file against a test DB.
