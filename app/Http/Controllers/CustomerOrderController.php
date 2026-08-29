<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Support\DateRange;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CustomerOrderController extends Controller
{
    /**
     * Sort options offered to the customer.
     */
    public const SORTS = [
        'newest' => 'Newest first',
        'oldest' => 'Oldest first',
        'total_desc' => 'Highest total',
        'total_asc' => 'Lowest total',
    ];

    /**
     * Page sizes.
     */
    public const PER_PAGE = [10, 25, 50];

    /**
     * Audio and video containers a voice note may use.
     *
     * Deliberately broad, since phones record in a variety of formats.
     */
    public const VOICE_EXTENSIONS = [
        'mp3', 'mp4', 'm4a', 'wav', 'ogg', 'oga', 'opus',
        'webm', 'aac', 'amr', '3gp', '3gpp', 'flac', 'wma', 'caf', 'mov',
    ];

    /**
     * The customer's own orders, filtered.
     */
    public function index(Request $request): View
    {
        $filters = $this->filters($request);

        $query = $request->user()->orders()
            ->with(['product', 'productPrice'])
            ->tap(fn (Builder $q) => $this->applyFilters($q, $filters));

        $totals = (clone $query)
            ->selectRaw('COUNT(*) as orders, COALESCE(SUM(total_price), 0) as value')
            ->reorder()
            ->first();

        return view('frontend.orders.index', [
            'orders' => $query->paginate($filters['per_page'])->withQueryString(),
            'filters' => $filters,
            'periods' => DateRange::PERIODS,
            'sorts' => self::SORTS,
            'perPageOptions' => self::PER_PAGE,
            'statusMeta' => Order::STATUS_META,
            'products' => Product::orderBy('name')->get(['id', 'name']),
            'totalOrders' => (int) ($totals->orders ?? 0),
            'totalValue' => (float) ($totals->value ?? 0),
            'activeFilterCount' => $this->activeFilterCount($filters),
        ]);
    }

    /**
     * A single order belonging to the signed in customer.
     */
    public function show(Request $request, Order $order): View
    {
        $this->authorizeOrder($request, $order);

        $order->load(['product', 'productPrice']);

        return view('frontend.orders.show', [
            'order' => $order,
            'customFields' => \App\Models\FormField::all()->keyBy('key'),
            'maxUploadKb' => $this->maxUploadKb(),
            'allowedExtensions' => self::VOICE_EXTENSIONS,
        ]);
    }

    /**
     * Attach or replace the voice note on an order.
     */
    public function storeVoiceNote(Request $request, Order $order): RedirectResponse
    {
        $this->authorizeOrder($request, $order);

        $request->validate([
            'voice_note' => [
                'required',
                'file',
                'max:'.$this->maxUploadKb(),
                'extensions:'.implode(',', self::VOICE_EXTENSIONS),
            ],
        ], [
            'voice_note.required' => 'Choose a file to upload.',
            'voice_note.max' => 'That file is larger than the '.round($this->maxUploadKb() / 1024, 1).' MB limit.',
            'voice_note.extensions' => 'Use an audio or video recording, for example MP3, MP4, M4A, WAV or OGG.',
        ]);

        $previous = $order->voice_note_path;

        $file = $request->file('voice_note');

        // Keep the uploader's own extension. Guessing it from the file's
        // contents produces ".bin" for formats like amr, 3gp and caf, which
        // then neither play in the browser nor pick the right player.
        $extension = strtolower($file->getClientOriginalExtension() ?: 'bin');

        $order->update([
            'voice_note_path' => $file->storeAs(
                'voice-notes',
                Str::random(40).'.'.$extension,
                'public',
            ),
            'voice_note_name' => $file->getClientOriginalName(),
            'voice_note_uploaded_at' => now(),
        ]);

        // Replacing should not leave the old recording behind.
        if ($previous) {
            Storage::disk('public')->delete($previous);
        }

        return redirect()
            ->route('order.show', $order)
            ->with('status', $previous ? 'Voice note replaced.' : 'Voice note uploaded.');
    }

    /**
     * Remove the voice note from an order.
     */
    public function destroyVoiceNote(Request $request, Order $order): RedirectResponse
    {
        $this->authorizeOrder($request, $order);

        if ($order->voice_note_path) {
            Storage::disk('public')->delete($order->voice_note_path);
        }

        $order->update([
            'voice_note_path' => null,
            'voice_note_name' => null,
            'voice_note_uploaded_at' => null,
        ]);

        return redirect()
            ->route('order.show', $order)
            ->with('status', 'Voice note removed.');
    }

    /**
     * A customer may only ever touch their own orders.
     */
    private function authorizeOrder(Request $request, Order $order): void
    {
        abort_unless($order->user_id === $request->user()->id, 404);
    }

    /**
     * The largest upload PHP will actually accept, in kilobytes.
     *
     * Validating against the real limit means the message tells the truth
     * rather than promising a size the server will reject.
     */
    private function maxUploadKb(): int
    {
        $toKb = function (string $value): int {
            $value = trim($value);
            $unit = strtolower(substr($value, -1));
            $number = (int) $value;

            return match ($unit) {
                'g' => $number * 1024 * 1024,
                'm' => $number * 1024,
                'k' => $number,
                default => (int) ($number / 1024),
            };
        };

        $limits = array_filter([
            $toKb((string) ini_get('upload_max_filesize')),
            $toKb((string) ini_get('post_max_size')),
            20480, // 20 MB ceiling of our own
        ]);

        return max(256, min($limits));
    }

    /**
     * Read and sanitise the filters.
     *
     * @return array<string, mixed>
     */
    private function filters(Request $request): array
    {
        $status = $request->query('status');
        $period = $request->query('period');
        $sort = $request->query('sort');
        $perPage = (int) $request->query('per_page', 10);

        return [
            'q' => trim((string) $request->query('q', '')),
            'status' => in_array($status, Order::statuses(), true) ? $status : 'all',
            'period' => array_key_exists((string) $period, DateRange::PERIODS) ? $period : 'all',
            'from' => DateRange::parseDate($request->query('from')),
            'to' => DateRange::parseDate($request->query('to')),
            'product_id' => $request->query('product_id') ? (int) $request->query('product_id') : null,
            'sort' => array_key_exists((string) $sort, self::SORTS) ? $sort : 'newest',
            'per_page' => in_array($perPage, self::PER_PAGE, true) ? $perPage : 10,
        ];
    }

    /**
     * Apply the filters to the query.
     *
     * @param  array<string, mixed>  $filters
     */
    private function applyFilters(Builder $query, array $filters): void
    {
        if ($filters['q'] !== '') {
            $term = '%'.$filters['q'].'%';

            $query->where(function (Builder $q) use ($term, $filters) {
                $q->where('full_name', 'like', $term)
                    ->orWhere('address', 'like', $term)
                    ->orWhere('phone', 'like', $term);

                if (ctype_digit($filters['q'])) {
                    $q->orWhere('id', (int) $filters['q']);
                }
            });
        }

        if ($filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }

        if ($filters['product_id']) {
            $query->where('product_id', $filters['product_id']);
        }

        [$from, $to] = DateRange::resolve($filters['period'], $filters['from'], $filters['to']);

        if ($from) {
            $query->where('created_at', '>=', $from);
        }

        if ($to) {
            $query->where('created_at', '<=', $to);
        }

        match ($filters['sort']) {
            'oldest' => $query->oldest(),
            'total_desc' => $query->orderByDesc('total_price'),
            'total_asc' => $query->orderBy('total_price'),
            default => $query->latest(),
        };
    }

    /**
     * How many filters are narrowing the list.
     *
     * @param  array<string, mixed>  $filters
     */
    private function activeFilterCount(array $filters): int
    {
        return collect([
            $filters['q'] !== '',
            $filters['status'] !== 'all',
            $filters['period'] !== 'all',
            $filters['product_id'] !== null,
            $filters['sort'] !== 'newest',
        ])->filter()->count();
    }
}
