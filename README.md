# Order Management System

A comprehensive Laravel-based order management system with a modern frontend interface, built for managing customers, products, and orders with full CRUD operations, JWT authentication, and real-time dashboard statistics.

## 🎯 Features

- **📊 Dashboard** - Real-time statistics and order management
- **👥 Customer Management** - Full CRUD operations with validation
- **📦 Product Management** - Complete product lifecycle management
- **🛒 Order Management** - Order creation, status tracking, and updates
- **🔐 JWT Authentication** - Secure API access with Laravel Sanctum
- **🧪 Comprehensive Testing** - 43 unit tests with 100% pass rate
- **🐳 Docker Ready** - Complete containerized development environment
- **📱 Responsive UI** - Modern Bootstrap-based frontend

## 🚀 Quick Start

### Prerequisites
- Docker and Docker Compose installed
- Git

### Setup
1. Clone the repository
2. Copy `.env.example` to `.env` and configure
3. Set up MySQL database
4. Configure environment variables
### Environment Variables
```bash
# Database
DB_CONNECTION=mysql
DB_HOST=your_host
DB_DATABASE=order_management
DB_USERNAME=your_username
DB_PASSWORD=your_password

```
5. Run docker setup script
```bash
   cd order-management
   ./docker-setup.sh
   ```

6. **Access the application**
   - **Application**: http://localhost:8080
   - **Login**: admin@management.web / Admin1234!

## 📝 API Endpoints

### Authentication
- `POST /api/login` - User login
- `POST /api/logout` - User logout

### Customers
- `GET /api/customers` - List customers
- `POST /api/customers` - Create customer
- `GET /api/customers/{id}` - Get customer
- `PUT /api/customers/{id}` - Update customer
- `DELETE /api/customers/{id}` - Delete customer

### Products
- `GET /api/products` - List products
- `POST /api/products` - Create product
- `GET /api/products/{id}` - Get product
- `PUT /api/products/{id}` - Update product
- `DELETE /api/products/{id}` - Delete product

### Orders
- `GET /api/orders` - List orders
- `POST /api/orders` - Create order
- `GET /api/orders/{id}` - Get order
- `PUT /api/orders/{id}` - Update order
- `PUT /api/orders/{id}/status` - Update order status
- `DELETE /api/orders/{id}` - Delete order

### Dashboard
- `GET /api/dashboard/stats` - Get statistics
- `GET /api/dashboard/order-status-summary` - Get order status summary

## 🧪 Testing

```bash
# Run all tests
docker-compose exec app php artisan test
```

## 🏗️ Architecture

### Backend (Laravel)
- **Framework**: Laravel 10.x
- **Authentication**: Laravel Sanctum (JWT)
- **Database**: MySQL with Eloquent ORM
- **Patterns**: Repository Pattern, Dependency Injection
- **Testing**: PHPUnit with Feature and Unit tests
- **API**: RESTful endpoints with comprehensive validation

### Frontend
- **Templates**: Blade with Bootstrap 5
- **JavaScript**: Vanilla JS with Axios for API calls
- **UI Components**: Responsive design with Font Awesome icons
- **Authentication**: Client-side token management

### Database Schema
- **Users** - Authentication and user management
- **Customers** - Customer information and contact details
- **Products** - Product catalog with pricing
- **Orders** - Order management with status tracking
- **Order Statuses** - Configurable order status workflow
- **Order Products** - Many-to-many relationship for order items

## 🔧 Technical Details

### Design Patterns
- **Repository Pattern** - Data access abstraction
- **Dependency Injection** - Service container integration
- **Factory Pattern** - Test data generation
- **Observer Pattern** - Model event handling

### Security Features
- **JWT Token Expiration** - 60-minute token lifetime
- **Input Validation** - Comprehensive request validation
- **CORS Protection** - Cross-origin request security
- **Rate Limiting** - API request throttling
- **SQL Injection Prevention** - Eloquent ORM protection

### Testing Coverage
- **Authentication Tests** - Login, logout, token validation
- **API Endpoint Tests** - All CRUD operations
- **Token Expiration Tests** - Security validation
- **Validation Tests** - Input validation coverage
- **Integration Tests** - End-to-end functionality

```
