# Technical Tasks & How-tos

This page lists technical how-tos to support documentation maintenance and code hygiene.

1) Run tests (PHPUnit)

- Run the full test suite:

  vendor/bin/phpunit --configuration phpunit.xml

- Run a single test file:

  vendor/bin/phpunit tests/SomeTest.php

2) Static analysis (PHPStan)

- Install PHPStan (`composer require --dev phpstan/phpstan`)
- Run analysis:

  vendor/bin/phpstan analyse backend --level=7

3) Generate PHPDoc

- Install `phpdocumentor/phpdocumentor` via Composer:

  composer require --dev phpdocumentor/phpdocumentor

- Generate docs:

  vendor/bin/phpdoc -d backend -t docs/phpdoc

4) Keeping API spec up-to-date

- Edit `openapi.yaml` and ensure endpoints match controller implementations.
- Consider adding a CI check that validates `openapi.yaml` against live endpoints (contract tests).

5) Publish documentation

- Run a local server:

  pip install mkdocs
  mkdocs serve -f documentation/mkdocs.yml

- Build static site:

  mkdocs build -f documentation/mkdocs.yml -d site

- CI: add a job to run `mkdocs build` and publish `site/` to GitHub Pages or other static host.

6) Add PHPDoc to code

- Add `/** ... */` style comments to classes and public methods in `backend/controllers` and `backend/models`.
- Example:

  /**
   * Get a customer by ID.
   *
   * @param int $id
   * @return array|null
   */
  public function getCustomer(int $id): ?array
  {
      // ...
  }

Notes

- Automate as much as possible (linting, phpstan, phpunit) in CI for PR quality checks.
