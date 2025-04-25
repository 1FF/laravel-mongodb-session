# Laravel Mongodb Session driver

A MongoDB session driver for Laravel

| **Laravel<br/>Version** | **Package<br/>Version** | **Install using<br/>this command**                |
|-------------------------|-------------------------|---------------------------------------------------|
| 10.x                    | 5.x.x                   | composer require 1ff/laravel-mongodb-session:^5.0 |
| 9.x                     | 4.x.x                   | composer require 1ff/laravel-mongodb-session:^4.0 |
| 8.x                     | 3.x.x                   | composer require 1ff/laravel-mongodb-session:^3.0 |
| 7.x                     | 2.x.x                   | composer require 1ff/laravel-mongodb-session:^2.0 |
| 5.x.x, 6.x              | 1.x.x                   | composer require 1ff/laravel-mongodb-session:^1.0 |

Installation
------------

Install using composer:

    composer require 1ff/laravel-mongodb-session

Change the connection in `config/session.php` to the name of the mongo connection from your `config/database.php` config

    'connection' =>  'mongodb',
    
Update your .env file and change the `SESSION_DRIVER` to mongodb

    SESSION_DRIVER=mongodb

Advantages
----------

* This driver uses the [MongoDB TTL indexes](https://docs.mongodb.com/manual/core/index-ttl/) meaning when a session key expires it will be automatically deleted. So no need for garbage collection implementation.
* This way, the collection's size will remain around the size you expect and won't get falsely filled with unused data.
* The package automatically adds a migration which creates the index. If you change the name of the `session.table` you should rerun the ttl index creation command `php artisan mongodb:session:index`.

Enjoy!
------

## Testing

This package includes a comprehensive test suite to ensure the MongoDB session handler works correctly. The tests cover:

1. Basic session operations (read, write, destroy)
2. Integration with Laravel's session system
3. HTTP session functionality
4. Laravel's testing helpers integration

### Running the Tests

To run the tests, follow these steps:

1. Make sure MongoDB is installed and running on your system
2. Install the package dependencies with Composer:

```bash
composer install
```

3. Run the tests with PHPUnit:

```bash
vendor/bin/phpunit
```

### Continuous Integration

The package includes a GitHub Actions workflow that automatically runs tests on PHP 8.1 with Laravel 10.x against MongoDB 7. The workflow:

1. Sets up a MongoDB service container
2. Installs PHP with MongoDB extension
3. Caches Composer dependencies for faster builds
4. Runs the test suite

This ensures all tests pass before merging new changes.

### Expected Test Results

When all tests are passing, you should see output similar to:

```
PHPUnit 10.x.x by Sebastian Bergmann and contributors.

...............                                                   15 / 15 (100%)

Time: 00:00.444, Memory: 32.00 MB

OK (15 tests, 41 assertions)
```

### Testing Environments

The tests are compatible with:

- PHP 8.1+
- Laravel 10.x
- MongoDB 4.0+

### Test Coverage

- **Unit Tests**: These test the `MongoDbSessionHandler` methods directly (open, close, read, write, destroy, gc)
- **Feature Tests**: These test the integration with Laravel's session functionality
- **HTTP Tests**: These test session handling in HTTP requests and session persistence
- **Laravel Helper Tests**: These test integration with Laravel's testing helpers like `withSession` and `flushSession`

If you encounter any issues with the tests, please submit an issue on the GitHub repository.
