# Technology Stack

## Programming Languages
- **PHP 8.2+** - Backend application language
- **JavaScript (ES6+)** - Frontend scripting
- **SQL** - Database queries and migrations

## Backend Framework
- **Laravel 12.0** - PHP web application framework
- **Composer** - PHP dependency management

## Authentication & Authorization
- **Laravel Passport 13.0** - OAuth 2.0 server implementation
- **Laravel Socialite 5.23** - Social authentication (OAuth providers)
- **aacotroneo/laravel-saml2 2.1** - SAML 2.0 single sign-on
- **pragmarx/google2fa-laravel 2.3** - Two-factor authentication
- **Spatie Laravel Permission 6.22** - Role and permission management

## Frontend Stack
- **Vite 7.0** - Build tool and dev server
- **Tailwind CSS 4.0** - Utility-first CSS framework
- **Axios 1.11** - HTTP client
- **Laravel Vite Plugin 2.0** - Laravel-Vite integration

## Testing Framework
- **Pest PHP 4.1** - Testing framework
- **Pest Laravel Plugin 4.0** - Laravel-specific testing utilities
- **Mockery 1.6** - Mocking library
- **PHPUnit** - Underlying test runner

## Development Tools
- **Laravel Pint 1.24** - Code style fixer (PHP CS Fixer wrapper)
- **Laravel Sail 1.41** - Docker development environment
- **Laravel Tinker 2.10** - REPL for Laravel
- **Laravel Pail 1.2** - Log viewer
- **Concurrently 9.0** - Run multiple commands simultaneously

## Database
- Supports MySQL, PostgreSQL, SQLite, SQL Server via Laravel's database abstraction
- Eloquent ORM for database interactions
- Migration-based schema management

## Queue & Jobs
- Laravel Queue system for asynchronous processing
- Supports Redis, database, SQS, and other queue drivers
- Job-based workflow execution

## File Storage
- Laravel Filesystem abstraction
- Supports local, S3, and other storage drivers

## Development Commands

### Setup
```bash
composer setup
# Runs: composer install, .env setup, key generation, migrations, npm install, npm build
```

### Development Server
```bash
composer dev
# Runs concurrently: Laravel server, queue worker, log viewer, Vite dev server
```

### Testing
```bash
composer test
# Runs: config:clear, artisan test (Pest)
```

### Code Style
```bash
./vendor/bin/pint
# Fixes code style issues
```

### Database Management
```bash
php artisan migrate          # Run migrations
php artisan db:seed          # Seed database
php artisan migrate:fresh    # Drop and recreate
```

### Queue Management
```bash
php artisan queue:work       # Process queue jobs
php artisan queue:listen     # Listen for jobs
```

### Passport Setup
```bash
php artisan passport:install # Install OAuth keys
php artisan passport:client  # Create OAuth client
```

## Build Configuration
- **composer.json** - PHP dependencies and scripts
- **package.json** - Node dependencies and build scripts
- **vite.config.js** - Vite bundler configuration
- **phpunit.xml** - Test suite configuration
- **.editorconfig** - Editor formatting rules

## Environment Configuration
- **.env** - Environment-specific settings
- **/config/** - Application configuration files
- Supports multiple environments (local, staging, production)
