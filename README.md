<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

# Handi - Service Discovery Management System

## About Handi

Handi is a web application built with Laravel that helps manage service discoveries and item tracking. It provides both web interface and API endpoints for creating and managing service discoveries.

## Features

-   Service Discovery Management

    -   Customer information tracking
    -   Detailed service descriptions
    -   Todo list management
    -   Cost calculation and tracking
    -   Image attachments support
    -   Notes for customers and internal use

-   Item Management

    -   Item catalog with search functionality
    -   Price tracking
    -   Custom pricing per discovery
    -   Quantity management

-   Cost Management
    -   Service cost tracking
    -   Transportation cost calculation
    -   Labor cost tracking
    -   Extra fees management
    -   Flexible discount system (rate or fixed amount)

## Installation

1. Clone the repository

```bash
git clone https://github.com/yourusername/handi.git
cd handi
```

2. Install dependencies

```bash
composer install
npm install
```

3. Set up environment file

```bash
cp .env.example .env
php artisan key:generate
```

4. Configure your database in `.env` file

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=handi
DB_USERNAME=root
DB_PASSWORD=
```

5. Run migrations

```bash
php artisan migrate
```

6. Set up storage link

```bash
php artisan storage:link
```

7. Start the development server

```bash
php artisan serve
```

## API Documentation

### Creating a Discovery

**Endpoint:** `POST /api/discovery`

**Headers:**

-   `Accept: application/json`
-   `Content-Type: multipart/form-data`
-   `Authorization: Bearer {token}`

**Parameters:**

-   `customer_name` (required): string
-   `customer_phone` (required): string
-   `customer_email` (required): email
-   `discovery` (required): string
-   `todo_list` (optional): string
-   `note_to_customer` (optional): string
-   `note_to_handi` (optional): string
-   `completion_time` (optional): integer
-   `offer_valid_until` (optional): date
-   `service_cost` (optional): decimal
-   `transportation_cost` (optional): decimal
-   `labor_cost` (optional): decimal
-   `extra_fee` (optional): decimal
-   `discount_rate` (optional): decimal
-   `discount_amount` (optional): decimal
-   `payment_method` (optional): string
-   `images[]` (optional): file
-   `items` (optional): array
    -   `id`: integer
    -   `quantity`: integer
    -   `custom_price`: decimal

## Security

-   CSRF protection enabled
-   Form validation
-   File upload restrictions
-   Authentication required for all actions

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
