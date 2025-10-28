# Patone Documentation

This directory contains the complete documentation for the Patone Roadside Assistance platform.

## Quick Start

### View Documentation Locally
```bash
# Install MkDocs
pip install mkdocs

# Serve documentation 
mkdocs serve -f documentation/mkdocs.yml

# Open http://127.0.0.1:8000 in your browser
```

### Build Static Site
```bash
mkdocs build -f documentation/mkdocs.yml -d documentation/site
```

## Documentation Structure

- **docs/** - All Markdown documentation files
  - `index.md` - Home page
  - `api.md` - API reference and OpenAPI spec
  - `developer-setup.md` - Development environment setup
  - `database-schema.md` - Complete database documentation
  - `deployment-guide.md` - Production deployment guide
  - `admin-manual.md` - Administrator user manual
  - `driver-mobile-app.md` - Driver mobile app documentation
  - `integration-guide.md` - Third-party integration guide
  - `troubleshooting.md` - Common issues and solutions
  - `faq.md` - Frequently asked questions
  - `code-architecture.md` - Code organization and architecture
  - `technical-tasks.md` - Development tools and CI setup
  - `openapi.yaml` - OpenAPI/Swagger specification

- **mkdocs.yml** - MkDocs configuration
- **site/** - Generated static site (after build)

## CI/CD

The documentation is automatically:
- Built and validated on every push/PR
- Deployed to GitHub Pages when merged to main branch

See `.github/workflows/ci.yml` for the complete CI configuration.

## Contributing

1. Edit Markdown files in `docs/`
2. Test locally with `mkdocs serve`
3. Verify build works: `mkdocs build`
4. Submit PR - CI will validate automatically

## Documentation Coverage

✅ **Complete** - All requested documentation types implemented:
- [x] API documentation (Swagger/OpenAPI)
- [x] Developer setup guide
- [x] Database schema documentation
- [x] Deployment guide  
- [x] User manual for administrators
- [x] Driver mobile app documentation
- [x] Integration guide for third parties
- [x] Installation instructions
- [x] Configuration guide
- [x] API reference
- [x] Code architecture documentation
- [x] Troubleshooting guide
- [x] FAQ section
- [x] Technical tasks (PHPDoc, testing, CI)
