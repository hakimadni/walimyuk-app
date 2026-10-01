# WalimYuk Feature Expansion Plan: Analytics & QR Check-in

> **For Hermes:** Use subagent-driven-development skill to implement this plan task-by-task.

**Goal:** Implement high-value features for WalimYuk:
1. **Analytics Dashboard:** Visual representation of RSVP conversion and guest statistics.
2. **QR Code Check-in:** Generate QR codes for confirmed guests and build a scanner interface for usher/reception use on the wedding day.
3. **Music Library Manager:** Admin-managed music library that Pro users can select from (Custom domain routing skipped for now).

**Architecture:** 
- **Analytics:** Controller methods computing aggregates from the `rsvps` and `guests` tables, displayed via charts (e.g., Chart.js/Vue-Chartjs) in a new Dashboard tab.
- **QR Check-in:** Generate QR codes (using a package like `simplesoftwareio/simple-qrcode`) embedded in the public invitation or a separate "Ticket" route. Create a new `Receptionist` Vue page that uses a JS QR scanner library (e.g., `html5-qrcode`) to hit a check-in API endpoint. Add an `is_attended` boolean to the `guests` table.
- **Music Library Manager (Pro Only):** A global library of background music managed by admins. Pro users can select from this library instead of manually uploading MP3 files.

**Tech Stack:** Laravel, Vue 3, Inertia, Tailwind CSS, simple-qrcode, html5-qrcode, chart.js.

---

### Task 1: Database Updates for QR Check-in

**Objective:** Add attendance tracking to the guests table.

**Files:**
- Create: `database/migrations/2026_10_01_000002_add_attendance_to_guests_table.php`
- Modify: `app/Models/Guest.php`

**Step 1: Write Migration**
```php
Schema::table('guests', function (Blueprint $table) {
    $table->boolean('is_attended')->default(false)->after('is_invitation_sent');
    $table->timestamp('attended_at')->nullable()->after('is_attended');
    // Optional: UUID/Hash for the QR payload to prevent guessing IDs
    $table->string('qr_code_hash')->nullable()->unique()->after('token');
});
```

**Step 2: Update Model**
Add `is_attended`, `attended_at`, and `qr_code_hash` to `$fillable`. Cast `is_attended` to boolean and `attended_at` to datetime.
In the `booted` method or creation observer, automatically generate a unique `qr_code_hash` for new guests (or generate them lazily).

---

### Task 2: Analytics Backend (Dashboard Data)

**Objective:** Create an API endpoint or update the `WeddingController` to provide aggregate data for charts.

**Files:**
- Modify: `app/Http/Controllers/Dashboard/WeddingController.php` (or a dedicated `AnalyticsController`)

**Step 1: Aggregation Logic**
Create a method that returns:
- Total invited guests (`sum(max_pax)`)
- Total RSVP confirmed (`sum(pax_count)` where `is_attending = true`)
- Total RSVP declined
- Total pending RSVP
- Total actually attended (based on `is_attended`)

Pass this data as props to the Inertia view (e.g., `Admin/Weddings/Show.vue` or a new `Analytics.vue`).

---

### Task 3: Analytics UI (Charts)

**Objective:** Display the RSVP and attendance data visually in the dashboard.

**Files:**
- Modify: `resources/js/Pages/Admin/Weddings/Show.vue`
- Create (optional): `resources/js/Components/Charts/RsvpChart.vue`

**Step 1: Implement UI**
Install `chart.js` and `vue-chartjs` if not present.
Build a Doughnut chart showing RSVP status (Attending, Declined, Pending).
Build a Stat card summarizing total expected pax vs actual attended pax.

---

### Task 4: QR Code Generation & Display

**Objective:** Show a QR code to the guest on their invitation if they have confirmed attendance.

**Files:**
- Install Package: `composer require simplesoftwareio/simple-qrcode`
- Modify: `app/Http/Controllers/PublicInvitationController.php` (or wherever the invitation is rendered)
- Modify: `resources/js/Pages/Public/InvitationShell.vue` (or a specific section like RsvpSection)

**Step 1: Backend**
Pass the `qr_code_hash` to the frontend. Ensure it's only available if RSVP is confirmed. (Alternatively, generate the SVG on the backend and pass it as a string).

**Step 2: Frontend**
Display the QR code image in the Vue template after they successfully RSVP. Add instructions like "Tunjukkan QR ini kepada penerima tamu."

---

### Task 5: Scanner Interface for Ushers

**Objective:** Build a page where receptionists can scan the QR codes.

**Files:**
- Create: `resources/js/Pages/Public/Scanner.vue`
- Modify: `routes/web.php`
- Create: `app/Http/Controllers/ScannerController.php`

**Step 1: Backend Routes**
- `GET /w/{wedding}/scanner` - Returns the Scanner Vue page (Requires a specific usher pin/password or just owner auth).
- `POST /api/check-in` - Receives the `qr_code_hash`, verifies it belongs to the wedding, marks `is_attended = true` and `attended_at = now()`, and returns the guest's name, table/group (if any), and pax count.

**Step 2: Frontend Implementation**
- Install `html5-qrcode` (or similar scanner library).
- Build the `Scanner.vue` component that accesses the device camera.
- On successful scan, hit the `POST /api/check-in` endpoint.
- Show a success toast/modal with the guest's details (e.g., "Selamat Datang, Bpk Budi (2 Pax)").

---

### Task 6: Background Music Library Manager (Admin & Tenant)

**Objective:** Let admins upload reusable background music, and let Pro users select from them in the Builder.

**Files:**
- Create: `app/Models/BackgroundMusic.php`
- Create: `database/migrations/2026_10_01_000003_create_background_music_table.php`
- Create: `app/Http/Controllers/Dashboard/BackgroundMusicController.php`
- Modify: `resources/js/Pages/Admin/Weddings/Builder.vue`
- Create: `resources/js/Pages/Admin/BackgroundMusic/Index.vue`

**Step 1: Backend**
Create table with `title`, `artist`, `file_path`.
`BackgroundMusicController` allows admins to upload MP3s.
Add API endpoint to fetch list of available music.

**Step 2: Frontend (Builder)**
In `Builder.vue`, under the Music section, add a tab: "Upload Sendiri" vs "Pilih dari Library".
If user is not Pro, they can only use default or must upgrade to access the Library. 
If Pro, they can select a track and it updates `form.builder.content.music_url` with the library file's URL.
