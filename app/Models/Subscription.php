<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Carbon\Carbon;

class Subscription extends Model
{
    use HasFactory, HasUuids;

    // Define that the primary key is a UUID
    protected $primaryKey = 'id';
    public $incrementing = false; // Disable auto-incrementing
    protected $keyType = 'string'; // UUIDs are stored as strings

    const GRACE_PERIOD_DAYS = 7;

    protected $fillable = [
        'user_id',
        'subscription_type',
        'is_auto_renewal',
        'start_date',
        'end_date',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Check if the subscription is active
    public function isActive()
    {
        return $this->status === 'active' && Carbon::now()->lt($this->end_date);
    }

    // Check if the subscription is in the grace period
    public function isInGracePeriod()
    {
        if ($this->status === 'expired') {
            $gracePeriodEnd = Carbon::parse($this->end_date)->addDays(self::GRACE_PERIOD_DAYS);
            return Carbon::now()->between($this->end_date, $gracePeriodEnd);
        }

        return false;
    }

    // Set trial subscription
    public function startTrial()
    {
        $this->subscription_type = 'trial';
        $this->start_date = now();
        $this->end_date = Carbon::now()->addMonth(); // One-month trial
        $this->status = 'active';
        $this->save();
    }

    // Set paid subscription (monthly or annually)
    public function startPaid($type, $autoRenewal = false)
    {
        if ($type !== 'monthly' && $type !== 'annually') {
            throw new \Exception('Invalid subscription type');
        }

        $this->subscription_type = $type;
        $this->start_date = now();
        $this->end_date = $type === 'monthly' ? Carbon::now()->addMonth() : Carbon::now()->addYear();
        $this->is_auto_renewal = $autoRenewal;
        $this->status = 'active';
        $this->save();
    }

    // Handle auto-renewal if enabled
    public function renewSubscription()
    {
        if ($this->is_auto_renewal && $this->isActive()) {
            $this->start_date = now();
            $this->end_date = $this->subscription_type === 'monthly' ? Carbon::now()->addMonth() : Carbon::now()->addYear();
            $this->status = 'active';
            $this->save();
        }
    }

    // Mark the subscription as expired
    public function expire()
    {
        $this->status = 'expired';
        $this->save();
    }

    // End the grace period and mark the subscription fully expired
    public function endGracePeriod()
    {
        $gracePeriodEnd = Carbon::parse($this->end_date)->addDays(self::GRACE_PERIOD_DAYS);
        if (Carbon::now()->gt($gracePeriodEnd)) {
            $this->status = 'fully_expired';
            $this->save();
        }
    }
}
