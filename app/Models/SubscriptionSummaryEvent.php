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



     /**
     * Create a new subscription summary event record.
     *
     * @param int $userId - The ID of the user related to the event.
     * @param int $subscriptionId - The ID of the subscription related to the event.
     * @param string $eventType - The type of event (e.g., 'payment_success', 'subscription_expired').
     * @param string|null $description - Optional description for the event.
     * @return SubscriptionSummaryEvent - Returns the created SubscriptionSummaryEvent record.
     */
    public static function logEvent($userId, $subscriptionId, $eventType, $description = null)
    {
        // Insert the record and return it
        return self::create([
            'user_id' => $userId,
            'subscription_id' => $subscriptionId,
            'event_type' => $eventType,
            'description' => $description,
        ]);
    }
}
