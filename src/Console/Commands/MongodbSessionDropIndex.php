<?php

namespace ForFit\Session\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use \MongoDB\Driver\ReadPreference;

/**
 * Drop the indexes created by MongodbSessionIndex
 */
class MongodbSessionDropIndex extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mongodb:session:dropindex {index}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Drops the passed index from the mongodb `sessions` collection';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $collection = config('session.table');

        DB::connection('mongodb')->getMongoDB()->command([
            'dropIndexes' => $collection,
            'index' => $this->argument('index'),
        ], [
            'readPreference' => new ReadPreference(ReadPreference::RP_PRIMARY)
        ]);
    }
}
