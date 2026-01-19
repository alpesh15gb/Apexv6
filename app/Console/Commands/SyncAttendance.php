<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncAttendance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync attendance logs from biometric SQL Server';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting attendance sync...');

        try {
            // Check connection first
            try {
                DB::connection('sqlsrv')->getPdo();
            } catch (\Exception $e) {
                $this->error('Could not connect to SQL Server: ' . $e->getMessage());
                return 1;
            }

            // Look back logic
            $lookbackDays = 30;
            $startDate = Carbon::today()->subDays($lookbackDays);
            $endDate = Carbon::today();

            $this->info("Scanning range: " . $startDate->toDateString() . " to " . $endDate->toDateString());

            // Pre-load Device User Mapping
            $this->info("Loading Device User Mapping...");
            $deviceUserMap = DB::connection('sqlsrv')->table('DeviceUsers')
                ->pluck('EmployeeId', 'DeviceUserId')
                ->toArray();
            $this->info("Loaded " . count($deviceUserMap) . " device mappings.");

            // Generate list of tables
            $tablesToCheck = [];
            $period = \Carbon\CarbonPeriod::create($startDate, '1 month', $endDate->copy()->addMonth());

            foreach ($period as $dt) {
                $tableName = 'DeviceLogs_' . $dt->month . '_' . $dt->year;
                $tablesToCheck[] = $tableName;
            }
            $tablesToCheck = array_unique($tablesToCheck);

            foreach ($tablesToCheck as $table) {
                // Check if table exists
                $exists = DB::connection('sqlsrv')->select("SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = ?", [$table]);

                if (empty($exists)) {
                    continue;
                }

                $this->info("Fetching logs from table: $table...");

                $logs = DB::connection('sqlsrv')->table($table)
                    ->whereRaw("CAST(LogDate AS DATETIME) >= ?", [$startDate->format('Y-m-d')])
                    ->orderByRaw('CAST(LogDate AS DATETIME) ASC')
                    ->get();

                if ($logs->isEmpty()) {
                    continue;
                }

                $this->info("Found " . $logs->count() . " records in $table.");

                foreach ($logs as $log) {
                    $this->processLog($log, $deviceUserMap);
                }
            }

            $this->info('Sync completed successfully.');

        } catch (\Exception $e) {
            $this->error('An error occurred during sync: ' . $e->getMessage());
            Log::error('Attendance Sync Error: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }

    private function processLog($log, $deviceUserMap = [])
    {
        $deviceUserId = $log->UserId;

        // Priority 1: Exact Match (Code or ID)
        $user = User::where('employee_id', (string) $deviceUserId)
            ->orWhere('device_employee_id', $deviceUserId)
            ->first();

        // Priority 2: Suffix Match (e.g. Log 106 matches Code KSDK106)
        // Only if not found and ID is significant
        if (!$user && strlen((string) $deviceUserId) > 0) {
            // Fetch candidates ending with ID
            $candidates = User::where('employee_id', 'LIKE', '%' . $deviceUserId)->get();

            foreach ($candidates as $candidate) {
                $code = $candidate->employee_id;
                // Check if it ends with ID
                if (str_ends_with($code, (string) $deviceUserId)) {
                    // Check char before ID (must be non-digit or start of string)
                    $prefixLen = strlen($code) - strlen((string) $deviceUserId);
                    if ($prefixLen === 0) {
                        $user = $candidate;
                        break; // Exact match handled by Priority 1, but safe here
                    }

                    $charBefore = substr($code, $prefixLen - 1, 1);
                    if (!is_numeric($charBefore)) {
                        $user = $candidate; // "KSDK1" matches "1" (K is not numeric)
                        break;
                    }
                    // Else: "11" matches "1" -> 1 is numeric. Skip.
                }
            }
        }

        // Priority 3: Mapped Match (DeviceUsers)
        if (!$user && isset($deviceUserMap[$deviceUserId])) {
            $mappedId = $deviceUserMap[$deviceUserId];
            $user = User::where('device_employee_id', $mappedId)
                ->orWhere('employee_id', (string) $mappedId)
                ->first();
        }

        if (!$user) {
            // $this->warn("User not found for Device User ID: $deviceUserId");
            return;
        }

        $logDate = Carbon::parse($log->LogDate);
        $dateOnly = $logDate->format('Y-m-d');
        $timeOnly = $logDate->format('H:i:s');

        // Debug for User 10
        if ($log->UserId == 10) {
            $this->info("Processing Log for User 10 at $timeOnly. Date: $dateOnly");
        }

        // Format date with time for SQLite consistency if column is datetime
        $dateDb = $logDate->format('Y-m-d 00:00:00');

        $attendance = Attendance::withTrashed()->updateOrCreate(
            ['user_id' => $user->id, 'date' => $dateDb],
            [] // Do not overwrite existing data on match
        );

        // If it was trashed, restore it
        if ($attendance->trashed()) {
            $attendance->restore();
        }

        // Logic to process punches
        if (!$attendance->punch_in_time) {
            $attendance->update([
                'punch_in_time' => $timeOnly,
                'status' => 'present',
            ]);
            if ($log->UserId == 10)
                $this->info("  -> Set Punch In: $timeOnly");
        } else {
            if ($log->UserId == 10)
                $this->info("  -> Has Punch In: {$attendance->punch_in_time}. Comparison: $timeOnly > {$attendance->punch_in_time}");

            // Self-healing: If status is absent but we have a punch, fix it
            if ($attendance->status === 'absent') {
                $attendance->update(['status' => 'present']);
            }

            $punchIn = Carbon::parse($attendance->punch_in_time);
            $logDt = Carbon::parse($log->LogDate); // Use full LogDate

            // Check if this punch is EARLIER than current In (Fix bad data or unordered logs)
            if ($logDt->lt($punchIn)) {
                if ($log->UserId == 10)
                    $this->info("  -> Found EARLIER Punch In ({$logDt->toTimeString()} < {$punchIn->toTimeString()}). Swapping.");

                $oldIn = $attendance->punch_in_time;
                // Update In to the new Log Date/Time
                $attendance->update(['punch_in_time' => $log->LogDate]); // Use full datetime

                // The old In time was actually a later punch (likely an Out punch processed early)
                // So move it to Out, if it helps
                // Compare timestamps
                if (!$attendance->punch_out_time || Carbon::parse($oldIn)->gt(Carbon::parse($attendance->punch_out_time))) {
                    $attendance->update(['punch_out_time' => $oldIn]);
                }

                // Re-calc hours
                if ($attendance->punch_out_time) {
                    $start = Carbon::parse($log->LogDate);
                    $end = Carbon::parse($attendance->punch_out_time); // This might be $oldIn string or DB value
                    // Ensure we use the latest DB value if we just updated it? 
                    // No, $attendance is not refreshed. $oldIn is safe if we updated it.
                    // Let's re-fetch or use logic

                    if ($end > $start) {
                        $hours = round($start->diffInMinutes($end) / 60, 2);
                        $attendance->update(['total_hours' => $hours]);
                    }
                }
            }
            // Update punch out if later
            elseif ($logDt->gt($punchIn)) {
                // Debounce: Check if less than 2 minutes difference
                if ($punchIn->diffInMinutes($logDt) < 2) {
                    if ($log->UserId == 10)
                        $this->info("  -> Skipped Out Update (Debounce < 2 mins)");
                    // Do nothing, treat as duplicate punch
                } else {
                    if ($log->UserId == 10)
                        $this->info("  -> Updating Punch Out to {$logDt->toTimeString()}");

                    $attendance->update(['punch_out_time' => $log->LogDate]); // Full Datetime

                    // Recalculate hours
                    $start = $punchIn;
                    $end = $logDt;
                    $hours = round($start->diffInMinutes($end) / 60, 2);

                    $attendance->update(['total_hours' => $hours]);
                }
            } else {
                if ($log->UserId == 10)
                    $this->info("  -> Skipped Out Update (Not greater)");
            }
        }
    }
}
