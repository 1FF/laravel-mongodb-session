<?php

namespace ForFit\Session\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use MongoDB\Driver\ReadPreference;

/**
 * Drop the indexes created by MongodbSessionIndex
 */
class MongodbSessionDropIndex extends Command
{

    /** The name and signature of the console command. */
    protected $signature = 'mongodb:session:dropindex {index}';

    /** The console command description. */
    protected $description = 'Drops the passed index from the mongodb `sessions` collection';

    /** Execute the console command. */
    public function handle(): void
    {
        $collection = config('session.table');

        DB::connection('mongodb')->getMongoDB()->command([
            'dropIndexes' => $collection,
            'index' => $this->argument('index'),
        ], [
            'readPreference' => new ReadPreference(ReadPreference::PRIMARY)
        ]);
    }
}
