# Mini CRM Pipeline

Customer Relationship Management (CRM) pipeline application built with Laravel and Livewire.

Built with Livewire Flux for dynamic UI interactions and Tailwind CSS for a clean, responsive interface.

---

## 🚀 Tech Stack

### Backend
- PHP `>= 8.3`
- Laravel `^13.7`
- Livewire `^4.1`
- Livewire Flux `^2.13.1`
- Laravel Fortify `^1.34`

### Frontend
- Tailwind CSS `^4.0.7`
- Vite `^8.0.0`

### Testing & Code Quality
- Pest PHP `^4.7`
- Laravel Pint `^1.27`
- FakerPHP

---

## 📋 Prerequisites

Ensure your local environment includes:

- PHP `>= 8.3`
- Composer
- Node.js & npm
- MySQL

---

## ⚙️ Installation & Setup

### 1. Clone the Repository

```bash
git clone https://github.com/pra7iksinh/mini-crm-pipeline.git
cd mini-crm-pipeline
```

### 2. Install Dependencies

```bash
composer install
npm install
```

### 3. Configure Environment

```bash
cp .env.example .env
php artisan key:generate
```

Update your `.env` file with your database credentials and application configuration.

### 4. Run Database Migrations

```bash
php artisan migrate
```
### 5. Run Database Seeders

```bash
php artisan db:seed
```

This creates a demo user and seeds **10 leads per pipeline stage** (40 leads total).

| Field    | Value              |
|----------|--------------------|
| Email    | test@example.com   |
| Password | Test105*           |


### 5. Build Frontend Assets
Build optimized frontend assets for production:
```bash
npm run build
```

### 6. Start the Development Server

```bash
composer run dev
```

Or run services separately:

```bash
php artisan serve
npm run dev
```

---

## 🧪 Running Tests

Run the automated test suite using Pest PHP:

```bash
composer run test
```

---

## 🎨 Code Formatting

Format the codebase using Laravel Pint:

```bash
composer run lint
```

Check formatting issues without modifying files:

```bash
composer run lint:check
```
