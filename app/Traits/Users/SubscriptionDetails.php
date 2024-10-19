<?php

namespace App\Traits\Users;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;

trait SubscriptionDetails
{
    public function subscriptionDetails(): Attribute
    {
        return Attribute::make(
            get: function ()
            {
                if(!$this->subscribed_at){
                    return false;
                }

                $now = Carbon::now();
                $expiredAt = Carbon::parse($this->expired_at);

                $isExpired = $now->greaterThan($expiredAt);

                if (!$isExpired) {
                    $totalMonths = $now->floatDiffInMonths($expiredAt, false); // This will give a decimal value

                    $wholeMonths = floor($totalMonths);

                    $remainingFractionOfMonth = $totalMonths - $wholeMonths;

                    $totalDaysInFraction = $remainingFractionOfMonth * 30.44;
                    $wholeDays = floor($totalDaysInFraction);

                    $remainingFractionOfDay = $totalDaysInFraction - $wholeDays;

                    $totalHoursInFraction = $remainingFractionOfDay * 24;
                    $wholeHours = floor($totalHoursInFraction);

                    $monthText = $wholeMonths > 1 ? 'months' : 'month';
                    $dayText = $wholeDays > 1 ? 'days' : 'day';
                    $hourText = $wholeHours > 1 ? 'hours' : 'hour';

                    // Prepare the result
                    $remainingTime = $wholeMonths . " {$monthText}, " . $wholeDays . " {$dayText}, " . $wholeHours . " {$hourText}, ";
                } else {
                    $remainingTime = 'Expired';
                }
                return [
                    'is_expired' => $isExpired,
                    'remaining_time' => $remainingTime,
                ];
            }
        );
    }
}
