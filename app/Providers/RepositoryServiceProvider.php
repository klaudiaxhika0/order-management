<?php

namespace App\Providers;

use App\Repositories\Contracts\RepositoryInterface;
use App\Repositories\CustomerRepository;
use App\Repositories\OrderRepository;
use App\Repositories\OrderStatusRepository;
use App\Repositories\ProductRepository;
use App\Repositories\UserRepository;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Bind repositories to their interfaces
        $this->app->bind(RepositoryInterface::class, function ($app) {
            return $app->make(UserRepository::class);
        });

        // Bind specific repositories
        $this->app->bind(UserRepository::class, function ($app) {
            return new UserRepository($app->make(User::class));
        });

        $this->app->bind(CustomerRepository::class, function ($app) {
            return new CustomerRepository($app->make(Customer::class));
        });

        $this->app->bind(ProductRepository::class, function ($app) {
            return new ProductRepository($app->make(Product::class));
        });

        $this->app->bind(OrderRepository::class, function ($app) {
            return new OrderRepository($app->make(Order::class));
        });

        $this->app->bind(OrderStatusRepository::class, function ($app) {
            return new OrderStatusRepository($app->make(OrderStatus::class));
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
