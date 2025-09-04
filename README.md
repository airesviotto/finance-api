# 💰 Finance API – Personal Finance Management

A RESTful API for **personal finance management**, built with **Laravel**.  
It allows users to track **incomes and expenses**, organize them into **categories**, and generate **monthly reports** with ease and security.

---

## 🚀 Features
- JWT-like authentication using Laravel Sanctum
- Roles & Permissions for fine-grained access control
- Soft deletes for transactions, categories, and users
- Full CRUD for Transactions and Categories
- Example users: Admin and User
- Ready for SaaS expansion

---

## 🛠️ Tech Stack
- [Laravel 12](https://laravel.com/)
- [Laravel Sanctum](https://laravel.com/docs/10.x/sanctum) – token-based authentication
- [MySQL/PostgreSQL](https://www.postgresql.org/) – database
- [Swagger (l5-swagger)](https://github.com/DarkaOnLine/L5-Swagger) – API docs
- [Pest / PHPUnit](https://pestphp.com/) – testing

---

## Database Structure
ER Diagram

### Tables

- users: stores user data, soft deletes enabled
- roles: defines roles (Admin, User)
- permissions: defines granular actions (create_transaction, delete_transaction, etc.)
- role_user: pivot table linking users to roles
- permission_role: pivot table linking roles to permissions
- categories: transaction categories (Food, Transport, Salary, etc.)
- transactions: user-specific transactions, soft deletes enabled
- password_reset_tokens, sessions, personal_access_tokens: Laravel default tables for auth flows

## 📂 Data Model
- **Users (users)**  
- **Transactions (transactions)** → description, amount, type (income/expense), date, category, user  
- **Categories (categories)** → e.g. Food, Transport, Salary, Entertainment  

### Relationships:  
- `User hasMany Transactions`  
- `Transaction belongsTo User`  
- `Category hasMany Transactions`  
- `Transaction belongsTo Category`  

---

## Authentication & Permissions (Sanctum Integration)

### 1 - Login

- POST /login with email/password
- System collects all roles & permissions of the user

Creates Sanctum token with abilities:
{
  "user": { "id": 1, "name": "Admin User", "email": "admin@example.com" },
  "token": "1|sQ9z9ljslKJDS8asdj...",
  "abilities": ["create_transaction","view_transaction","delete_transaction","manage_users"]
}

### 2 - Protecting Routes
Example:

Route::post('/transactions', [TransactionController::class, 'store'])
     ->middleware(['auth:sanctum', 'abilities:create_transaction']);

Only users with the corresponding ability can access the endpoint.

### 3 - Logout
POST /logout revokes the current token

## 📌 Main Endpoints

### Authentication
- Method	Endpoint	Description
- POST	/login	Authenticate user and return token
- POST	/logout	Revoke current token

### Reports
- Method	Endpoint	Ability Required
- GET	/categories	view_category
- POST	/categories	create_category
- GET	/categories/{id}	view_category
- PUT	/categories/{id}	edit_category
- DELETE	/categories/{id}	delete_category

### Transactions
- Method	Endpoint	Ability Required
- GET	/transactions	view_transaction
- POST	/transactions	create_transaction
- GET	/transactions/{id}	view_transaction
- PUT	/transactions/{id}	create_transaction (or edit_transaction)
- DELETE	/transactions/{id}	delete_transaction

---

## ▶️ Getting Started

### 1. Clone the repository
```bash
git clone https://github.com/airesviotto/finance-api.git
cd finance-api

2. Install dependencies
bash
composer install

3. Configure environment
bash
cp .env.example .env
php artisan key:generate
Update .env with your database credentials.

4. Run migrations & seeders
bash
php artisan migrate --seed

5. Start the server
bash
php artisan serve
API will be available at: http://127.0.0.1:8000

🧪 Running Tests
bash
php artisan test
📖 API Documentation
Swagger UI available at:

bash
/api/documentation
☁️ Deployment
Example:
👉 https://finance-api.onrender.com

👨‍💻 Author
Developed by Aires Viotto 🚀
