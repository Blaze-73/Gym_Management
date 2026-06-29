<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_number',
        'total_amount',
        'status',
        'payment_status',
        'customer_name',
        'customer_email',
        'customer_phone',
        'shipping_address',
        'notes',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
    ];

    /**
     * Get the user who placed this order
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all items in this order
     */
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    /**
     * Generate unique order number
     */
    public static function generateOrderNumber()
    {
        $prefix = 'ORD';
        $date = now()->format('Ymd');
        $random = str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        
        return "{$prefix}{$date}{$random}";
    }

    /**
     * Automatically generate order number
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $order->order_number = self::generateOrderNumber();
            }
        });
    }
}