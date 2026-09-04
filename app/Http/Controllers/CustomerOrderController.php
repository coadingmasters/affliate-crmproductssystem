<?php

namespace App\Http\Controllers;

use App\Models\FormField;
use App\Models\Order;
use App\Models\Product;
use App\Support\DateRange;
use App\Support\OrderFilters;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
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
     * Audio containers a voice note may use.
     *
     * Deliberately broad, since phones and browsers record in a variety of
     * formats, but video containers are not accepted.
     */
    public const VOICE_EXTENSIONS = [
        'mp3', 'm4a', 'm4b', 'aac', 'wav', 'wave', 'ogg', 'oga', 'opus',
        'weba', 'amr', 'flac', 'wma', 'caf', 'aiff', 'aif', 'mid', '3ga',
    ];

    /**
     * The ceiling we allow, regardless of how generous PHP is configured.
     */
    public const MAX_UPLOAD_KB = 102400; // 100 MB

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
            'customFields' => FormField::all()->keyBy('key'),
            'maxUploadKb' => $this->maxUploadKb(),
            'intendedMaxKb' => self::MAX_UPLOAD_KB,
            'allowedExtensions' => self::VOICE_EXTENSIONS,
        ]);
    }

    /**
     * Attach or replace the voice note on an order.
     */
    public function storeVoiceNote(Request $request, Order $order): RedirectResponse|JsonResponse
    {
        $this->authorizeOrder($request, $order);

        $request->validate([
            'voice_note' => [
                'required',
                'file',
                'max:'.$this->maxUploadKb(),
                'extensions:'.implode(',', self::VOICE_EXTENSIONS),

                // Extensions alone are easy to rename, so refuse anything the
                // server can see is a video.
                function (string $attribute, $value, callable $fail) {
                    if ($value && str_starts_with((string) $value->getMimeType(), 'video/')) {
                        $fail('Video files are not accepted. Please upload a voice recording.');
                    }
                },
            ],
        ], [
            'voice_note.required' => 'Choose a recording to upload.',
            'voice_note.max' => 'That recording is larger than the '.round($this->maxUploadKb() / 1024).' MB limit.',
            'voice_note.extensions' => 'Use an audio recording, for example MP3, M4A, WAV, OGG or AMR.',
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

        $message = $previous ? 'Voice note replaced.' : 'Voice note uploaded.';

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'name' => $order->voice_note_name,
                'url' => $order->voiceNoteUrl(),
                'added' => $order->voice_note_uploaded_at?->timezone(config('app.display_timezone'))->diffForHumans(),
            ]);
        }

        return redirect()
            ->route('order.show', $order)
            ->with('status', $message);
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
            self::MAX_UPLOAD_KB,
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
        $sort = $request->query('sort');
        $perPage = (int) $request->query('per_page', 10);

        return OrderFilters::parse($request, withAccounts: false) + [
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
        OrderFilters::apply($query, $filters);

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
        return OrderFilters::activeCount($filters)
            + ($filters['sort'] !== 'newest' ? 1 : 0);
    }
}
