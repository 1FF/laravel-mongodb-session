<?php

namespace ForFit\Session\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use MongoDB\Driver\ReadPreference;

/**
 * Create indexes for the Session collection
 */
class MongodbSessionIndex extends Command
{

    /** The name and signature of the console command. */
    protected $signature = 'mongodb:session:index';

    /** The console command description. */
    protected $description = 'Create indexes on the mongodb `sessions` collection';

    /** Execute the console command. */
    public function handle(): void
    {
        $collection = config('session.table');

        DB::connection('mongodb')->getDatabase()->command([
            'createIndexes' => $collection,
            'indexes' => [
                [
                    'key' => ['expires_at' => 1],
                    'name' => 'expires_at_ttl',
                    'expireAfterSeconds' => 0,
                    'background' => true
                ]
            ]
        ], [
            'readPreference' => new ReadPreference(ReadPreference::PRIMARY)
        ]);
    }
}
