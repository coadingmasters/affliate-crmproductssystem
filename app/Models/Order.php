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
    'post_date',
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
        'new'                     => ['label' => 'New',                     'tone' => 'warning', 'customer' => 'Received'],
        'callback'                => ['label' => 'Callback',                'tone' => 'brand',   'customer' => 'Callback scheduled'],
        'confirmation_department' => ['label' => 'Confirmation Department', 'tone' => 'info',    'customer' => 'In review'],
        'post_date'               => ['label' => 'Post Date',               'tone' => 'info',    'customer' => 'Scheduled'],
        'awaiting_payment'        => ['label' => 'Awaiting Payment',        'tone' => 'warning', 'customer' => 'Awaiting payment'],
        'sale'                    => ['label' => 'Sale',                    'tone' => 'success', 'customer' => 'Confirmed'],
        'active_account'          => ['label' => 'Active Account',          'tone' => 'success', 'customer' => 'Active'],
        'going_to_return'         => ['label' => 'Going to Return',         'tone' => 'danger',  'customer' => 'Return in progress'],
        'card_declined'           => ['label' => 'Card Declined',           'tone' => 'danger',  'customer' => 'Payment declined'],
        'confirmation_failure'    => ['label' => 'Confirmation Failure',    'tone' => 'danger',  'customer' => 'Could not confirm'],
        'duplicate'               => ['label' => 'Duplicate',               'tone' => 'muted',   'customer' => 'Duplicate order'],
        'cancelled'               => ['label' => 'Cancelled',               'tone' => 'danger',  'customer' => 'Cancelled'],
    ];

    /**
     * Statuses that count as a converted sale, and so earn commission.
     */
    public const EARNING_STATUSES = ['sale', 'active_account'];

    /**
     * Statuses still working towards an outcome.
     */
    public const OPEN_STATUSES = [
        'new', 'callback', 'confirmation_department', 'post_date', 'awaiting_payment',
    ];

    /**
     * Statuses that ended without a sale.
     */
    public const LOST_STATUSES = [
        'going_to_return', 'card_declined', 'confirmation_failure', 'duplicate', 'cancelled',
    ];

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
            'status_changed_at' => 'datetime',
            'post_date' => 'date',
        ];
    }

    /**
     * Stamp the moment the status moves, leaving created_at untouched.
     */
    protected static function booted(): void
    {
        static::updating(function (self $order) {
            if ($order->isDirty('status')) {
                $order->status_changed_at = now();
            }
        });
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
     * When the order was submitted, in the configured display timezone.
     */
    public function submittedAt(): \Carbon\CarbonInterface
    {
        return $this->created_at->timezone(config('app.display_timezone'));
    }

    /**
     * Full submission date and time, e.g. "Aug 17, 2026 at 3:42 PM".
     */
    public function submittedAtLabel(): string
    {
        return $this->submittedAt()->format('M j, Y \a\t g:i A');
    }

    /**
     * When the status was last changed, in the display timezone.
     *
     * An order submitted on the 14th and cleared on the 20th keeps both
     * dates: the submission date never moves, this records the change.
     */
    public function statusChangedAt(): ?\Carbon\CarbonInterface
    {
        return $this->status_changed_at?->timezone(config('app.display_timezone'));
    }

    /**
     * Full status change date and time, or null if it never moved.
     */
    public function statusChangedAtLabel(): ?string
    {
        return $this->statusChangedAt()?->format('M j, Y \a\t g:i A');
    }

    /**
     * How long the order took to reach its current status.
     */
    public function timeToStatus(): ?string
    {
        if (! $this->status_changed_at) {
            return null;
        }

        return $this->created_at->diffForHumans($this->status_changed_at, [
            'syntax' => \Carbon\CarbonInterface::DIFF_ABSOLUTE,
        ]);
    }

    /**
     * The agreed payment date, e.g. "Aug 25, 2026", or null.
     */
    public function postDateLabel(): ?string
    {
        return $this->post_date?->format('M j, Y');
    }

    /**
     * How far through the pipeline this order is, as a percentage.
     */
    public function progress(): int
    {
        if (in_array($this->status, self::LOST_STATUSES, true)) {
            return 100;
        }

        $steps = ['new', 'callback', 'confirmation_department', 'awaiting_payment', 'sale', 'active_account'];
        $position = array_search($this->status, $steps, true);

        return $position === false ? 0 : (int) round(($position + 1) / count($steps) * 100);
    }
}
