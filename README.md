# 💰 Finance API – Personal Finance Management

A RESTful API for **personal finance management**, built with **Laravel**.  
It allows users to track **incomes and expenses**, organize them into **categories**, and generate **monthly reports** with ease and security.

---

## 🚀 Features
- User authentication with **Laravel Sanctum**
- CRUD for financial transactions (incomes & expenses)
- Category management (default + custom)
- Reports:
  - Monthly summary (incomes, expenses, balance)
  - Totals grouped by category
- Export data in **CSV/JSON**
- Soft deletes for transactions
- API documentation with Swagger (l5-swagger)
- Automated tests (Feature and Unit)

---

## 🛠️ Tech Stack
- [Laravel 12](https://laravel.com/)
- [Laravel Sanctum](https://laravel.com/docs/10.x/sanctum) – token-based authentication
- [MySQL/PostgreSQL](https://www.postgresql.org/) – database
- [Swagger (l5-swagger)](https://github.com/DarkaOnLine/L5-Swagger) – API docs
- [Pest / PHPUnit](https://pestphp.com/) – testing

---

## 📂 Data Model
- **Users (users)**  
- **Transactions (transactions)** → description, amount, type (income/expense), date, category, user  
- **Categories (categories)** → e.g. Food, Transport, Salary, Entertainment  

Relationships:  
- `User hasMany Transactions`  
- `Transaction belongsTo User`  
- `Category hasMany Transactions`  
- `Transaction belongsTo Category`  

---

## 📌 Main Endpoints

### Authentication
- `POST /register` → Register a new user  
- `POST /login` → Login and generate token  
- `POST /logout` → Logout and revoke token  

### Transactions
- `GET /transactions` → List all user’s transactions  
- `POST /transactions` → Create a new transaction  
- `GET /transactions/{id}` → Get transaction details  
- `PUT /transactions/{id}` → Update a transaction  
- `DELETE /transactions/{id}` → Delete a transaction  

### Reports
- `GET /reports/monthly` → Current month summary  
- `GET /reports/monthly?month=08&year=2025` → Specific month summary  
- `GET /reports/by-category` → Totals grouped by category  

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
