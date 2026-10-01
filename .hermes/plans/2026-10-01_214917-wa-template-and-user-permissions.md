# WA Template Builder & User Management Permissions Plan

> **For Hermes:** Use subagent-driven-development skill to implement this plan task-by-task.

**Goal:** 
1. Implement a WhatsApp message template builder (20 global seeds). Non-pro users can create up to 5 custom templates; Pro users get unlimited.
2. Remove the "Permission Matrix" from the Wedding Customization page (Builder.vue) and move it into the Admin User Management page so features are controlled per-user by admins.

**Architecture:** 
- New `MessageTemplate` model for WA templates (global if `user_id=null`, tenant-specific if set).
- Move builder permissions from `weddings.theme_config` to a new `users.builder_permissions` JSON column.

**Tech Stack:** Laravel, Vue 3, Inertia, Tailwind CSS.

---

### Task 1: Create MessageTemplate Migration & Model

**Objective:** Scaffold the table for WhatsApp message templates.

**Files:**
- Create: `database/migrations/2026_10_01_000000_create_message_templates_table.php` (use current date in actual implementation)
- Create: `app/Models/MessageTemplate.php`

**Step 1: Write Migration**
```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('message_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('content');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('message_templates');
    }
};
```

**Step 2: Write Model**
```php
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MessageTemplate extends Model
{
    protected $fillable = ['user_id', 'name', 'content'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
```

---

### Task 2: Create MessageTemplateSeeder (20 Variations)

**Objective:** Seed 20 global message templates with placeholders like `[NAMA_TAMU]` and `[LINK_UNDANGAN]`.

**Files:**
- Create: `database/seeders/MessageTemplateSeeder.php`
- Modify: `database/seeders/DatabaseSeeder.php` (to call it)

**Step 1: Write Seeder**
```php
<?php
namespace Database\Seeders;

use App\Models\MessageTemplate;
use Illuminate\Database\Seeder;

class MessageTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            ['name' => '1. Formal Islam (Standar)', 'content' => "Assalamu'alaikum Wr. Wb.\nBismillahirahmanirrahim.\nYth. Bpk/Ibu/Sdr/i [NAMA_TAMU],\nTanpa mengurangi rasa hormat, perkenankan kami mengundang Anda ke acara pernikahan kami.\n\nDetail acara dapat dilihat pada tautan berikut:\n[LINK_UNDANGAN]\n\nKehadiran dan doa restu Anda adalah kebahagiaan bagi kami.\nWassalamu'alaikum Wr. Wb."],
            ['name' => '2. Formal Nasional', 'content' => "Dengan hormat,\nYth. Bpk/Ibu/Sdr/i [NAMA_TAMU],\nBersama pesan ini, kami bermaksud mengundang Anda untuk hadir pada hari bahagia pernikahan kami.\n\nInformasi lengkap mengenai acara:\n[LINK_UNDANGAN]\n\nMerupakan suatu kehormatan bagi kami apabila Bapak/Ibu/Saudara/i berkenan hadir. Terima kasih."],
            ['name' => '3. Casual Sahabat', 'content' => "Halo [NAMA_TAMU]! 👋\nAkhirnya hari yang ditunggu tiba juga. Aku mau ngundang kamu buat datang ke acara pernikahanku!\n\nBisa cek info lengkapnya di link ini ya:\n[LINK_UNDANGAN]\n\nDitunggu banget kedatangannya, jangan sampai nggak datang ya! 🥳"],
            ['name' => '4. Bahasa Inggris (Formal)', 'content' => "Dear [NAMA_TAMU],\nWe are overjoyed to invite you to celebrate our wedding day.\n\nPlease find the details of our special day here:\n[LINK_UNDANGAN]\n\nYour presence will make our day truly complete. We look forward to seeing you!"],
            ['name' => '5. Singkat & Padat', 'content' => "Halo [NAMA_TAMU],\nMohon doa dan kehadirannya di acara pernikahan kami.\n\nUndangan lengkap: [LINK_UNDANGAN]\n\nTerima kasih!\n\n*Catatan: Undangan ini bersifat privat, mohon untuk tidak disebarluaskan."],
            // (Add 15 more variations to reach 20 in implementation: Pantun, Kristen, Katolik, Hindu, Bahasa Jawa Krama, Bahasa Sunda, Fun/Humoris, Aesthetic, Puitis, Modern, Keluarga Jauh, Rekan Kerja, dsb. Ensure EVERY template ends with the privacy note: "*Catatan: Undangan ini bersifat privat, mohon untuk tidak disebarluaskan.")
        ];

        foreach ($templates as $t) {
            MessageTemplate::create(array_merge($t, ['user_id' => null]));
        }
    }
}
```

---

### Task 3: API & Controller for Message Templates

**Objective:** Allow users to list, create, and delete their templates, enforcing the 5-slot limit for non-pro users.

**Files:**
- Create: `app/Http/Controllers/Dashboard/MessageTemplateController.php`
- Modify: `routes/web.php`

**Step 1: Write Controller**
```php
<?php
namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\MessageTemplate;
use Illuminate\Http\Request;

class MessageTemplateController extends Controller
{
    public function index(Request $request)
    {
        $global = MessageTemplate::whereNull('user_id')->get();
        $userTemplates = MessageTemplate::where('user_id', $request->user()->id)->get();
        
        return response()->json([
            'global' => $global,
            'custom' => $userTemplates,
            'can_create' => $request->user()->is_premium || $userTemplates->count() < 5
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user->is_premium && $user->messageTemplates()->count() >= 5) {
            abort(403, 'Batas maksimal 5 template khusus. Upgrade ke Pro untuk slot tak terbatas.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'content' => 'required|string'
        ]);

        $template = $user->messageTemplates()->create($validated);
        return response()->json($template);
    }
    
    public function destroy(Request $request, MessageTemplate $messageTemplate)
    {
        if ($messageTemplate->user_id !== $request->user()->id) abort(403);
        $messageTemplate->delete();
        return response()->json(['success' => true]);
    }
}
```

**Step 2: Add Route in `routes/web.php`**
```php
Route::middleware(['auth', 'verified'])->group(function () {
    Route::apiResource('message-templates', \App\Http\Controllers\Dashboard\MessageTemplateController::class)->only(['index', 'store', 'destroy']);
});
```

---

### Task 4: Move Builder Permissions to Users Table

**Objective:** Move `builder_permissions` to the User model so admins can toggle them per user.

**Files:**
- Create: `database/migrations/2026_10_01_000001_add_builder_permissions_to_users_table.php`
- Modify: `app/Models/User.php`

**Step 1: Write Migration**
```php
Schema::table('users', function (Blueprint $table) {
    $table->json('builder_permissions')->nullable()->after('is_premium');
});
```

**Step 2: Update User Model**
Add to `$fillable`: `'builder_permissions'`
Add to `casts()`: `'builder_permissions' => 'array'`

---

### Task 5: Update WeddingController Builder Logic

**Objective:** Pull builder permissions from the User model instead of `theme_config`, and stop saving them there.

**Files:**
- Modify: `app/Http/Controllers/Dashboard/WeddingController.php`

**Step 1: Edit `resolveBuilderConfig`**
```php
private function resolveBuilderConfig(Wedding $wedding): array
{
    $config = array_replace_recursive(
        self::BUILDER_DEFAULTS,
        $wedding->theme_config['builder'] ?? []
    );

    // Override permissions from user settings
    $userPermissions = $wedding->user->builder_permissions ?? self::BUILDER_DEFAULTS['permissions'];
    $config['permissions'] = $userPermissions;

    return $config;
}
```

**Step 2: Edit `tenantAllowedBuilderPayload`**
Remove any code that writes `permissions` back into the allowed payload. The tenant cannot save permissions anymore.

---

### Task 6: UI - Move Matrix from Builder to User Edit

**Objective:** Remove the matrix from the Wedding Builder and place it in User Management.

**Files:**
- Modify: `resources/js/Pages/Admin/Weddings/Builder.vue`
- Modify: `resources/js/Pages/Admin/Users/Edit.vue`

**Step 1: Remove from `Builder.vue`**
- Delete the entire `<div ...> Permission Matrix (Guardrails) ... </div>` block.
- Remove `canEditPermissionMatrix` prop if no longer used.

**Step 2: Add to `Users/Edit.vue`**
- Add `builder_permissions` to the `$inertia.useForm` payload.
- Create checkboxes in `Edit.vue` (Admin only) to toggle `custom_decorations`, `music`, `palette`, etc., directly modifying `form.builder_permissions`.

---

### Task 7: UI - Add Template Selection in Guest Index

**Objective:** Let users select and manage a template when copying a guest link.

**Files:**
- Modify: `resources/js/Pages/Admin/Guests/Index.vue`

**Step 1:**
- Add a "Kirim Pesan" / "Copy Pesan WA" button to the guest row.
- When clicked, open a Modal:
  - Show a `<select>` or List of Templates fetched from `/message-templates` API.
  - Preview the final text (replace `[NAMA_TAMU]` and `[LINK_UNDANGAN]`).
  - Button "Copy Text" and button "Kirim ke WA".
- Add a "Kelola Template" tab/modal for users to create new ones (up to 5 if non-pro).
