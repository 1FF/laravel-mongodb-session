<?php

namespace ForFit\Session\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use \MongoDB\Driver\ReadPreference;

/**
 * Create indexes for the Session collection
 */
class MongodbSessionIndex extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mongodb:session:index';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create indexes on the mongodb `sessions` collection';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $collection = config('session.table');

        DB::connection('mongodb')->getMongoDB()->command([
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
            'readPreference' => new ReadPreference(ReadPreference::RP_PRIMARY)
        ]);
    }
}
