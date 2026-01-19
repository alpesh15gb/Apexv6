<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ImportEmployees extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'employees:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import employees from biometric SQL Server';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting employee import from biometric system...');

        try {
            // Check connection
            try {
                DB::connection('sqlsrv')->getPdo();
            } catch (\Exception $e) {
                $this->error('Could not connect to SQL Server: ' . $e->getMessage());
                return 1;
            }

            $table = 'Employees';

            // Check if table exists
            $tableExists = DB::connection('sqlsrv')->select("SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = ?", [$table]);

            if (empty($tableExists)) {
                $this->error("Table $table not found in SQL Server.");
                return 1;
            }

            $this->info("Fetching employees from $table...");

            $employees = DB::connection('sqlsrv')->table($table)
                // Select only necessary columns ensuring EmployeeCodeInDevice is included if possible
                // But select * or try to select specific columns.
                // Note: EmployeeCodeInDevice might not exist in older versions, checking safety?
                // Assuming it exists based on previous verification.
                ->select('*')
                ->get();

            $this->info("Found " . $employees->count() . " employees.");

            $count = 0;
            $updated = 0;

            foreach ($employees as $emp) {
                // Determine Code and Name
                $code = trim($emp->EmployeeCode);
                $name = trim($emp->EmployeeName);

                if (empty($code) || empty($name))
                    continue;

                $email = strtolower($code) . '@apex.com';

                // Determine Device Binding ID (EmployeeCodeInDevice or Fallback)
                // Use property_exists or just isset to be safe
                $deviceId = isset($emp->EmployeeCodeInDevice) && !empty($emp->EmployeeCodeInDevice)
                    ? $emp->EmployeeCodeInDevice
                    : $emp->EmployeeId;

                $user = User::updateOrCreate(
                    ['email' => $email],
                    [
                        'name' => $name,
                        'password' => Hash::make('password'),
                        'role' => 'employee',
                        'employee_id' => $code, // Visual Code
                        'device_employee_id' => $deviceId, // Binding ID
                        'is_active' => true,
                    ]
                );

                if ($user->wasRecentlyCreated) {
                    $count++;
                    $this->info("Imported: $name ($code) - Bind: $deviceId");
                } else {
                    $updated++;
                }
            }

            $this->info("Import Completed: $count new users created, $updated users updated.");

        } catch (\Exception $e) {
            $this->error('An error occurred: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
