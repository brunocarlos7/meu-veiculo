# Meu Veículo em Dia 🚗

Application to track vehicle costs, fuel efficiency, and maintenance. Built with Laravel 12 (API), Vue 3, and TailwindCSS.

## Features

- **Auth**: User registration and login (Sanctum).
- **Vehicles**: Manage multiple vehicles (Brand, Model, Year, Fuel Type).
- **Combustível**: Track refuelling (Liters, Price, Odometer, Full Tank).
- **Dashboard**: View total costs, monthly spending, and vehicle count.

## Prerequisites

- PHP 8.2+
- Composer
- Node.js & NPM
- SQLite (enabled in `php.ini`)

## Setup

1.  **Install PHP Dependencies**:
    ```bash
    composer install
    ```

2.  **Install Node Dependencies**:
    ```bash
    npm install
    ```

3.  **Environment Setup**:
    ```bash
    cp .env.example .env
    php artisan key:generate
    touch database/database.sqlite
    php artisan migrate
    ```

## Running the Application

You need two terminals:

1.  **Frontend (Vite dev server)**:
    ```bash
    npm run dev
    ```

2.  **Backend (Laravel server)**:
    ```bash
    php artisan serve
    ```

Access the application at `http://localhost:8000`.

## API Documentation

- `POST /api/register` - Create account
- `POST /api/login` - Authenticate
- `GET /api/vehicles` - List vehicles
- `POST /api/vehicles` - Add vehicle
- `GET /api/fuel-entries` - List fuel entries
