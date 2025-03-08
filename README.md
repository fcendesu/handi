Handi: Discovery Management System

## About Handi

Handi is a professional web application built with Laravel that streamlines service discovery management and item tracking for service-based businesses. It offers both a comprehensive web interface and robust API endpoints for creating and managing service discoveries.

## Features

### Service Discovery Management

-   **Customer Information Management**: Track and organize customer details efficiently
-   **Discovery Documentation**: Create detailed service descriptions and assessments
-   **Task Management**: Maintain organized todo lists for each service discovery
-   **Financial Tracking**: Comprehensive cost calculation and tracking system
-   **Visual Documentation**: Support for multiple image attachments
-   **Communication Tools**: Separate notes for customer communication and internal use

### Item Management

-   **Dynamic Item Catalog**: Full-featured catalog with powerful search functionality
-   **Price Management**: Track and update item pricing
-   **Flexible Pricing**: Set custom prices per discovery when needed
-   **Inventory Control**: Manage item quantities effectively

### Cost Management

-   **Service Cost Tracking**: Track base service costs
-   **Transportation Expenses**: Calculate and record transportation costs
-   **Labor Cost Management**: Track labor expenses separately
-   **Additional Expense Handling**: Manage extra fees as needed
-   **Advanced Discount System**: Apply discounts as either percentage rates or fixed amounts

## Installation

1. Clone the repository

```bash
git clone https://github.com/fcendesu/handi.git
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

4. Configure your database in .env file

```env
DB_CONNECTION=sqlite
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

## Technical Details

### Cost Calculation

Handi implements a sophisticated cost calculation system that:

-   Sums base costs (service, transportation, labor, and extra fees)
-   Applies percentage-based discounts to the base amount
-   Adds costs of all associated items with their quantities
-   Applies any fixed discount amounts to the final total

### Database Structure

-   **Discoveries**: Stores all service discovery information including customer details and cost data
-   **Items**: Catalogs all available items with their standard pricing
-   **Discovery-Item Relationship**: Tracks which items are associated with each discovery, including quantities and custom pricing when applicable

## Security

-   CSRF protection enabled
-   Comprehensive form validation
-   Secure file upload restrictions
-   Authentication required for all actions

## License

This project is open-source software licensed under the [MIT license](https://opensource.org/licenses/MIT).
