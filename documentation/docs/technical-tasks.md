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

- Install MkDocs:

  pip install mkdocs

- Run a local server:

  mkdocs serve -f documentation/mkdocs.yml

- Build static site:

  mkdocs build -f documentation/mkdocs.yml -d documentation/site

- CI: add a job to run `mkdocs build` and publish `site/` to GitHub Pages or other static host.

6) Validation & CI Setup

See the dedicated "Validation & CI" section below for complete setup instructions.

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

## Validation & CI Setup

### Local Validation

Before committing, run these validation checks:

```bash
# Validate MkDocs builds successfully
cd /workspaces/pat
mkdocs build -f documentation/mkdocs.yml -d documentation/site

# Run PHP tests
cd "Desktop/Code Projects/Patone"
composer test

# Run JavaScript tests  
npm test

# Check PHP code quality (if PHPStan installed)
vendor/bin/phpstan analyse backend --level=7

# Validate OpenAPI spec (if redoc-cli installed)
redoc-cli validate documentation/docs/openapi.yaml
```

### GitHub Actions CI Setup

Create `.github/workflows/ci.yml` to automate validation:

```yaml
name: CI

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    
    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_ROOT_PASSWORD: root
          MYSQL_DATABASE: roadside_assistance_test
        options: >-
          --health-cmd="mysqladmin ping"
          --health-interval=10s
          --health-timeout=5s
          --health-retries=3

    steps:
    - uses: actions/checkout@v3
    
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: '8.0'
        extensions: pdo, pdo_mysql, mbstring, curl, zip, json
    
    - name: Install Composer dependencies
      working-directory: ./Desktop/Code\ Projects/Patone
      run: composer install --prefer-dist --no-progress
    
    - name: Setup Node.js
      uses: actions/setup-node@v3
      with:
        node-version: '18'
    
    - name: Install Node dependencies
      working-directory: ./Desktop/Code\ Projects/Patone
      run: npm install
    
    - name: Run PHP tests
      working-directory: ./Desktop/Code\ Projects/Patone
      run: composer test
      env:
        DB_HOST: 127.0.0.1
        DB_PORT: 3306
        DB_DATABASE: roadside_assistance_test
        DB_USERNAME: root
        DB_PASSWORD: root
    
    - name: Run JavaScript tests
      working-directory: ./Desktop/Code\ Projects/Patone
      run: npm test

  docs:
    runs-on: ubuntu-latest
    
    steps:
    - uses: actions/checkout@v3
    
    - name: Setup Python
      uses: actions/setup-python@v4
      with:
        python-version: '3.x'
    
    - name: Install MkDocs
      run: pip install mkdocs
    
    - name: Build documentation
      run: mkdocs build -f documentation/mkdocs.yml -d documentation/site
    
    - name: Deploy to GitHub Pages
      if: github.ref == 'refs/heads/main'
      uses: peaceiris/actions-gh-pages@v3
      with:
        github_token: ${{ secrets.GITHUB_TOKEN }}
        publish_dir: ./documentation/site
```

### Documentation Deployment

**GitHub Pages Setup**:
1. Go to repository Settings → Pages
2. Set source to "GitHub Actions"
3. The CI will automatically deploy docs on main branch updates
4. Documentation will be available at `https://username.github.io/repo-name`

**Alternative Hosting**:
- **Netlify**: Connect repo, set build command to `mkdocs build -f documentation/mkdocs.yml`, publish directory to `documentation/site`
- **Vercel**: Similar setup with build command
- **AWS S3**: Upload `documentation/site/` contents to S3 bucket with static hosting

### Quality Gates

Recommended PR checks:
- [ ] All tests pass (PHP + JavaScript)
- [ ] MkDocs builds without errors
- [ ] No PHPStan errors (if enabled)
- [ ] OpenAPI spec validates (if tooling added)

### Manual Release Checklist

Before major releases:
- [ ] Update version numbers in `package.json` and documentation
- [ ] Review and update all documentation for accuracy
- [ ] Test full local setup using developer setup guide
- [ ] Generate fresh PHPDoc and commit any updates
- [ ] Verify all links in documentation work
- [ ] Update changelog/release notes

Notes

- Automate as much as possible (linting, phpstan, phpunit) in CI for PR quality checks.
- Consider adding Lighthouse CI for performance testing of the documentation site.
- Set up branch protection rules to require CI checks before merging.
