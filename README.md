# 💰 FinTrack API (Laravel 12)

A modern **personal finance management API** built with Laravel 12, allowing users to track **income, expenses**, and view **summary dashboards** of their financial health.

---

## 🚀 Features

- 🔐 **User Authentication** (Register, Login, Logout using Sanctum)
- 💸 **Income Management** – Track multiple income sources
- 💰 **Expense Management** – Categorize and manage expenses
- 📊 **Dashboard Summary** – View balance, total income/expenses, and categorized stats
- 🧩 **RESTful API Structure** with slug-based routes for cleaner URLs

---

## ⚙️ Tech Stack

- **Backend:** Laravel 12 (PHP 8.3)
- **Database:** MySQL
- **Auth:** Laravel Sanctum
- **API Testing:** Postman

---

## 🧠 API Endpoints

### 🔑 Authentication
| Method | Endpoint | Description |
|--------|-----------|-------------|
| POST | `/api/register` | Register new user |
| POST | `/api/login` | Login user |
| POST | `/api/logout` | Logout authenticated user |
| GET  | `/api/me` | Get logged-in user details |

### 💸 Income
| Method | Endpoint | Description |
|--------|-----------|-------------|
| GET | `/api/incomes` | List all incomes |
| POST | `/api/incomes` | Create income |
| GET | `/api/incomes/{slug}` | View income details |
| PUT | `/api/incomes/{slug}` | Update income |
| DELETE | `/api/incomes/{slug}` | Delete income |

### 💰 Expense
| Method | Endpoint | Description |
|--------|-----------|-------------|
| GET | `/api/expenses` | List all expenses |
| POST | `/api/expenses` | Create expense |
| GET | `/api/expenses/{slug}` | View expense details |
| PUT | `/api/expenses/{slug}` | Update expense |
| DELETE | `/api/expenses/{slug}` | Delete expense |

### 📊 Dashboard
| Method | Endpoint | Description |
|--------|-----------|-------------|
| GET | `/api/summary` | Summary of income & expenses |
| GET | `/api/summary/categories` | Summary by category |
| GET | `/api/dashboard` | Dashboard overview |

---

## 🧰 Setup Instructions

1. Clone the repository:
   ```bash
   git clone https://github.com/yourusername/fintrack-api.git
   cd fintrack-api
