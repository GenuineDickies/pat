# Deployment Guide

This guide covers recommended deployment steps for the Patone platform.

Environments

- Development — local, quick iteration
- Staging — QA and integration testing
- Production — public-facing deployment

Environment variables & configuration

- `config.php` should contain DB connection, JWT secret, and other environment-specific settings.
- Before deploying, set `APP_DEBUG=false` and ensure secure JWT secrets.

Recommended stack

- PHP-FPM behind Nginx
- MySQL or managed RDS
- Redis for cache/session (optional)
- A process manager (systemd) for queue workers

Simple Docker example (compose)

version: '3.8'
services:
  app:
    image: php:8.1-fpm
    volumes:
      - ./:/var/www/html
    depends_on:
      - db
  web:
    image: nginx:stable
    ports:
      - 80:80
    volumes:
      - ./:/var/www/html
      - ./deploy/nginx.conf:/etc/nginx/conf.d/default.conf
    depends_on:
      - app
  db:
    image: mysql:8
    environment:
      MYSQL_ROOT_PASSWORD: example
      MYSQL_DATABASE: pat

Zero-downtime deploy checklist

- Run DB migrations in a backwards-compatible way
- Warm caches and precompile views/assets before switching traffic
- Test health checks and roll back if necessary

Security & best practices

- Use HTTPS with strong TLS config
- Rotate JWT and API keys periodically
- Use least-privilege DB user for app database
- Configure logging and monitoring (error rates, latency)

CI/CD

- Build and test the app in CI (PHPUnit, PHPStan)
- Publish docs (MkDocs) to a static site host as part of release artifacts

Rollback plan

- Keep previous build artifacts available
- Have DB rollback plan (manual or migration-based) — avoid destructive migrations during peak hours

Notes

Adjust the example Docker Compose snippet for your environment. Add health checks and appropriate volume/secret handling in production.
