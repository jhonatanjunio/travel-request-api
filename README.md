<p align="center">
  <img src="https://github.com/jhonatanjunio/travel-request-api/blob/main/public/logo.png" alt="Travel Request API Logo" width="400">
</p>

<p align="center">
  <a href="https://laravel.com"><img src="https://img.shields.io/badge/Laravel-12.0-FF2D20?style=flat-square&logo=laravel&logoColor=white" alt="Laravel 12"></a>
  <a href="https://php.net"><img src="https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat-square&logo=php&logoColor=white" alt="PHP 8.2+"></a>
  <a href="https://docker.com"><img src="https://img.shields.io/badge/Docker-Powered-2496ED?style=flat-square&logo=docker&logoColor=white" alt="Docker"></a>
  <a href="https://github.com/jhonatanjunio/travel-request-api/actions"><img src="https://img.shields.io/badge/Tests-Passing-4CAF50?style=flat-square" alt="Tests Passing"></a>
  <a href="https://opensource.org/licenses/MIT"><img src="https://img.shields.io/badge/License-MIT-blue.svg?style=flat-square" alt="License: MIT"></a>
</p>

# Travel Request API

A Laravel microservice for managing corporate travel requests, featuring JWT authentication, role-based access control (user/admin), event-driven notifications and i18n support.

## Branches

This repository contains two versions:

| Branch | Description |
|--------|-------------|
| **`main`** | Strictly follows the challenge requirements — simplified 3-status flow, JWT auth, comprehensive tests |
| **`feature/enhanced-cancellation`** | Everything from `main` plus an enhanced multi-step cancellation workflow for approved requests (see [Future Improvements](#future-improvements)) |

## Technical Decisions

### Architecture

The project follows a layered architecture (Controller -> Service -> Repository -> Model) with clear separation of concerns:

- **Controllers** receive requests and delegate to the Service layer — no business logic
- **Services** hold business rules and orchestrate operations
- **Repositories** abstract data access via interfaces, enabling easy testing and swappable implementations
- **DTOs** transfer data between layers with strong typing
- **Policies** centralize authorization, separated from business logic
- **Form Requests** isolate validation from controllers
- **API Resources** standardize JSON responses
- **Events + Listeners** decouple status change notifications via event-driven architecture

### Authentication: JWT

Implemented JWT authentication using [`php-open-source-saver/jwt-auth`](https://github.com/PHP-Open-Source-Saver/jwt-auth), as specified in the challenge requirements. Features:

- Stateless token-based authentication
- Token refresh endpoint for seamless session extension
- Configurable TTL via environment variables (`JWT_TTL`, `JWT_REFRESH_TTL`)

### Status Model

The system uses 3 well-defined statuses via PHP Enum (`App\Enums\TravelRequestStatus`):

| Status | Description |
|--------|-------------|
| `requested` | Request created, awaiting review |
| `approved` | Request approved by administrator |
| `canceled` | Request canceled |

**Status transitions:**

```
requested  ──→  approved   (admin)
requested  ──→  canceled   (admin or owner via POST /cancel)
approved   ──→  canceled   (admin only)
canceled   ──→  (final state, no further transitions allowed)
```

**Business rules:**
- **Administrators** can update status to `approved` or `canceled` (challenge item 4)
- **Canceled requests cannot be modified** — any attempt to update a canceled request returns 403
- **The requester cannot change** their own request's status via the status update endpoint
- **The requester can cancel** their request via the dedicated `POST /cancel` endpoint, **only if status is `requested`** — fulfilling challenge item 5, which states that cancellation is only allowed when the request has not yet been approved
- Each user can only view their own requests; administrators can view all

### Internationalization (i18n)

The API supports multiple languages via the `Accept-Language` header. The default language is **English**, with **Portuguese (pt-BR)** also available:

```
# English responses (default)
Accept-Language: en

# Portuguese responses
Accept-Language: pt-BR
```

All messages are centralized in translation files (`lang/en/` and `lang/pt_BR/`), using Laravel's native localization system. This applies globally — including the interactive API documentation at `/docs/api`, which automatically adapts to the browser's language preference.

### Event-Driven Notifications

Status changes are handled through Laravel's event system:

1. `TravelRequestStatusUpdated` event is dispatched when a status changes
2. `SendTravelRequestNotification` listener (queued) sends the notification
3. `TravelRequestStatusChanged` notification delivers via both **mail** and **database** channels

This decoupled approach allows easy extension (webhooks, Slack notifications, etc.) without modifying the service layer.

## Requirements

- Docker and Docker Compose
- Git

## Quick Start

```bash
git clone https://github.com/jhonatanjunio/travel-request-api.git
cd travel-request-api
docker compose up -d --build
```

That's it. The Docker entrypoint automatically handles:
- Copying `.env.example` to `.env`
- Installing Composer dependencies
- Generating application key and JWT secret
- Waiting for MySQL to be ready
- Running migrations and seeders
- Setting up the testing database

The API will be available at: **`http://localhost:8000/api/v1`**

> **Note:** The first `docker compose up --build` may take a few minutes while dependencies are installed. Check progress with `docker compose logs -f travel-request-api`.

### Makefile Commands

| Command | Description |
|---------|-------------|
| `make setup` | Full setup from scratch (build + start + test) |
| `make test` | Run test suite |
| `make fresh` | Reset database with fresh migrations + seed |
| `make logs` | Follow container logs |
| `make shell` | Open shell in PHP container |
| `make stop` | Stop containers |

## Environment Variables

Key variables in `.env.example`:

| Variable | Description | Default |
|----------|-------------|---------|
| `DB_HOST` | Database host | `db` |
| `DB_DATABASE` | Database name | `travel_management` |
| `APP_LOCALE` | Default language | `en` |
| `JWT_TTL` | Token lifetime (minutes) | `60` |
| `JWT_REFRESH_TTL` | Refresh window (minutes) | `20160` |
| `MAIL_MAILER` | Mail driver | `log` |
| `QUEUE_CONNECTION` | Queue driver | `database` |

## Pre-configured Users

| Role | Email | Password |
|------|-------|----------|
| Administrator | admin@travelrequests.com | admin123 |
| Regular User | user@travelrequests.com | user123 |

## API Endpoints

### Authentication

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| POST | `/api/v1/auth/register` | Register new user | No |
| POST | `/api/v1/auth/login` | Login and get JWT token | No |
| POST | `/api/v1/auth/logout` | Invalidate token | Yes |
| GET | `/api/v1/auth/me` | Get authenticated user data | Yes |
| POST | `/api/v1/auth/refresh` | Refresh JWT token | Yes |

### Travel Requests

| Method | Endpoint | Description | Auth | Role |
|--------|----------|-------------|------|------|
| GET | `/api/v1/travel-requests` | List requests (with filters) | Yes | All |
| POST | `/api/v1/travel-requests` | Create travel request | Yes | All |
| GET | `/api/v1/travel-requests/{id}` | Request details | Yes | Owner/Admin |
| PATCH | `/api/v1/travel-requests/{id}` | Update status (approved/canceled) | Yes | Admin |
| POST | `/api/v1/travel-requests/{id}/cancel` | Cancel own request | Yes | Owner |

### Available Filters (GET /travel-requests)

| Parameter | Type | Description |
|-----------|------|-------------|
| `status` | string | Filter by status: `requested`, `approved`, `canceled` |
| `destination` | string | Partial search by destination |
| `start_date` | date | Start of creation date range |
| `end_date` | date | End of creation date range |
| `departure_date_start` | date | Start of departure date range |
| `departure_date_end` | date | End of departure date range |
| `per_page` | int | Items per page (1-100, default: 15) |

## API Documentation

Interactive API documentation is auto-generated and available at:

**`http://localhost:8000/docs/api`**

Powered by [Scramble](https://scramble.dedoc.co/) — automatically reads routes, form requests, and API resources to generate OpenAPI/Swagger documentation with zero manual annotations.

A Postman collection is also available:
[Travel Request API Collection](https://documenter.getpostman.com/view/2620805/2sAYk7S4V9)

## Automated Tests

The project includes a comprehensive test suite covering authentication, CRUD operations, filters, authorization, business rules, events and i18n.

### Running Tests

```bash
# Create test database (first time)
docker compose exec db mysql -u root -ptravel_password -e "CREATE DATABASE IF NOT EXISTS travel_management_testing;"

# Run migrations on test database
docker compose exec travel-request-api php artisan migrate --env=testing

# Run tests
docker compose exec travel-request-api php artisan test
```

### Test Coverage

| Area | Tests |
|------|-------|
| Authentication | Register, login, logout, profile, token refresh, protected routes |
| Creation | Valid fields, date validation, required fields |
| Listing | Own requests, all (admin), filters, pagination, empty list |
| Details | Own view, user isolation, admin access, 404 |
| Status Update | Approval, cancellation (admin), non-admin block, requester block |
| User Cancel | Cancel requested, block approved, block already canceled, block other user's |
| Events | Event dispatched on change, no event when unchanged |
| i18n | English default, Portuguese via Accept-Language |
| Model | Enum cast, accessor, relationship, date casts, canCancel logic |

## Future Improvements

A previous version of this project (available in the `feature/enhanced-cancellation` branch) includes an implementation of some of these features — I chose to simplify the main branch to strictly match the challenge scope while keeping the code clean and focused.

- **Cancellation of approved requests**: Multi-step flow where the requester can request cancellation of an already approved request (respecting a configurable time window, e.g., up to 2 days before departure). The request would transition through intermediate states (`awaiting_confirmation` -> `pending_cancellation`) with confirmation via signed URL/email token, and the administrator would approve or reject the cancellation. This logic was implemented in the previous version with dedicated Mailables (`CancellationRequested`, `CancellationApproved`, `CancellationRejected`), signed URLs, and per-user cancellation statistics
- **Cancellation statistics**: Per-user cancellation metrics to assist managers in decision-making
- **Webhooks**: Integration with external systems (ERP, corporate calendars) via real-time notifications
- **Granular rate limiting**: Differentiated limits per endpoint and user role
- **OpenAPI/Swagger documentation**: Automatic interactive API documentation generation
- **Audit trail**: Log of all status changes with timestamp and responsible user
- **Extended i18n**: Support for additional languages and full translation of Laravel validation messages

## License

This project is licensed under the [MIT License](https://opensource.org/licenses/MIT).
