<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Wedding;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class WeddingController extends Controller
{
    private const BUILDER_DEFAULTS = [
        'permissions' => [
            'tenant_builder_enabled' => false,
            'block_builder' => false,
            'custom_decorations' => false,
            'custom_font' => false,
            'custom_text' => true,
            'character_image' => false,
            'background_image' => false,
            'decoration_animation' => false,
            'music' => false,
            'palette' => false,
            'block_visibility' => true,
            'block_order' => true,
        ],
        'content' => [
            'font_family' => 'font-sans',
            'music_url' => null,
            'music_uploaded_url' => null,
            'music_autoplay' => false,
            'palette' => [
                'primary' => '#065f46',
                'secondary' => '#d4af37',
                'background' => '#fdf8f0',
                'text' => '#1f2937',
            ],
            'background_image' => ['type' => 'none', 'url' => null, 'uploaded_url' => null],
            'cover_background_image' => ['type' => 'none', 'url' => null, 'uploaded_url' => null],
            'cover_decorations' => [
                'top'    => ['type' => 'none', 'url' => null, 'uploaded_url' => null, 'animation' => 'none', 'x' => 50, 'y' =>  5, 'size' => 200, 'opacity' => 90],
                'bottom' => ['type' => 'none', 'url' => null, 'uploaded_url' => null, 'animation' => 'none', 'x' => 50, 'y' => 90, 'size' => 200, 'opacity' => 90],
                'left'   => ['type' => 'none', 'url' => null, 'uploaded_url' => null, 'animation' => 'none', 'x' =>  5, 'y' => 50, 'size' => 150, 'opacity' => 90],
                'right'  => ['type' => 'none', 'url' => null, 'uploaded_url' => null, 'animation' => 'none', 'x' => 95, 'y' => 50, 'size' => 150, 'opacity' => 90],
            ],
            'content_decorations' => [
                'top'    => ['type' => 'none', 'url' => null, 'uploaded_url' => null, 'animation' => 'none', 'x' => 50, 'y' =>  5, 'size' => 200, 'opacity' => 90],
                'bottom' => ['type' => 'none', 'url' => null, 'uploaded_url' => null, 'animation' => 'none', 'x' => 50, 'y' => 90, 'size' => 200, 'opacity' => 90],
                'left'   => ['type' => 'none', 'url' => null, 'uploaded_url' => null, 'animation' => 'none', 'x' =>  5, 'y' => 50, 'size' => 150, 'opacity' => 90],
                'right'  => ['type' => 'none', 'url' => null, 'uploaded_url' => null, 'animation' => 'none', 'x' => 95, 'y' => 50, 'size' => 150, 'opacity' => 90],
            ],
            'character_image' => ['type' => 'preset-couple-1', 'url' => null, 'uploaded_url' => null, 'size' => 120, 'x' => 50, 'y' => 35],
            'custom_text' => [
                'cover_intro' => null,
                'cover_button_label' => 'Buka Undangan',
                'closing_note' => null,
            ],
            'blocks' => [
                ['id' => 'ayat',      'label' => 'Ayat',     'enabled' => true],
                ['id' => 'countdown', 'label' => 'Countdown', 'enabled' => true],
                ['id' => 'mempelai',  'label' => 'Mempelai',  'enabled' => true],
                ['id' => 'acara',     'label' => 'Acara',     'enabled' => true],
                ['id' => 'lokasi',    'label' => 'Lokasi',    'enabled' => true],
                ['id' => 'gift',      'label' => 'Gift',      'enabled' => true],
                ['id' => 'rsvp',      'label' => 'RSVP',      'enabled' => true],
                ['id' => 'doa',       'label' => 'Doa',       'enabled' => true],
                ['id' => 'penutup',   'label' => 'Penutup',   'enabled' => true],
            ],
        ],
    ];

    /**
     * Display a listing of the authenticated user's weddings.
     */
    public function index(Request $request): Response
    {
        $weddingsQuery = in_array($request->user()->role, ['super_admin', 'admin'], true)
            ? Wedding::query()
            : $request->user()->weddings();

        $weddings = $weddingsQuery
            ->with(['coupleProfiles' => fn ($query) => $query->orderBy('sort_order')])
            ->with('user:id,name,email')
            ->withCount(['guests', 'rsvps', 'wishes'])
            ->orderByDesc('created_at')
            ->get();

        return Inertia::render('Admin/Weddings/Index', [
            'weddings' => $weddings,
        ]);
    }

    /**
     * Show the form for creating a new wedding.
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Weddings/Create');
    }

    /**
     * Store a newly created wedding.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'cover_title' => ['required', 'string', 'max:255'],
            'cover_subtitle' => ['nullable', 'string', 'max:255'],
            'wedding_date' => ['required', 'date'],
            'timezone' => ['nullable', 'string', 'max:64'],
            'welcome_text' => ['nullable', 'string'],
            'closing_text' => ['nullable', 'string'],
            'rsvp_required' => ['nullable', 'boolean'],
            'comments_need_approval' => ['nullable', 'boolean'],
            'pax_buffer_percentage' => ['nullable', 'integer', 'min:0', 'max:100'],
        ]);

        $wedding = $request->user()->weddings()->create([
            ...$validated,
            'slug' => Str::slug($validated['cover_title']) . '-' . Str::random(6),
            'status' => 'draft',
            'rsvp_required' => $validated['rsvp_required'] ?? true,
            'comments_need_approval' => $validated['comments_need_approval'] ?? true,
            'pax_buffer_percentage' => $validated['pax_buffer_percentage'] ?? 10,
        ]);

        return redirect()->route('dashboard.weddings.show', $wedding)
            ->with('success', 'Undangan berhasil dibuat.');
    }

    /**
     * Display the specified wedding.
     */
    public function show(Request $request, Wedding $wedding): Response
    {
        $this->authorizeWedding($request, $wedding);

        $wedding->load(['events', 'coupleProfiles', 'weddingVerses', 'giftBankAccounts', 'giftAddresses']);
        $wedding->loadCount(['guests', 'rsvps', 'wishes']);

        return Inertia::render('Admin/Weddings/Show', [
            'wedding' => $wedding,
            'builderConfig' => $this->resolveBuilderConfig($wedding),
            'previewUrl' => $this->previewUrl($wedding),
        ]);
    }

    /**
     * Show the form for editing the specified wedding.
     */
    public function edit(Request $request, Wedding $wedding): Response
    {
        $this->authorizeWedding($request, $wedding);

        $wedding->load(['coupleProfiles' => fn ($query) => $query->orderBy('sort_order')]);

        return Inertia::render('Admin/Weddings/Edit', [
            'wedding' => $wedding,
        ]);
    }

    public function builder(Request $request, Wedding $wedding): Response
    {
        $this->authorizeWedding($request, $wedding);

        $builderConfig = $this->resolveBuilderConfig($wedding);

        return Inertia::render('Admin/Weddings/Builder', [
            'wedding' => $wedding,
            'builderConfig' => $builderConfig,
            'builderCatalog' => [
                'fonts' => [
                    ['label' => 'Sans Clean', 'value' => 'font-sans'],
                    ['label' => 'Serif Elegant', 'value' => 'font-serif'],
                    ['label' => 'Mono Modern', 'value' => 'font-mono'],
                ],
                'decorations' => [
                    ['label' => 'Floral Emerald', 'value' => 'preset-floral-1'],
                    ['label' => 'Floral Gold', 'value' => 'preset-floral-2'],
                    ['label' => 'Lantern Islamic', 'value' => 'preset-lantern-1'],
                    ['label' => 'Arch Ornament', 'value' => 'preset-arch-1'],
                ],
                'characters' => [
                    ['label' => 'Couple Classic', 'value' => 'preset-couple-1'],
                    ['label' => 'Couple Chibi', 'value' => 'preset-couple-2'],
                    ['label' => 'Couple Silhouette', 'value' => 'preset-couple-3'],
                ],
                'animations' => [
                    ['label' => 'None', 'value' => 'none'],
                    ['label' => 'Float', 'value' => 'float'],
                    ['label' => 'Pulse', 'value' => 'pulse'],
                    ['label' => 'Spin Slow', 'value' => 'spin-slow'],
                ],
            ],
            'mode' => $this->isAdmin($request) ? 'admin' : 'tenant',
            'isPremium' => $this->isAdmin($request) || $request->user()->is_premium,
            'previewUrl' => $this->previewUrl($wedding),
            'canEditPermissionMatrix' => $this->isAdmin($request),
        ]);
    }

    public function updateBuilder(Request $request, Wedding $wedding): RedirectResponse
    {
        $this->authorizeWedding($request, $wedding);

        $current = $this->resolveBuilderConfig($wedding);

        if (! $this->isAdmin($request) && ! data_get($current, 'permissions.tenant_builder_enabled')) {
            abort(403, 'Invitation builder belum diaktifkan oleh admin.');
        }

        $validated = $request->validate([
            'builder.permissions.tenant_builder_enabled' => ['nullable', 'boolean'],
            'builder.permissions.block_builder' => ['nullable', 'boolean'],
            'builder.permissions.custom_decorations' => ['nullable', 'boolean'],
            'builder.permissions.custom_font' => ['nullable', 'boolean'],
            'builder.permissions.custom_text' => ['nullable', 'boolean'],
            'builder.permissions.character_image' => ['nullable', 'boolean'],
            'builder.permissions.background_image' => ['nullable', 'boolean'],
            'builder.permissions.decoration_animation' => ['nullable', 'boolean'],
            'builder.permissions.music' => ['nullable', 'boolean'],
            'builder.permissions.palette' => ['nullable', 'boolean'],
            'builder.permissions.block_visibility' => ['nullable', 'boolean'],
            'builder.permissions.block_order' => ['nullable', 'boolean'],

            'builder.content.font_family' => ['nullable', 'string', 'max:50'],
            'builder.content.music_url' => ['nullable', 'string', 'max:1000'],
            'builder.content.music_uploaded_url' => ['nullable', 'string', 'max:1000'],
            'builder.content.music_autoplay' => ['nullable', 'boolean'],
            'builder.content.music_file' => ['nullable', 'file', 'mimes:mp3,wav,ogg,m4a,aac', 'max:10240'],
            'builder.content.palette.primary' => ['nullable', 'string', 'max:20'],
            'builder.content.palette.secondary' => ['nullable', 'string', 'max:20'],
            'builder.content.palette.background' => ['nullable', 'string', 'max:20'],
            'builder.content.palette.text' => ['nullable', 'string', 'max:20'],
            // Cover decorations - 4 slots
            'builder.content.cover_decorations.top.type' => ['nullable', 'string', 'max:100'],
            'builder.content.cover_decorations.top.url' => ['nullable', 'string', 'max:1000'],
            'builder.content.cover_decorations.top.animation' => ['nullable', 'string', 'max:50'],
            'builder.content.cover_decorations.top.x' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'builder.content.cover_decorations.top.y' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'builder.content.cover_decorations.top.size' => ['nullable', 'integer', 'min:30', 'max:600'],
            'builder.content.cover_decorations.top.opacity' => ['nullable', 'integer', 'min:10', 'max:100'],
            'builder.content.cover_decorations.bottom.type' => ['nullable', 'string', 'max:100'],
            'builder.content.cover_decorations.bottom.url' => ['nullable', 'string', 'max:1000'],
            'builder.content.cover_decorations.bottom.animation' => ['nullable', 'string', 'max:50'],
            'builder.content.cover_decorations.bottom.x' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'builder.content.cover_decorations.bottom.y' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'builder.content.cover_decorations.bottom.size' => ['nullable', 'integer', 'min:30', 'max:600'],
            'builder.content.cover_decorations.bottom.opacity' => ['nullable', 'integer', 'min:10', 'max:100'],
            'builder.content.cover_decorations.left.type' => ['nullable', 'string', 'max:100'],
            'builder.content.cover_decorations.left.url' => ['nullable', 'string', 'max:1000'],
            'builder.content.cover_decorations.left.animation' => ['nullable', 'string', 'max:50'],
            'builder.content.cover_decorations.left.x' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'builder.content.cover_decorations.left.y' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'builder.content.cover_decorations.left.size' => ['nullable', 'integer', 'min:30', 'max:600'],
            'builder.content.cover_decorations.left.opacity' => ['nullable', 'integer', 'min:10', 'max:100'],
            'builder.content.cover_decorations.right.type' => ['nullable', 'string', 'max:100'],
            'builder.content.cover_decorations.right.url' => ['nullable', 'string', 'max:1000'],
            'builder.content.cover_decorations.right.animation' => ['nullable', 'string', 'max:50'],
            'builder.content.cover_decorations.right.x' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'builder.content.cover_decorations.right.y' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'builder.content.cover_decorations.right.size' => ['nullable', 'integer', 'min:30', 'max:600'],
            'builder.content.cover_decorations.right.opacity' => ['nullable', 'integer', 'min:10', 'max:100'],
            // Content decorations - 4 slots
            'builder.content.content_decorations.top.type' => ['nullable', 'string', 'max:100'],
            'builder.content.content_decorations.top.url' => ['nullable', 'string', 'max:1000'],
            'builder.content.content_decorations.top.animation' => ['nullable', 'string', 'max:50'],
            'builder.content.content_decorations.top.x' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'builder.content.content_decorations.top.y' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'builder.content.content_decorations.top.size' => ['nullable', 'integer', 'min:30', 'max:600'],
            'builder.content.content_decorations.top.opacity' => ['nullable', 'integer', 'min:10', 'max:100'],
            'builder.content.content_decorations.bottom.type' => ['nullable', 'string', 'max:100'],
            'builder.content.content_decorations.bottom.url' => ['nullable', 'string', 'max:1000'],
            'builder.content.content_decorations.bottom.animation' => ['nullable', 'string', 'max:50'],
            'builder.content.content_decorations.bottom.x' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'builder.content.content_decorations.bottom.y' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'builder.content.content_decorations.bottom.size' => ['nullable', 'integer', 'min:30', 'max:600'],
            'builder.content.content_decorations.bottom.opacity' => ['nullable', 'integer', 'min:10', 'max:100'],
            'builder.content.content_decorations.left.type' => ['nullable', 'string', 'max:100'],
            'builder.content.content_decorations.left.url' => ['nullable', 'string', 'max:1000'],
            'builder.content.content_decorations.left.animation' => ['nullable', 'string', 'max:50'],
            'builder.content.content_decorations.left.x' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'builder.content.content_decorations.left.y' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'builder.content.content_decorations.left.size' => ['nullable', 'integer', 'min:30', 'max:600'],
            'builder.content.content_decorations.left.opacity' => ['nullable', 'integer', 'min:10', 'max:100'],
            'builder.content.content_decorations.right.type' => ['nullable', 'string', 'max:100'],
            'builder.content.content_decorations.right.url' => ['nullable', 'string', 'max:1000'],
            'builder.content.content_decorations.right.animation' => ['nullable', 'string', 'max:50'],
            'builder.content.content_decorations.right.x' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'builder.content.content_decorations.right.y' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'builder.content.content_decorations.right.size' => ['nullable', 'integer', 'min:30', 'max:600'],
            'builder.content.content_decorations.right.opacity' => ['nullable', 'integer', 'min:10', 'max:100'],
            // Cover background
            'builder.content.cover_background_image.type' => ['nullable', 'string', 'max:100'],
            'builder.content.cover_background_image.url' => ['nullable', 'string', 'max:1000'],
            'builder.content.cover_background_image.uploaded_url' => ['nullable', 'string', 'max:1000'],
            'builder.content.character_image.type' => ['nullable', 'string', 'max:100'],
            'builder.content.character_image.url' => ['nullable', 'string', 'max:1000'],
            'builder.content.character_image.uploaded_url' => ['nullable', 'string', 'max:1000'],
            'builder.content.character_image.file' => ['nullable', 'image', 'max:4096'],
            'builder.content.character_image.size' => ['nullable', 'integer', 'min:30', 'max:600'],
            'builder.content.character_image.x' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'builder.content.character_image.y' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'builder.content.background_image.type' => ['nullable', 'string', 'max:100'],
            'builder.content.background_image.url' => ['nullable', 'string', 'max:1000'],
            'builder.content.background_image.uploaded_url' => ['nullable', 'string', 'max:1000'],
            'builder.content.background_image.file' => ['nullable', 'image', 'max:4096'],
            'builder.content.custom_text.cover_intro' => ['nullable', 'string', 'max:1000'],
            'builder.content.custom_text.cover_button_label' => ['nullable', 'string', 'max:100'],
            'builder.content.custom_text.closing_note' => ['nullable', 'string', 'max:1000'],
            'builder.content.blocks' => ['nullable', 'array'],
            'builder.content.blocks.*.id' => ['required_with:builder.content.blocks', 'string', 'max:50'],
            'builder.content.blocks.*.label' => ['nullable', 'string', 'max:100'],
            'builder.content.blocks.*.enabled' => ['nullable', 'boolean'],
        ]);

        $incoming = $validated['builder'] ?? [];

        if (! $this->isAdmin($request)) {
            $incoming = $this->tenantAllowedBuilderPayload($current, $incoming);
        }

        $this->applyUploadedAssets($request, $wedding, $incoming);

        data_forget($incoming, 'content.music_file');
        data_forget($incoming, 'content.cover_background_image.file');
        data_forget($incoming, 'content.character_image.file');
        data_forget($incoming, 'content.background_image.file');

        $merged = array_replace_recursive($current, $incoming);

        // Explicitly sync permissions for admin to avoid array_replace_recursive retaining un-checked checkboxes
        if ($this->isAdmin($request)) {
            $permissionKeys = [
                'tenant_builder_enabled',
                'block_builder',
                'custom_decorations',
                'custom_font',
                'custom_text',
                'character_image',
                'background_image',
                'decoration_animation',
                'music',
                'palette',
                'block_visibility',
                'block_order',
            ];
            foreach ($permissionKeys as $pKey) {
                $rawVal = $request->input("builder.permissions.{$pKey}");
                $merged['permissions'][$pKey] = filter_var($rawVal, FILTER_VALIDATE_BOOLEAN);
            }
        }

        // Explicitly sync blocks array so that enabled states and order are accurately preserved
        if (isset($incoming['content']['blocks']) && is_array($incoming['content']['blocks'])) {
            $merged['content']['blocks'] = array_values(array_map(function ($block) {
                return [
                    'id' => (string) ($block['id'] ?? ''),
                    'label' => (string) ($block['label'] ?? ''),
                    'enabled' => filter_var($block['enabled'] ?? false, FILTER_VALIDATE_BOOLEAN),
                ];
            }, $incoming['content']['blocks']));
        }

        $wedding->update([
            'theme_config' => array_replace_recursive($wedding->theme_config ?? [], [
                'builder' => $merged,
            ]),
        ]);

        return redirect()->route('dashboard.weddings.builder', $wedding)
            ->with('success', 'Builder undangan berhasil diperbarui.');
    }

    /**
     * Update the specified wedding.
     */
    public function update(Request $request, Wedding $wedding): RedirectResponse
    {
        $this->authorizeWedding($request, $wedding);

        $validated = $request->validate([
            'cover_title' => ['required', 'string', 'max:255'],
            'cover_subtitle' => ['nullable', 'string', 'max:255'],
            'wedding_date' => ['required', 'date'],
            'timezone' => ['nullable', 'string', 'max:64'],
            'welcome_text' => ['nullable', 'string'],
            'closing_text' => ['nullable', 'string'],
            'theme_config' => ['nullable', 'array'],
            'rsvp_required' => ['nullable', 'boolean'],
            'comments_need_approval' => ['nullable', 'boolean'],
            'pax_buffer_percentage' => ['nullable', 'integer', 'min:0', 'max:100'],
        ]);

        $wedding->update($validated);

        return redirect()->route('dashboard.weddings.show', $wedding)
            ->with('success', 'Undangan berhasil diperbarui.');
    }

    /**
     * Remove the specified wedding.
     */
    public function destroy(Request $request, Wedding $wedding): RedirectResponse
    {
        $this->authorizeWedding($request, $wedding);

        $wedding->delete();

        return redirect()->route('dashboard.weddings.index')
            ->with('success', 'Undangan berhasil dihapus.');
    }

    /**
     * Publish the specified wedding (change status from draft to published).
     */
    public function publish(Request $request, Wedding $wedding): RedirectResponse
    {
        $this->authorizeWedding($request, $wedding);

        $wedding->update(['status' => 'published']);

        return redirect()->route('dashboard.weddings.show', $wedding)
            ->with('success', 'Undangan berhasil dipublikasikan!');
    }

    /**
     * Ensure the wedding belongs to the authenticated user.
     */
    private function authorizeWedding(Request $request, Wedding $wedding): void
    {
        $this->authorize('view', $wedding);
    }

    private function resolveBuilderConfig(Wedding $wedding): array
    {
        return array_replace_recursive(
            self::BUILDER_DEFAULTS,
            $wedding->theme_config['builder'] ?? []
        );
    }

    private function isAdmin(Request $request): bool
    {
        return in_array($request->user()?->role, ['super_admin', 'admin'], true);
    }

    private function tenantAllowedBuilderPayload(array $current, array $incoming): array
    {
        $allowed = [];

        if (data_get($current, 'permissions.custom_font')) {
            data_set($allowed, 'content.font_family', data_get($incoming, 'content.font_family'));
        }

        if (data_get($current, 'permissions.palette')) {
            data_set($allowed, 'content.palette', data_get($incoming, 'content.palette', []));
        }

        if (data_get($current, 'permissions.custom_decorations')) {
            foreach (['top', 'bottom', 'left', 'right'] as $slot) {
                foreach (['type', 'url', 'uploaded_url', 'x', 'y', 'size', 'opacity'] as $field) {
                    data_set($allowed, "content.cover_decorations.{$slot}.{$field}",
                        data_get($incoming, "content.cover_decorations.{$slot}.{$field}"));
                    data_set($allowed, "content.content_decorations.{$slot}.{$field}",
                        data_get($incoming, "content.content_decorations.{$slot}.{$field}"));
                }
            }
            data_set($allowed, 'content.cover_background_image', data_get($incoming, 'content.cover_background_image', []));
        }

        if (data_get($current, 'permissions.decoration_animation')) {
            foreach (['top', 'bottom', 'left', 'right'] as $slot) {
                data_set($allowed, "content.cover_decorations.{$slot}.animation",
                    data_get($incoming, "content.cover_decorations.{$slot}.animation"));
                data_set($allowed, "content.content_decorations.{$slot}.animation",
                    data_get($incoming, "content.content_decorations.{$slot}.animation"));
            }
        }

        if (data_get($current, 'permissions.character_image')) {
            data_set($allowed, 'content.character_image', data_get($incoming, 'content.character_image', []));
        }

        if (data_get($current, 'permissions.background_image')) {
            data_set($allowed, 'content.background_image', data_get($incoming, 'content.background_image', []));
        }

        if (data_get($current, 'permissions.custom_text')) {
            data_set($allowed, 'content.custom_text', data_get($incoming, 'content.custom_text', []));
        }

        if (data_get($current, 'permissions.music')) {
            data_set($allowed, 'content.music_url', data_get($incoming, 'content.music_url'));
            data_set($allowed, 'content.music_uploaded_url', data_get($incoming, 'content.music_uploaded_url'));
            data_set($allowed, 'content.music_file', data_get($incoming, 'content.music_file'));
            data_set($allowed, 'content.music_autoplay', data_get($incoming, 'content.music_autoplay'));
        }

        if (data_get($current, 'permissions.block_builder')) {
            $blocks = data_get($incoming, 'content.blocks', []);
            if (! data_get($current, 'permissions.block_visibility')) {
                $blocks = collect($blocks)->map(function (array $block, int $index) use ($current) {
                    $block['enabled'] = data_get($current, "content.blocks.{$index}.enabled", true);
                    return $block;
                })->all();
            }

            if (! data_get($current, 'permissions.block_order')) {
                $blocks = data_get($current, 'content.blocks', []);
            }

            data_set($allowed, 'content.blocks', $blocks);
        }

        return $allowed;
    }

    private function applyUploadedAssets(Request $request, Wedding $wedding, array &$incoming): void
    {
        $basePath = "weddings/{$wedding->id}/builder";

        if ($request->hasFile('builder.content.cover_background_image.file')) {
            $path = $request->file('builder.content.cover_background_image.file')->store("{$basePath}/backgrounds", 'public');
            data_set($incoming, 'content.cover_background_image.uploaded_url', Storage::disk('public')->url($path));
        }

        if ($request->hasFile('builder.content.character_image.file')) {
            $path = $request->file('builder.content.character_image.file')->store("{$basePath}/characters", 'public');
            data_set($incoming, 'content.character_image.uploaded_url', Storage::disk('public')->url($path));
        }

        if ($request->hasFile('builder.content.background_image.file')) {
            $path = $request->file('builder.content.background_image.file')->store("{$basePath}/backgrounds", 'public');
            data_set($incoming, 'content.background_image.uploaded_url', Storage::disk('public')->url($path));
        }

        if ($request->hasFile('builder.content.music_file')) {
            $path = $request->file('builder.content.music_file')->store("{$basePath}/music", 'public');
            data_set($incoming, 'content.music_uploaded_url', Storage::disk('public')->url($path));
        }
    }

    private function previewUrl(Wedding $wedding): ?string
    {
        $guest = $wedding->guests()->oldest('id')->first(['token']);

        if (! $guest) {
            $guest = $wedding->guests()->create([
                'name' => 'Tamu Preview',
                'token' => Str::random(64),
                'slug' => 'tamu-preview-' . Str::random(6),
                'max_pax' => 2,
                'is_invitation_sent' => false,
            ]);
        }

        return route('public.invitation', ['wedding' => $wedding, 'token' => $guest->token]);
    }
}
