<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

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
    'user_commission_total',
    'admin_commission_total',
    'status',
    'post_date',
    'sale_date',
    'return_date',
    'notes',
    'form_data',
    'voice_note_path',
    'voice_note_name',
    'voice_note_uploaded_at',
])]
class Order extends Model
{
    use SoftDeletes;

    /**
     * The COD follow-up pipeline, in the order it normally progresses.
     *
     * @var array<string, array{label: string, tone: string, customer: string}>
     */
    public const STATUS_META = [
        'new' => ['label' => 'New',                     'tone' => 'warning', 'customer' => 'New'],
        'callback' => ['label' => 'Callback',                'tone' => 'brand',   'customer' => 'Callback scheduled'],
        'confirmation_department' => ['label' => 'Confirmation Department', 'tone' => 'info',    'customer' => 'In review'],
        'post_date' => ['label' => 'Post Date',               'tone' => 'info',    'customer' => 'Scheduled'],
        'awaiting_payment' => ['label' => 'Awaiting Payment',        'tone' => 'warning', 'customer' => 'Awaiting payment'],
        'sale' => ['label' => 'Sale',                    'tone' => 'success', 'customer' => 'Confirmed'],
        'active_account' => ['label' => 'Active Account',          'tone' => 'success', 'customer' => 'Active'],
        'going_to_return' => ['label' => 'Going to Return',         'tone' => 'danger',  'customer' => 'Return in progress'],
        'card_declined' => ['label' => 'Card Declined',           'tone' => 'danger',  'customer' => 'Payment declined'],
        'confirmation_failure' => ['label' => 'Confirmation Failure',    'tone' => 'danger',  'customer' => 'Could not confirm'],
        'duplicate' => ['label' => 'Duplicate',               'tone' => 'muted',   'customer' => 'Duplicate order'],
        'cancelled' => ['label' => 'Cancelled',               'tone' => 'danger',  'customer' => 'Cancelled'],
    ];

    /**
     * Statuses that ask the admin for a date, and where that date is kept.
     *
     * @var array<string, array{column: string, label: string, help: string}>
     */
    public const STATUS_DATES = [
        'post_date' => [
            'column' => 'post_date',
            'label' => 'Payment Date',
            'help' => 'when the customer will pay',
        ],
        'sale' => [
            'column' => 'sale_date',
            'label' => 'Sale Date',
            'help' => 'when the sale was made',
        ],
        'going_to_return' => [
            'column' => 'return_date',
            'label' => 'Return Date',
            'help' => 'when it is going back',
        ],
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
            'user_commission_total' => 'decimal:2',
            'admin_commission_total' => 'decimal:2',
            'status_changed_at' => 'datetime',
            'post_date' => 'date',
            'sale_date' => 'date',
            'return_date' => 'date',
            'form_data' => 'array',
            'voice_note_uploaded_at' => 'datetime',
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
    public function submittedAt(): CarbonInterface
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
    public function statusChangedAt(): ?CarbonInterface
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
            'syntax' => CarbonInterface::DIFF_ABSOLUTE,
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
     * The date belonging to the current status, if that status has one.
     */
    public function statusDate(): ?CarbonInterface
    {
        $column = self::STATUS_DATES[$this->status]['column'] ?? null;

        return $column ? $this->{$column} : null;
    }

    /**
     * What that date is called, e.g. "Sale Date".
     */
    public function statusDateLabel(): ?string
    {
        return self::STATUS_DATES[$this->status]['label'] ?? null;
    }

    /**
     * That date formatted for display, or null.
     */
    public function statusDateValue(): ?string
    {
        return $this->statusDate()?->format('M j, Y');
    }

    /**
     * Every date this order has picked up, keyed by its label.
     *
     * @return array<string, string>
     */
    public function allStatusDates(): array
    {
        $dates = [];

        foreach (self::STATUS_DATES as $meta) {
            $value = $this->{$meta['column']};

            if ($value) {
                $dates[$meta['label']] = $value->format('M j, Y');
            }
        }

        return $dates;
    }

    /**
     * Whether a voice note is attached.
     */
    public function hasVoiceNote(): bool
    {
        return filled($this->voice_note_path);
    }

    /**
     * Public URL of the attached voice note, if any.
     */
    public function voiceNoteUrl(): ?string
    {
        return $this->hasVoiceNote()
            ? Storage::disk('public')->url($this->voice_note_path)
            : null;
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
