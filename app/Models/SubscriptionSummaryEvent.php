<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class SubscriptionSummaryEvent extends Model
{
    use HasFactory, HasUuids;

    // Define the table if not automatically inferred
    protected $table = 'subscription_summary_events';

    /**
     * Connect the relevant database
     *
     */
    public static function connect($connection =null)
    {
        $connection = $connection ?:config('database.default');
        return (new static)->setConnection($connection);
    }

    // Define fillable fields for mass assignment
    protected $fillable = [
        'user_id',
        'subscription_id',
        'event_type',
        'description',
        'amount',
        'event_date',
    ];

    // Relationship to User model
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relationship to Subscription model
    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }
}
