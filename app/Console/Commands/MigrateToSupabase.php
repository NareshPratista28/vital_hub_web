<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Config;

class MigrateToSupabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:to-supabase';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate data from local MySQL to Supabase PostgreSQL';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting data migration to Supabase...');

        // 1. Configure temporary MySQL connection (the "old" database)
        // These are the credentials before the user switched to Supabase
        Config::set('database.connections.mysql_old', [
            'driver' => 'mysql',
            'host' => '127.0.0.1',
            'port' => '3306',
            'database' => 'vitalhub_app',
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ]);

        $tables = [
            'users',
            'doctors',
            'patients',
            'devices',
            'visits',
            'measurements',
            'device_readings',
            'cache',
            'cache_locks',
            'jobs',
            'job_batches',
            'failed_jobs',
        ];

        // Disable Foreign Key checks for PostgreSQL
        DB::statement('SET session_replication_role = "replica";');

        foreach ($tables as $table) {
            if (!Schema::hasTable($table)) {
                $this->warn("Table '{$table}' does not exist on Supabase, skipping.");
                continue;
            }

            $this->info("Migrating table: {$table}");

            // Clear existing data in Supabase table
            DB::table($table)->truncate();

            // Fetch data from local MySQL
            $oldData = DB::connection('mysql_old')->table($table)->get();

            if ($oldData->isEmpty()) {
                $this->info("Table '{$table}' is empty.");
                continue;
            }

            // Insert data into Supabase
            // We chunk the insertion to avoid memory/packet limits
            $chunks = $oldData->chunk(100);
            foreach ($chunks as $chunk) {
                // Convert StdClass objects to arrays
                $dataArray = json_decode(json_encode($chunk), true);
                DB::table($table)->insert($dataArray);
            }

            $this->info("Successfully migrated " . $oldData->count() . " rows into '{$table}'.");

            // Reset PostgreSQL sequence for the table (if it has an 'id' column)
            if (Schema::hasColumn($table, 'id')) {
                $this->resetSequence($table);
            }
        }

        // Re-enable Foreign Key checks
        DB::statement('SET session_replication_role = "origin";');

        $this->info('Data migration completed successfully!');
    }

    /**
     * Resets the PostgreSQL sequence for a given table.
     */
    private function resetSequence($table)
    {
        $maxId = DB::table($table)->max('id');
        if ($maxId) {
            $nextId = $maxId + 1;
            // PostgreSQL table sequence naming convention: {table}_id_seq
            $sequenceName = "{$table}_id_seq";
            
            try {
                DB::statement("SELECT setval('{$sequenceName}', {$nextId}, false)");
            } catch (\Exception $e) {
                $this->warn("Could not reset sequence for table '{$table}': " . $e->getMessage());
            }
        }
    }
}
