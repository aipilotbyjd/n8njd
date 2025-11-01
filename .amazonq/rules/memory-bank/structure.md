# Project Structure

## Directory Organization

### `/app` - Application Core
Main application logic following Laravel conventions.

#### `/app/Console/Commands`
Artisan CLI commands for maintenance and automation tasks.

#### `/app/Enums`
Type-safe enumerations for domain concepts:
- `AuditLogAction` - Audit event types
- `CredentialType` - Credential storage types
- `ExecutionStatus` - Workflow execution states
- `NodeType` - Workflow node categories
- `UserRole` - User permission levels
- `WorkflowStatus` - Workflow lifecycle states

#### `/app/Http`
HTTP layer with controllers, middleware, and request validation:
- `Controllers/` - API and web request handlers
- `Middleware/` - Request/response filters
- `Requests/` - Form request validation classes

#### `/app/Jobs`
Asynchronous background jobs:
- `ExecuteWorkflowJob` - Workflow execution orchestration
- `RefreshOAuthTokenJob` - OAuth token renewal

#### `/app/Models`
Eloquent ORM models representing database entities:
- Core: `User`, `Organization`, `Team`, `Workflow`, `Node`
- Execution: `Execution`, `NodeExecution`, `Schedule`, `Webhook`
- Security: `Credential`, `AuditLog`, `RateLimit`
- Collaboration: `WorkflowShare`, `OrganizationUser`, `NotificationSetting`
- Versioning: `WorkflowVersion`, `Template`, `Variable`

#### `/app/Services`
Business logic organized by domain:
- `Admin/` - Administrative operations
- `Ai/` - AI integration services
- `Analytics/` - Metrics and reporting
- `Auth/` - Authentication logic
- `Collaboration/` - Sharing and teams
- `Credential/` - Credential management
- `Execution/` - Workflow execution engine
- `Expression/` - Expression evaluation
- `Node/` - Node type implementations
- `Notification/` - Alert delivery
- `Organization/` - Multi-tenancy
- `Storage/` - File management
- `Template/` - Workflow templates
- `Variable/` - Variable resolution
- `Webhook/` - Webhook handling
- `Workflow/` - Workflow CRUD and graph operations

#### `/app/Traits`
Reusable traits:
- `ApiResponse` - Standardized API response formatting

### `/config` - Configuration
Laravel configuration files for services, authentication, database, caching, queues, and third-party integrations (SAML, Passport, Google 2FA, permissions).

### `/database` - Database Layer
- `migrations/` - Database schema definitions
- `factories/` - Test data generators
- `seeders/` - Database population scripts

### `/routes` - HTTP Routing
- `api.php` - RESTful API endpoints
- `web.php` - Web interface routes
- `console.php` - CLI command registration

### `/resources` - Frontend Assets
- `css/` - Stylesheets (Tailwind CSS)
- `js/` - JavaScript (Vite bundled)
- `views/` - Blade templates

### `/public` - Web Root
Publicly accessible files including entry point (index.php), assets, and adminer.php for database management.

### `/storage` - Runtime Storage
- `app/` - Application files (private/public)
- `framework/` - Framework cache and sessions
- `logs/` - Application logs

### `/tests` - Test Suite
- `Feature/` - Integration tests
- `Unit/` - Unit tests
- Pest PHP testing framework

## Core Components

### Workflow Engine
- **Graph Builder** (`Services/Workflow/Graph.php`) - Constructs workflow execution graphs
- **Execution Service** (`Services/Execution/ExecutionService.php`) - Orchestrates workflow runs
- **Node Executor** - Executes individual workflow nodes
- **Expression Evaluator** - Processes dynamic expressions in workflows

### Authentication System
- OAuth 2.0 server (Laravel Passport)
- SAML 2.0 provider integration
- Two-factor authentication
- Social login providers

### Multi-Tenancy
- Organization-based isolation
- Team-level workflow organization
- Role-based access control (Spatie permissions)
- Resource sharing mechanisms

## Architectural Patterns

### Service Layer Architecture
Business logic encapsulated in service classes, keeping controllers thin and focused on HTTP concerns.

### Repository Pattern
Models serve as repositories with Eloquent ORM, providing data access abstraction.

### Job Queue Pattern
Long-running operations (workflow execution, token refresh) handled asynchronously via Laravel queues.

### Event-Driven Architecture
Webhooks and schedules trigger workflow executions, with audit logging capturing all system events.

### Graph-Based Execution
Workflows represented as directed graphs with nodes and connections, enabling complex branching and parallel execution.
