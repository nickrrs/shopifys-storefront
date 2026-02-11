# Shopify Storefront

A modern Laravel + Vue.js application for managing and synchronizing Shopify stores and products through a GraphQL API.

## Table of Contents

- [Overview](#overview)
- [Features](#features)
- [Technology Stack](#technology-stack)
- [Architecture](#architecture)
- [Development Practices](#development-practices)
- [Getting Started](#getting-started)
- [Testing](#testing)
- [Project Structure](#project-structure)

## Overview

This project provides a dashboard interface for users to connect their Shopify stores, view store details, and synchronize products from Shopify's API. The application uses a GraphQL API (Laravel Lighthouse) for all backend operations and Inertia.js to bridge Laravel with Vue.js, creating a seamless single-page application experience.

### Purpose

The application serves as a centralized platform for Shopify store owners who need to:
- Connect multiple Shopify stores using domain and access tokens
- View store information and product catalogs
- Synchronize product data from Shopify to a local database

### Scope & Limitations

**In Scope:**
- User authentication (registration, login, logout)
- Store connection and management
- Product synchronization from Shopify GraphQL API on background jobs
- User profile and password management
- Account deletion

**Improvements to make:**
- Real-time product updates (sync is manual/on-demand)
- Order management
- Inventory tracking beyond basic quantity
- Shopify webhook handling

### Business Rules

1. **Store Connection**: Users must provide a valid Shopify domain and access token. The system validates the connection by fetching shop details from Shopify's API.
2. **Product Sync**: Product synchronization runs as a background job to prevent blocking the user interface. Only one sync can run per store at a time (tracked via `syncing` flag).
3. **Data Storage**: Products are stored locally with a reference to their Shopify product ID (`shopify_product_id`) to enable updates on subsequent syncs.
4. **Security**: Access tokens are encrypted at rest using Laravel's encrypted cast. Tokens are never exposed in API responses or frontend components.

## Features

- **Authentication System**
  - User registration with email/password
  - Session-based login/logout
  - Password-protected account deletion

- **Store Management**
  - Connect new Shopify stores via domain and access token
  - View connected stores with details (name, domain, connection date)
  - Edit store names
  - Track sync status per store

- **Product Management**
  - View products from connected stores
  - Cursor-based pagination with infinite scroll
  - Product details: title, description, price, inventory, status
  - Manual product synchronization from Shopify

- **User Settings**
  - Update profile (name, email)
  - Change password with current password verification
  - Delete account with password confirmation

## Technology Stack

### Backend

- **Laravel 12** - PHP framework providing routing, ORM, queues, and middleware
- **Laravel Lighthouse** - GraphQL server implementation for Laravel
- **Laravel Inertia** - Server-side adapter for Inertia.js, enabling SPA experience without API boilerplate
- **Laravel Queues** - Background job processing for product synchronization
- **PostgreSQL** - Primary database (configurable to MySQL/SQLite)

### Frontend

- **Vue.js 3** - Progressive JavaScript framework with Composition API
- **Inertia.js** - Client-side adapter, enabling Vue components to work seamlessly with Laravel routes
- **TypeScript** - Type safety and improved developer experience
- **Tailwind CSS 4** - Utility-first CSS framework for rapid UI development
- **Lucide Vue Next** - Icon library
- **Reka UI** - Headless UI component library

### Development Tools

- **Vite** - Fast build tool and dev server
- **Vitest** - Fast unit testing framework (replaces Playwright for composable testing)
- **Pest PHP** - Elegant PHP testing framework
- **ESLint + Prettier** - Code quality and formatting
- **Laravel Pint** - PHP code style fixer

### Why These Technologies?

- **GraphQL over REST**: Provides flexible queries, reduces over-fetching, and enables a single endpoint for all operations
- **Inertia.js**: Eliminates the need for a separate API layer while maintaining SPA benefits (no page reloads, shared state)
- **Vue 3 Composition API**: Better code organization, reusability, and TypeScript support
- **Laravel Queues**: Ensures product sync doesn't block user requests, improving UX
- **Vitest**: Fast unit tests for frontend logic without browser overhead

## Architecture

### Backend Architecture

The application follows **Laravel's MVC pattern** with **GraphQL** as the API layer:

```
Request → GraphQL Schema → Mutation/Query Resolver → Service Layer → Model → Database
```

**Key Components:**

1. **GraphQL Schema** (`graphql/`): Feature-based organization
   - `auth/` - Authentication mutations (login, register, logout)
   - `user/` - User profile mutations and queries
   - `store/` - Store management mutations and queries
   - `product/` - Product types and enums

2. **Resolvers** (`app/GraphQL/`): Handle GraphQL operations
   - `Mutations/` - Business logic for mutations
   - `Queries/` - Data fetching logic

3. **Services** (`app/Services/`): Encapsulate external API interactions
   - `Shopify/ShopifyGraphqlClient` - HTTP client for Shopify GraphQL API
   - `Shopify/ShopifyStoreConnector` - Store connection logic

4. **Jobs** (`app/Jobs/`): Background processing
   - `SyncProductsJob` - Fetches and syncs products from Shopify

5. **Models** (`app/Models/`): Eloquent ORM models with relationships

### Frontend Architecture

The frontend uses **Composition API** with **composables** for reusable logic:

```
Page Component → Composable → GraphQL Client → Backend
```

**Key Patterns:**

1. **Composables** (`resources/js/composables/`): Reusable reactive logic
   - `useAuthLogin`, `useAuthRegister`, `useAuthLogout` - Authentication
   - `useStores`, `useConnectStore`, `useUpdateStore`, `useSyncStore` - Store management
   - `useProfileUpdate`, `usePasswordUpdate`, `useDeleteAccount` - User settings
   - `useToast` - Global toast notification system

2. **GraphQL Client** (`resources/js/lib/graphql.ts`): Centralized request wrapper
   - Handles CSRF token management
   - Error handling (network, HTTP, GraphQL)
   - Type-safe request/response handling

3. **Pages** (`resources/js/pages/`): Inertia page components
   - `Welcome.vue` - Login page
   - `Dashboard.vue` - Main store/product dashboard
   - `settings/` - User settings pages

4. **Components** (`resources/js/components/`): Reusable UI components
   - `StoreCard.vue` - Store display card
   - `ProductTable.vue` - Product listing with infinite scroll
   - `ToastContainer.vue` - Toast notification display

### Folder Organization

```
shopifys-storefront/
├── app/
│   ├── GraphQL/          # GraphQL resolvers (mutations/queries)
│   ├── Jobs/             # Background jobs
│   ├── Models/           # Eloquent models
│   ├── Services/         # External API services
│   └── Http/             # Middleware, controllers (minimal)
├── graphql/              # GraphQL schema files (feature-based)
│   ├── auth/
│   ├── user/
│   ├── store/
│   └── product/
├── resources/
│   └── js/
│       ├── composables/  # Vue composables (business logic)
│       ├── components/   # Vue components (UI)
│       ├── lib/          # Utilities (GraphQL client)
│       └── pages/        # Inertia pages
├── routes/
│   └── web.php          # Laravel routes (Inertia)
├── tests/               # Backend tests (Pest PHP)
└── resources/js/**/__tests__/  # Frontend tests (Vitest)
```

**Why This Organization?**

- **Feature-based GraphQL schema**: Easier to locate and maintain related types/mutations
- **Composables separation**: Business logic isolated from UI components
- **Service layer**: External API calls abstracted for testability
- **Test co-location**: Frontend tests next to source files for better discoverability

## Development Practices

### Code Quality Principles

1. **SOLID Principles**
   - **Single Responsibility**: Each composable/service handles one concern
   - **Open/Closed**: Services are extensible without modification
   - **Dependency Injection**: Resolvers and services use constructor injection
   - **Interface Segregation**: Services define clear contracts

2. **DRY (Don't Repeat Yourself)**
   - Shared GraphQL client (`requestWrapper`)
   - Reusable composables for common operations
   - Centralized error handling

3. **KISS (Keep It Simple, Stupid)**
   - Direct GraphQL mutations (no unnecessary abstraction layers)
   - Simple reactive state management (no Vuex/Pinia)
   - Straightforward component hierarchy

### Security Practices

- **CSRF Protection**: All GraphQL requests include XSRF token from cookies
- **Encrypted Storage**: Access tokens stored with Laravel's `encrypted` cast
- **Input Validation**: Laravel validation in GraphQL resolvers
- **Session-based Auth**: Uses Laravel's `web` guard (no token management)

### Testing Strategy

- **Backend (Pest PHP)**: Unit tests for models/services, feature tests for GraphQL operations
- **Frontend (Vitest)**: Unit tests for composables and GraphQL client
- **Mocking**: External API calls mocked in tests (`Http::fake()` for Shopify)

## Getting Started

### Prerequisites

- **PHP 8.2+** with extensions: `pdo_pgsql`, `mbstring`, `xml`, `curl`
- **Composer** 2.x
- **Node.js** 22.x and npm
- **PostgreSQL** 14+ (or MySQL 8+ / SQLite)
- **Docker & Docker Compose** (optional, for Laravel Sail)

### Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd shopifys-storefront
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install Node dependencies**
   ```bash
   npm install
   ```

4. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Configure database** in `.env`:
   ```env
   DB_CONNECTION=pgsql
   DB_HOST=127.0.0.1
   DB_PORT=5432
   DB_DATABASE=shopifys_storefront
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

6. **Run migrations**
   ```bash
   php artisan migrate
   ```

7. **Build frontend assets**
   ```bash
   npm run build
   ```
8. **Get your Shopify credentials**: To connect a store, you'll need:
   - A Shopify store domain (e.g., `your-store.myshopify.com`)
   - An access token from a Shopify app (create a custom app in your Shopify admin to generate one)
   
   For testing purposes, you can create a development store at [partners.shopify.com](https://partners.shopify.com) or use an existing store.

### Running the Application

#### Option 1: Laravel Sail (Docker)

```bash
# Start services (database, queue, etc.)
./vendor/bin/sail up -d

# Run migrations
./vendor/bin/sail artisan migrate

# Start development server
./vendor/bin/sail artisan serve
```

#### Option 2: Local Development

```bash
# Start Laravel server
php artisan serve

# Start Vite dev server (in another terminal)
npm run dev

# Start queue worker (in another terminal)
php artisan queue:work
```

#### Option 3: All-in-One (Concurrently)

```bash
composer run dev
```

This starts:
- Laravel server (`php artisan serve`)
- Queue worker (`php artisan queue:listen`)
- Log viewer (`php artisan pail`)
- Vite dev server (`npm run dev`)

The application will be available at `http://localhost:8000`

### Available Commands

#### Backend (Laravel)

```bash
# Development
php artisan serve              # Start development server
php artisan queue:work          # Process background jobs
php artisan migrate             # Run migrations
php artisan migrate:fresh       # Reset database and run migrations

# Code Quality
composer run lint               # Run Laravel Pint (PHP formatting)
composer run test               # Run backend tests (Pest)

# Build
php artisan optimize            # Cache config/routes
php artisan view:cache          # Cache views
```

#### Frontend (Node)

```bash
# Development
npm run dev                     # Start Vite dev server with HMR
npm run build                   # Build for production
npm run build:ssr               # Build with SSR support

# Code Quality
npm run lint                    # Run ESLint and fix issues
npm run format                  # Format code with Prettier
npm run format:check            # Check formatting without fixing

# Testing
npm test                        # Run Vitest tests once
npm run test:watch              # Run Vitest in watch mode
```

## Testing

### Backend Tests (Pest PHP)

Backend tests cover:
- **Unit Tests**: Model relationships, accessors/mutators, service logic
- **Feature Tests**: GraphQL mutations/queries, authentication flows, store operations

```bash
# Run all backend tests
composer run test

# Run specific test file
php artisan test tests/Feature/GraphQL/Auth/LoginTest.php

# Run with coverage
php artisan test --coverage
```

**Test Structure:**
- `tests/Unit/` - Model and service unit tests
- `tests/Feature/` - GraphQL endpoint feature tests

### Frontend Tests (Vitest)

Frontend tests cover:
- **Composable Tests**: Authentication, store management, user settings
- **GraphQL Client Tests**: Request handling, error scenarios, CSRF token management
- **Toast System Tests**: Notification display and auto-removal

```bash
# Run all frontend tests
npm test

# Run in watch mode
npm run test:watch

# Run with coverage
npm test -- --coverage
```

**Test Structure:**
- `resources/js/**/__tests__/` - Tests co-located with source files

### Test Coverage

- **Backend**: 56 tests covering models, services, jobs, and GraphQL operations
- **Frontend**: 54 tests covering composables and GraphQL client

## Project Structure

### Key Directories

```
app/
├── GraphQL/
│   ├── Mutations/          # GraphQL mutation resolvers
│   │   ├── Auth/          # Login, Register, Logout
│   │   ├── User/          # UpdateProfile, UpdatePassword, DeleteAccount
│   │   └── Shopify/       # ConnectStore, UpdateStore, SyncStoreProducts
│   └── Queries/           # GraphQL query resolvers
├── Jobs/
│   └── SyncProductsJob.php  # Background product synchronization
├── Models/                 # Eloquent models (User, Store, Product)
└── Services/
    └── Shopify/            # Shopify API integration

graphql/
├── schema.graphql          # Main schema (imports feature schemas)
├── auth/                   # Auth types, inputs, mutations
├── user/                   # User types, inputs, mutations
├── store/                  # Store types, inputs, mutations
└── product/                # Product types, enums

resources/js/
├── composables/            # Vue composables (business logic)
├── components/             # Reusable Vue components
├── lib/
│   └── graphql.ts          # GraphQL request wrapper
└── pages/                  # Inertia page components

tests/
├── Unit/                   # Backend unit tests
└── Feature/                # Backend feature tests
```

## Additional Notes

### Queue Configuration

Product synchronization runs asynchronously. Ensure a queue worker is running:

```bash
php artisan queue:work
```

Or use Laravel Horizon

### Environment Variables

Key environment variables:

```env
APP_ENV=local
APP_DEBUG=true
DB_CONNECTION=pgsql
QUEUE_CONNECTION=database  # or 'redis' for production
```

### GraphQL Endpoint

All GraphQL operations are available at:
```
POST /graphql
```

The endpoint uses Laravel's `web` middleware for session-based authentication.

## License

This project is open-sourced software licensed under the [MIT license](LICENSE).
