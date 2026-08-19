<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['product_id', 'label', 'price', 'user_commission', 'admin_commission'])]
class ProductPrice extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'user_commission' => 'decimal:2',
            'admin_commission' => 'decimal:2',
        ];
    }

    /**
     * The product this price option belongs to.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * The orders placed against this price option.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
