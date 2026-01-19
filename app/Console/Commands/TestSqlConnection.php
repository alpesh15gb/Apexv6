<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TestSqlConnection extends Command
{
    protected $signature = 'db:test-sql';
    protected $description = 'Test SQL Server Connection and list tables';

    public function handle()
    {
        try {
            $this->info('Attempting to connect to SQL Server...');
            // Find tables matching Employee
            $tables = DB::connection('sqlsrv')->select("SELECT name FROM sys.tables WHERE name LIKE '%Employee%' OR name LIKE '%User%' ORDER BY name");

            $this->info('Found possible Employee tables:');
            foreach ($tables as $table) {
                $this->line('- ' . $table->name);
            }

            $t = 'DeviceLogs_1_2026';
            $ids = DB::connection('sqlsrv')->table($t)->distinct()->pluck('UserId')->take(5);
            $this->info("UserIds in $t: " . implode(', ', $ids->toArray()));

            $this->info("First 5 Employees (Id - Code):");
            $emps = DB::connection('sqlsrv')->select("SELECT TOP 5 EmployeeId, EmployeeCode FROM Employees");
            foreach ($emps as $e) {
                $this->line($e->EmployeeId . " - " . $e->EmployeeCode);
            }
            try {
                $count = DB::connection('sqlsrv')->table($t)->count();
                $this->info("$t Total Rows: $count");
                if ($count > 0) {
                    $latest = DB::connection('sqlsrv')->table($t)->max('LogDate');
                    $this->info("Latest Log in Jan: $latest");
                } else {
                    $this->warn("No logs in Jan 2026 table.");
                }
            } catch (\Exception $e) {
                $this->error("Error checking $t: " . $e->getMessage());
            }
        } catch (\Exception $e) {
            $this->error('Connection failed: ' . $e->getMessage());
        }
    }
}
