# DesbravaHub

Multi-tenant SaaS web platform for Pathfinder (Desbravadores) clubs.

## Quick Start

1. Copy `.env.example` to `.env`
2. Update database credentials in `.env`
3. Point your web server to the `public/` directory

## Configuration

All configuration is centralized:

- `config/app.php` - Application settings (base_url, environment, etc.)
- `config/database.php` - Database connection settings
- `.env` - Environment-specific values (never commit to version control)

### Using Configuration

```php
// Get config values
config('app.base_url');           // Returns base URL
config('database.connections.mysql.host');  // Returns DB host

// URL helpers
base_url();                       // Base URL
base_url('admin/dashboard');      // Full URL path
tenant_url('club-alpha', 'activities');  // Tenant URL
asset_url('css/style.css');       // Versioned asset URL
api_url('version');               // API endpoint URL

// Environment helpers
is_production();                  // Check if production
is_dev();                         // Check if development
is_debug();                       // Check if debug mode
```

## Directory Structure

```
├── bootstrap/          # Application bootstrapper
├── config/             # Configuration files
├── helpers/            # Global helper functions
├── public/             # Web root (entry point)
└── .env                # Environment variables
```

## Technology Stack

- PHP 8+
- MySQL
- HTML5, CSS, JavaScript ES6+
- MVC + Service Layer architecture
