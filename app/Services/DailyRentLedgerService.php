<?php

namespace App\Services;

use App\Models\DailyAttendance;
use App\Models\DailyRentLedger;
use App\Models\PlotAllocation;
use App\Models\RateHistory;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DailyRentLedgerService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

public function syncAllocation(
        int|PlotAllocation $allocation,
        ?string $throughDate = null
    ): array {
        $allocationId = $allocation instanceof PlotAllocation
            ? $allocation->id
            : $allocation;

        $targetDate = $throughDate
            ? Carbon::parse($throughDate)->startOfDay()
            : now()->startOfDay();

        $result = [
            'allocation_id' => $allocationId,
            'created_attendances' => 0,
            'created_ledgers' => 0,
            'synced_through' => null,
            'blocked_date' => null,
            'status' => 'completed',
        ];

        while (true) {
            $dayResult = DB::transaction(function () use (
                $allocationId,
                $targetDate
            ) {
                $allocation = PlotAllocation::query()
                    ->lockForUpdate()
                    ->findOrFail($allocationId);

                $startDate = Carbon::parse(
                    $allocation->start_date
                )->startOfDay();

                // Allocation-এর দিন ভাড়া প্রযোজ্য নয়।
                $minimumDate = $startDate->copy()->addDay();

                // Cursor না থাকলে প্রথম ভাড়ার দিন থেকে শুরু।
                $nextDate = $allocation->last_synced_date
                    ? Carbon::parse(
                        $allocation->last_synced_date
                    )->addDay()->startOfDay()
                    : $minimumDate->copy();

                if ($nextDate->lt($minimumDate)) {
                    $nextDate = $minimumDate->copy();
                }

                // Allocation-এর শেষ তারিখ অতিক্রম করা যাবে না।
                if ($allocation->end_date) {
                    $endDate = Carbon::parse(
                        $allocation->end_date
                    )->startOfDay();

                    if ($endDate->lt($targetDate)) {
                        $effectiveTarget = $endDate;
                    } else {
                        $effectiveTarget = $targetDate->copy();
                    }
                } else {
                    $effectiveTarget = $targetDate->copy();
                }

                if ($nextDate->gt($effectiveTarget)) {
                    return [
                        'status' => 'completed',
                    ];
                }

                $dateString = $nextDate->toDateString();

                // ১. Attendance খুঁজবে অথবা তৈরি করবে।
                $attendance = DailyAttendance::query()
                    ->where('allocation_id', $allocationId)
                    ->where('attendance_date', $dateString)
                    ->first();

                $createdAttendance = false;

                if (!$attendance) {
                    $attendance = DailyAttendance::create([
                        'allocation_id' => $allocationId,
                        'attendance_date' => $dateString,
                        'attendance_status' => 'initiated',
                    ]);

                    $createdAttendance = true;
                }

                // ২. আগে থেকে Ledger থাকলে সেটি পরিবর্তন করবে না।
                $ledger = DailyRentLedger::query()
                    ->where('allocation_id', $allocationId)
                    ->where('rent_date', $dateString)
                    ->first();

                $createdLedger = false;

                if ($ledger) {
                    // পুরোনো Ledger-এর Attendance link অনুপস্থিত হলে
                    // শুধু সেই সম্পর্কটি ঠিক করবে।
                    if (!$ledger->attendance_id) {
                        $ledger->attendance_id = $attendance->id;
                        $ledger->save();
                    } elseif (
                        (int) $ledger->attendance_id !==
                        (int) $attendance->id
                    ) {
                        throw new RuntimeException(
                            "Attendance-Ledger mismatch: {$dateString}"
                        );
                    }
                } else {
                    // ৩. ওই দিনের কার্যকর Rate খুঁজবে।
                    $rateHistory = RateHistory::query()
                        ->where('rate_type', 'daily_rent')
                        ->whereDate(
                            'effective_from',
                            '<=',
                            $dateString
                        )
                        ->where(function ($query) use ($dateString) {
                            $query->whereNull('effective_until')
                                ->orWhereDate(
                                    'effective_until',
                                    '>=',
                                    $dateString
                                );
                        })
                        ->orderByDesc('effective_from')
                        ->first();

                    // Rate না থাকলে Attendance থাকবে,
                    // কিন্তু Ledger ও Cursor তৈরি/পরিবর্তন হবে না।
                    if (!$rateHistory) {
                        return [
                            'status' => 'rate_missing',
                            'date' => $dateString,
                            'created_attendance' => $createdAttendance,
                        ];
                    }

                    // ৪. কার্যকর Rate দিয়ে Ledger তৈরি।
                    DailyRentLedger::create([
                        'allocation_id' => $allocationId,
                        'attendance_id' => $attendance->id,
                        'rate_history_id' => $rateHistory->id,
                        'rent_date' => $dateString,
                        'base_amount' => $rateHistory->amount,
                        'adjustment_amount' => 0,
                        'net_amount' => $rateHistory->amount,
                        'status' => 'pending',
                    ]);

                    $createdLedger = true;
                }

                // ৫. সফলভাবে দিনের কাজ শেষ হলে Cursor এগোবে।
                $allocation->last_synced_date = $dateString;
                $allocation->save();

                return [
                    'status' => 'success',
                    'date' => $dateString,
                    'created_attendance' => $createdAttendance,
                    'created_ledger' => $createdLedger,
                ];
            }, 3);

            if ($dayResult['status'] === 'completed') {
                break;
            }

            if ($dayResult['status'] === 'rate_missing') {
                if ($dayResult['created_attendance']) {
                    $result['created_attendances']++;
                }

                $result['status'] = 'rate_missing';
                $result['blocked_date'] = $dayResult['date'];

                break;
            }

            if ($dayResult['created_attendance']) {
                $result['created_attendances']++;
            }

            if ($dayResult['created_ledger']) {
                $result['created_ledgers']++;
            }

            $result['synced_through'] = $dayResult['date'];
        }

        return $result;
    }
}
