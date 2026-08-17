import sys

file_path = "/Users/user/Work/WalimYuk/walimyuk-app/app/Http/Controllers/Dashboard/WeddingController.php"
with open(file_path, "r") as f:
    content = f.read()

# Change 1
old_builder_defaults = """    private const BUILDER_DEFAULTS = [
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
            'decorations' => [
                'top_left' => ['type' => 'preset-floral-1', 'url' => null, 'uploaded_url' => null, 'animation' => 'none'],
                'bottom_right' => ['type' => 'preset-floral-2', 'url' => null, 'uploaded_url' => null, 'animation' => 'none'],
            ],
            'character_image' => ['type' => 'preset-couple-1', 'url' => null, 'uploaded_url' => null],
            'background_image' => ['type' => 'none', 'url' => null, 'uploaded_url' => null],
            'custom_text' => [
                'cover_intro' => null,
                'cover_button_label' => 'Buka Undangan',
                'closing_note' => null,
            ],
            'blocks' => [
                ['id' => 'ayat', 'label' => 'Ayat', 'enabled' => true],
                ['id' => 'countdown', 'label' => 'Countdown', 'enabled' => true],
                ['id' => 'mempelai', 'label' => 'Mempelai', 'enabled' => true],
                ['id' => 'acara', 'label' => 'Acara', 'enabled' => true],
                ['id' => 'lokasi', 'label' => 'Lokasi', 'enabled' => true],
                ['id' => 'gift', 'label' => 'Gift', 'enabled' => true],
                ['id' => 'rsvp', 'label' => 'RSVP', 'enabled' => true],
                ['id' => 'doa', 'label' => 'Doa', 'enabled' => true],
                ['id' => 'penutup', 'label' => 'Penutup', 'enabled' => true],
            ],
        ],
    ];"""

new_builder_defaults = """    private const BUILDER_DEFAULTS = [
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
    ];"""

if old_builder_defaults in content:
    content = content.replace(old_builder_defaults, new_builder_defaults)
else:
    print("Change 1 failed to match")


# Change 2
old_decorations_validation = """            'builder.content.decorations.top_left.type' => ['nullable', 'string', 'max:100'],
            'builder.content.decorations.top_left.url' => ['nullable', 'string', 'max:1000'],
            'builder.content.decorations.top_left.uploaded_url' => ['nullable', 'string', 'max:1000'],
            'builder.content.decorations.top_left.file' => ['nullable', 'image', 'max:4096'],
            'builder.content.decorations.top_left.animation' => ['nullable', 'string', 'max:50'],
            'builder.content.decorations.bottom_right.type' => ['nullable', 'string', 'max:100'],
            'builder.content.decorations.bottom_right.url' => ['nullable', 'string', 'max:1000'],
            'builder.content.decorations.bottom_right.uploaded_url' => ['nullable', 'string', 'max:1000'],
            'builder.content.decorations.bottom_right.file' => ['nullable', 'image', 'max:4096'],
            'builder.content.decorations.bottom_right.animation' => ['nullable', 'string', 'max:50'],"""

new_decorations_validation = """            // Cover decorations - 4 slots
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
            'builder.content.cover_background_image.uploaded_url' => ['nullable', 'string', 'max:1000'],"""

if old_decorations_validation in content:
    content = content.replace(old_decorations_validation, new_decorations_validation)
else:
    print("Change 2 failed to match")

# Change 3
old_character_image = """            'builder.content.character_image.type' => ['nullable', 'string', 'max:100'],
            'builder.content.character_image.url' => ['nullable', 'string', 'max:1000'],
            'builder.content.character_image.uploaded_url' => ['nullable', 'string', 'max:1000'],
            'builder.content.character_image.file' => ['nullable', 'image', 'max:4096'],"""

new_character_image = """            'builder.content.character_image.type' => ['nullable', 'string', 'max:100'],
            'builder.content.character_image.url' => ['nullable', 'string', 'max:1000'],
            'builder.content.character_image.uploaded_url' => ['nullable', 'string', 'max:1000'],
            'builder.content.character_image.file' => ['nullable', 'image', 'max:4096'],
            'builder.content.character_image.size' => ['nullable', 'integer', 'min:30', 'max:600'],
            'builder.content.character_image.x' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'builder.content.character_image.y' => ['nullable', 'numeric', 'min:0', 'max:100'],"""

if old_character_image in content:
    content = content.replace(old_character_image, new_character_image)
else:
    print("Change 3 failed to match")

# Change 4
old_tenant_allowed = """        if (data_get($current, 'permissions.custom_decorations')) {
            data_set($allowed, 'content.decorations.top_left.type', data_get($incoming, 'content.decorations.top_left.type'));
            data_set($allowed, 'content.decorations.top_left.url', data_get($incoming, 'content.decorations.top_left.url'));
            data_set($allowed, 'content.decorations.top_left.uploaded_url', data_get($incoming, 'content.decorations.top_left.uploaded_url'));
            data_set($allowed, 'content.decorations.top_left.file', data_get($incoming, 'content.decorations.top_left.file'));
            data_set($allowed, 'content.decorations.bottom_right.type', data_get($incoming, 'content.decorations.bottom_right.type'));
            data_set($allowed, 'content.decorations.bottom_right.url', data_get($incoming, 'content.decorations.bottom_right.url'));
            data_set($allowed, 'content.decorations.bottom_right.uploaded_url', data_get($incoming, 'content.decorations.bottom_right.uploaded_url'));
            data_set($allowed, 'content.decorations.bottom_right.file', data_get($incoming, 'content.decorations.bottom_right.file'));
        }

        if (data_get($current, 'permissions.decoration_animation')) {
            data_set($allowed, 'content.decorations.top_left.animation', data_get($incoming, 'content.decorations.top_left.animation'));
            data_set($allowed, 'content.decorations.bottom_right.animation', data_get($incoming, 'content.decorations.bottom_right.animation'));
        }

        if (data_get($current, 'permissions.character_image')) {
            data_set($allowed, 'content.character_image.type', data_get($incoming, 'content.character_image.type'));
            data_set($allowed, 'content.character_image.url', data_get($incoming, 'content.character_image.url'));
            data_set($allowed, 'content.character_image.uploaded_url', data_get($incoming, 'content.character_image.uploaded_url'));
            data_set($allowed, 'content.character_image.file', data_get($incoming, 'content.character_image.file'));
        }

        if (data_get($current, 'permissions.background_image')) {
            data_set($allowed, 'content.background_image.type', data_get($incoming, 'content.background_image.type'));
            data_set($allowed, 'content.background_image.url', data_get($incoming, 'content.background_image.url'));
            data_set($allowed, 'content.background_image.uploaded_url', data_get($incoming, 'content.background_image.uploaded_url'));
            data_set($allowed, 'content.background_image.file', data_get($incoming, 'content.background_image.file'));
        }"""

new_tenant_allowed = """        if (data_get($current, 'permissions.custom_decorations')) {
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
        }"""

if old_tenant_allowed in content:
    content = content.replace(old_tenant_allowed, new_tenant_allowed)
else:
    print("Change 4 failed to match")

# Change 5.1
old_apply_uploaded_1 = """        if ($request->hasFile('builder.content.decorations.top_left.file')) {
            $path = $request->file('builder.content.decorations.top_left.file')->store("{$basePath}/decorations", 'public');
            data_set($incoming, 'content.decorations.top_left.uploaded_url', Storage::disk('public')->url($path));
        }

        if ($request->hasFile('builder.content.decorations.bottom_right.file')) {
            $path = $request->file('builder.content.decorations.bottom_right.file')->store("{$basePath}/decorations", 'public');
            data_set($incoming, 'content.decorations.bottom_right.uploaded_url', Storage::disk('public')->url($path));
        }"""

new_apply_uploaded_1 = """        if ($request->hasFile('builder.content.cover_background_image.file')) {
            $path = $request->file('builder.content.cover_background_image.file')->store("{$basePath}/backgrounds", 'public');
            data_set($incoming, 'content.cover_background_image.uploaded_url', Storage::disk('public')->url($path));
        }"""

if old_apply_uploaded_1 in content:
    content = content.replace(old_apply_uploaded_1, new_apply_uploaded_1)
else:
    print("Change 5.1 failed to match")

# Change 5.2
old_apply_uploaded_2 = """        data_forget($incoming, 'content.decorations.top_left.file');
        data_forget($incoming, 'content.decorations.bottom_right.file');"""

new_apply_uploaded_2 = """        data_forget($incoming, 'content.cover_background_image.file');"""

if old_apply_uploaded_2 in content:
    content = content.replace(old_apply_uploaded_2, new_apply_uploaded_2)
else:
    print("Change 5.2 failed to match")

with open(file_path, "w") as f:
    f.write(content)

print("Done patching.")
