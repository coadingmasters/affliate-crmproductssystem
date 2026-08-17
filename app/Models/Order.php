<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'full_name',
    'email',
    'phone',
    'address',
    'product_id',
    'product_price_id',
    'quantity',
    'total_price',
    'commission_total',
    'status',
    'notes',
])]
class Order extends Model
{
    /**
     * The COD follow-up pipeline, in the order it normally progresses.
     *
     * @var array<string, array{label: string, tone: string, customer: string}>
     */
    public const STATUS_META = [
        'new'       => ['label' => 'New',       'tone' => 'warning', 'customer' => 'Received'],
        'contacted' => ['label' => 'Contacted', 'tone' => 'info',    'customer' => 'We called you'],
        'confirmed' => ['label' => 'Confirmed', 'tone' => 'brand',   'customer' => 'Confirmed'],
        'shipped'   => ['label' => 'Shipped',   'tone' => 'brand',   'customer' => 'On its way'],
        'delivered' => ['label' => 'Delivered', 'tone' => 'success', 'customer' => 'Delivered'],
        'paid'      => ['label' => 'Paid',      'tone' => 'success', 'customer' => 'Completed'],
        'cancelled' => ['label' => 'Cancelled', 'tone' => 'danger',  'customer' => 'Cancelled'],
        'returned'  => ['label' => 'Returned',  'tone' => 'danger',  'customer' => 'Returned'],
    ];

    /**
     * Statuses that count as a completed sale.
     */
    public const EARNING_STATUSES = ['delivered', 'paid'];

    /**
     * Statuses still moving through the pipeline.
     */
    public const OPEN_STATUSES = ['new', 'contacted', 'confirmed', 'shipped'];

    /**
     * Statuses that ended without a sale.
     */
    public const LOST_STATUSES = ['cancelled', 'returned'];

    /**
     * Every valid status key.
     *
     * @return array<int, string>
     */
    public static function statuses(): array
    {
        return array_keys(self::STATUS_META);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'total_price' => 'decimal:2',
            'commission_total' => 'decimal:2',
        ];
    }

    /**
     * The account that placed the order, if any.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The product that was ordered.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * The price option that was ordered.
     */
    public function productPrice(): BelongsTo
    {
        return $this->belongsTo(ProductPrice::class);
    }

    /**
     * Admin-facing label, e.g. "Contacted".
     */
    public function statusLabel(): string
    {
        return self::STATUS_META[$this->status]['label'] ?? ucfirst($this->status);
    }

    /**
     * Customer-facing wording, which is friendlier than the internal label.
     */
    public function customerStatusLabel(): string
    {
        return self::STATUS_META[$this->status]['customer'] ?? ucfirst($this->status);
    }

    /**
     * The Tailwind classes used to render this order's status badge.
     */
    public function statusClasses(): string
    {
        $tone = self::STATUS_META[$this->status]['tone'] ?? 'muted';

        return "bg-{$tone}/10 text-{$tone}";
    }

    /**
     * How far through the pipeline this order is, as a percentage.
     */
    public function progress(): int
    {
        if (in_array($this->status, self::LOST_STATUSES, true)) {
            return 100;
        }

        $steps = ['new', 'contacted', 'confirmed', 'shipped', 'delivered', 'paid'];
        $position = array_search($this->status, $steps, true);

        return $position === false ? 0 : (int) round(($position + 1) / count($steps) * 100);
    }
}
