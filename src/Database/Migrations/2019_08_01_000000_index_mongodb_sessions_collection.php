<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

class IndexMongodbSessionsCollection extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Artisan::call('mongodb:session:index');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Artisan::call('mongodb:session:dropindex', ['index' => 'expires_at_ttl']);
    }
}
