<?php

namespace App\Console\Commands;

use App\Models\Department;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncDepartments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:departments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync departments from SQL Server';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting department sync...');

        try {
            // Check connection
            try {
                DB::connection('sqlsrv')->getPdo();
            } catch (\Exception $e) {
                $this->error('Could not connect to SQL Server: ' . $e->getMessage());
                return 1;
            }

            // Attempt to fetch departments
            // Common table names: Departments, Dept, DepartmentMaster
            $tableName = 'Departments';

            // Verify table exists
            $exists = DB::connection('sqlsrv')->select("SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = ?", [$tableName]);

            if (empty($exists)) {
                $this->error("Table '$tableName' not found in SQL Server.");
                // Try 'Department'
                $exists = DB::connection('sqlsrv')->select("SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = ?", ['Department']);
                if (!empty($exists)) {
                    $tableName = 'Department';
                } else {
                    return 1;
                }
            }

            $this->info("Fetching from $tableName...");

            $remoteDepts = DB::connection('sqlsrv')->table($tableName)->get();

            $this->info("Found " . $remoteDepts->count() . " departments.");

            foreach ($remoteDepts as $rd) {
                // Adjust field names based on common schemas
                $name = $rd->DepartmentName ?? $rd->DeptName ?? $rd->Name ?? null;

                if (!$name) {
                    $this->warn("Skipping row with no name: " . json_encode($rd));
                    continue;
                }

                Department::updateOrCreate(
                    ['name' => $name],
                    [
                        'is_active' => true,
                        // 'description' => 'Imported from SQL Server ID: ' . ($rd->DepartmentId ?? $rd->DeptId ?? '')
                    ]
                );

                $this->info("Synced: $name");
            }

            $this->info('Department sync completed successfully.');

        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            Log::error('Department Sync Error: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
