# Laravel Mongodb Session driver

A MongoDB session driver for Laravel

Installation
------------

Make sure you have [jenssegers\mongodb](https://github.com/jenssegers/Laravel-MongoDB) installed before you continue.

Install using composer:

    composer require 1ff/laravel-mongodb-session

Add the service provider in `config/app.php`:

    'ForFit\Session\SessionServiceProvider::class',
    
Change the connection in `config/session.php` to the name of the mongo connection from your `config/database.php` config

    'connection' =>  'mongodb',
    
Update your .env file and change the `SESSION_DRIVER` to mongodb

    SESSION_DRIVER=mongodb

Advantages
----------

* This driver uses the [MongoDB TTL indexes](https://docs.mongodb.com/manual/core/index-ttl/) meaning when a session key expires it will be automatically deleted. So no need for garbage collection implementation.
* This way, the collection's size will remain around the size you expect and won't get falsely filled with unused data.
* The package automatically adds a migration which creates the index.

Warning
-------

This session driver is not compatible with other session drivers, because it uses its all table structure.
If you are using another mongodb session driver at the moment make sure you set a new collection for this one.

Enjoy!
------
