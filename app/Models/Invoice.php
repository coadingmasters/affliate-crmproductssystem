<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['order_id', 'user_id', 'amount', 'status', 'note', 'admin_note'])]
class Invoice extends Model
{
    /**
     * Where an invoice can stand, and how it reads on screen.
     *
     * The customer and the admin see the same words, the way order statuses
     * already work.
     */
    public const STATUS_META = [
        'pending' => ['label' => 'Pending', 'tone' => 'warning', 'help' => 'Waiting to be reviewed'],
        'paid' => ['label' => 'Paid', 'tone' => 'success', 'help' => 'Settled'],
        'rejected' => ['label' => 'Rejected', 'tone' => 'danger', 'help' => 'Not accepted'],
    ];

    /**
     * Keep the status change stamped, and hand out the next number.
     */
    protected static function booted(): void
    {
        static::created(function (self $invoice) {
            $invoice->forceFill([
                'number' => 'INV-'.str_pad((string) $invoice->id, 5, '0', STR_PAD_LEFT),
                'status_changed_at' => $invoice->status_changed_at ?? $invoice->freshTimestamp(),
            ])->saveQuietly();
        });

        static::updating(function (self $invoice) {
            if ($invoice->isDirty('status')) {
                $invoice->status_changed_at = $invoice->freshTimestamp();
            }
        });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'status_changed_at' => 'datetime',
        ];
    }

    /**
     * Every status this app accepts.
     *
     * @return array<int, string>
     */
    public static function statuses(): array
    {
        return array_keys(self::STATUS_META);
    }

    /**
     * The order being billed.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * The account that sent it.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * How this status reads on screen.
     */
    public function statusLabel(): string
    {
        return self::STATUS_META[$this->status]['label'] ?? ucfirst($this->status);
    }

    /**
     * Tailwind classes for the status chip.
     */
    public function statusClasses(): string
    {
        return match (self::STATUS_META[$this->status]['tone'] ?? 'muted') {
            'success' => 'bg-success/10 text-success',
            'warning' => 'bg-warning/10 text-warning',
            'danger' => 'bg-danger/10 text-danger',
            default => 'bg-elevated text-muted',
        };
    }

    /**
     * When the status last moved, in the reader's timezone.
     */
    public function statusChangedAtLabel(): ?string
    {
        return $this->status_changed_at
            ?->timezone(config('app.display_timezone'))
            ->format('M j, Y g:i A');
    }

    /**
     * When it was sent, in the reader's timezone.
     */
    public function sentAtLabel(): string
    {
        return $this->created_at
            ->timezone(config('app.display_timezone'))
            ->format('M j, Y g:i A');
    }
}
