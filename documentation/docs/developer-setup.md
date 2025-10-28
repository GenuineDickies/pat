# Developer Setup Guide

This page explains how to set up a local development environment for the Patone project.

Prerequisites

- PHP 8.0+ (check `config.php` and composer requirements)
- Composer
- MySQL or MariaDB
- Node.js + npm (for frontend assets and optional `redoc-cli`)
- Git

Quickstart

1. Clone the repository

   git clone https://github.com/GenuineDickies/pat.git

2. Install PHP dependencies

   cd pat
   composer install

3. Copy configuration

   cp config.php.example config.php
   # edit `config.php` to set DB credentials and environment variables

4. Create the database and import schema

   mysql -u root -p < database/schema.sql

5. Install frontend dependencies and build assets (if applicable)

   npm install
   npm run build

6. Run tests

   vendor/bin/phpunit --configuration phpunit.xml

Static analysis and docs

- PHPStan: `vendor/bin/phpstan analyse src --level=max` (adjust path and level as needed)
- Generate PHPDoc: use `phpDocumentor` or `phpdoc` to generate HTML docs

Notes & tips

- Use Docker for repeatable environments; see `deployment-guide.md` for an example Docker Compose snippet.
- If you hit permission or missing-extension issues, check `php -m` and enable needed extensions (pdo_mysql, openssl, mbstring, etc.).
