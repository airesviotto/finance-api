# 💰 Finance API – Personal Finance Management

#### A RESTful API for personal finance management, built with Laravel 12.
#### Track incomes, expenses, categories, generate reports, and monitor user activity logs with advanced security.

# 🚀 Key Features

#### Token-based authentication with Laravel Sanctum

#### Roles & Permissions for fine-grained access control

#### Audit Trail: logs with payload, user agent, status code, and request duration (duration_ms)

#### Rate Limiting per route to prevent abuse

#### Full CRUD for Transactions and Categories

#### Advanced reports: monthly average, category comparison, top expenses

#### Jobs & Notifications for alerts and reports

#### Soft deletes for Transactions, Categories, and Users

#### Ready for SaaS expansion

# 📂 Database Structure
| Table                            | Description                                            |
| -------------------------------- | ------------------------------------------------------ |
| `activity_logs`                  | User activity logs (audit trail)                       |
| `cache`, `cache_locks`           | Laravel cache tables                                   |
| `categories`                     | Transaction categories (Food, Transport, Salary, etc.) |
| `failed_jobs`, `jobs`            | Laravel queue & jobs management                        |
| `migrations`                     | Database migrations                                    |
| `notifications`                  | Laravel notifications                                  |
| `password_reset_tokens`          | Password reset tokens                                  |
| `permissions`, `permission_role` | Roles & permissions system                             |
| `personal_access_tokens`         | Sanctum token storage                                  |
| `roles`, `role_user`             | Role management & pivot table                          |
| `sessions`                       | Laravel session table                                  |
| `transactions`                   | User transactions (soft deletes enabled)               |
| `users`                          | User table (soft deletes enabled)                      |


### ER Diagram
User ---< Transaction >--- Category
User ---< Role >---< Permission
Transaction logs in activity_logs

Simple ASCII diagram. Can replace with actual ER diagram image if desired.

### Relationships

User → hasMany Transactions

Transaction → belongsTo User

Category → hasMany Transactions

Transaction → belongsTo Category

# 🔒 Authentication & Permissions

Login: POST /login → returns Sanctum token with user abilities

Protecting routes: middleware auth:sanctum + abilities:<permission>

Logout: POST /logout → revokes token

Example:

Route::post('/transactions', [TransactionController::class, 'store'])
    ->middleware(['auth:sanctum', 'abilities:create_transaction']);

# 📌 Main Endpoints
## Authentication
| Method | Endpoint | Description                   |
| ------ | -------- | ----------------------------- |
| POST   | /login   | Authenticate and return token |
| POST   | /logout  | Revoke current token          |


## Transactions
| Method | Endpoint           | Ability Required    |
| ------ | ------------------ | ------------------- |
| GET    | /transactions      | view\_transaction   |
| POST   | /transactions      | create\_transaction |
| GET    | /transactions/{id} | view\_transaction   |
| PUT    | /transactions/{id} | update\_transaction |
| DELETE | /transactions/{id} | delete\_transaction |


## Categories
| Method | Endpoint         | Ability Required |
| ------ | ---------------- | ---------------- |
| GET    | /categories      | view\_category   |
| POST   | /categories      | create\_category |
| GET    | /categories/{id} | view\_category   |
| PUT    | /categories/{id} | update\_category |
| DELETE | /categories/{id} | delete\_category |


## Reports
| Method | Endpoint                    | Description                     |
| ------ | --------------------------- | ------------------------------- |
| GET    | /report/monthly-average     | Monthly average of transactions |
| GET    | /report/category-comparison | Compare categories              |
| GET    | /report/top-expenses        | Top expenses                    |


## Activity Logs (Admin)
| Method | Endpoint                  | Description                                                        |
| ------ | ------------------------- | ------------------------------------------------------------------ |
| GET    | /logs/activity-logs       | List all activity logs                                             |
| GET    | /logs/activity-logs/stats | Aggregated metrics: avg duration, top endpoints, requests per user |


# ▶️ Getting Started
## 1. Clone repository
git clone https://github.com/airesviotto/finance-api.git
cd finance-api

## 2. Install dependencies
composer install

## 3. Configure environment
cp .env.example .env
php artisan key:generate
## Update .env with database credentials

## 4. Run migrations & seeders
php artisan migrate --seed

## 5. Start server
php artisan serve

API available at: http://127.0.0.1:8000

🧪 Running Tests
php artisan test

## 📝 Interactive API Documentation

You can explore all endpoints interactively using Swagger UI:

### Online Swagger Editor
Open the Swagger JSON in the [Swagger Editor](https://editor.swagger.io/):
[Open in Swagger Editor](https://editor.swagger.io/?url=https://raw.githubusercontent.com/airesviotto/finance-api/main/docs/api-docs.json)

### Local Swagger UI
1. Serve the API locally:  
php artisan serve

### Swagger UI available at:
/api/documentation

This UI is interactive: you can test requests and see example responses.

## 👨‍💻 Author
Aires Viotto 🚀
Fullstack developer passionate about secure and scalable APIs
#### GitHub: github.com/airesviotto

# 💡 Highlights for recruiters:
#### ✅ Advanced security: Roles, Permissions, Rate Limiting
#### ✅ Audit trail with request duration, status, payload
#### ✅ Ready for SaaS & scalable architecture
#### ✅ Fully documented API with Swagger
