# Laravel Mongodb Session driver

A MongoDB session driver for Laravel

| **Laravel<br/>Version** | **Package<br/>Version** | **Install using<br/>this command**                |
|------------------------|-------------------------|---------------------------------------------------|
| 5.x.x, 6.x             | 1.x.x                   | composer require 1ff/laravel-mongodb-session:^1.0 |
| 7.x                    | 2.x.x                   | composer require 1ff/laravel-mongodb-session:^2.0 |
| 8.x                    | 3.x.x                   | composer require 1ff/laravel-mongodb-session:^3.0 |
| 9.x                    | 4.x.x                   | Comming soon                                      |

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
