<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Wedding;
use App\Models\Event;
use App\Models\CoupleProfile;
use App\Models\WeddingVerse;
use App\Models\GiftBankAccount;
use App\Models\GiftAddress;
use App\Models\Guest;
use App\Models\Rsvp;
use App\Models\Wish;

class WalimYukSeeder extends Seeder
{
    public function run(): void
    {
        // 1a. Superadmin user
        $admin = User::create([
            'name'     => 'Admin WalimYuk',
            'email'    => 'admin@walimyuk.test',
            'password' => Hash::make('password'),
        ]);
        $admin->role = 'super_admin';
        $admin->save();

        // 1b. Tenant user
        $tenant = User::create([
            'name'     => 'Fulan',
            'email'    => 'fulan@test.com',
            'password' => Hash::make('password'),
        ]);
        $tenant->role = 'tenant';
        $tenant->save();

        // 1c. Wedding for Fulan
        // Note: groom_name / bride_name are NOT in the weddings table;
        // that info lives in couple_profiles instead.
        $wedding = Wedding::create([
            'user_id'               => $tenant->id,
            'slug'                  => 'fulan-fulanah',
            'status'                => 'published',
            'wedding_date'          => '2026-12-20 08:00:00',
            'timezone'              => 'Asia/Jakarta',
            'cover_title'           => 'The Wedding of Fulan & Fulanah',
            'cover_subtitle'        => 'Ahmad Fulan & Fatimah Fulanah',
            'pax_buffer_percentage' => 10,
            'rsvp_required'         => true,
        ]);

        // 1d. Two Events
        // Schema: title, date, start_time, end_time, venue_name, address, google_maps_url
        Event::create([
            'wedding_id'     => $wedding->id,
            'title'          => 'Akad Nikah',
            'date'           => '2026-12-20',
            'start_time'     => '08:00:00',
            'end_time'       => '10:00:00',
            'venue_name'     => 'Masjid Al-Ikhlas',
            'address'        => 'Jl. Masjid Raya No. 1, Jakarta',
            'google_maps_url' => 'https://maps.google.com/?q=-6.2088,106.8456',
            'sort_order'     => 1,
        ]);

        Event::create([
            'wedding_id'     => $wedding->id,
            'title'          => 'Resepsi',
            'date'           => '2026-12-20',
            'start_time'     => '11:00:00',
            'end_time'       => '14:00:00',
            'venue_name'     => 'Gedung Serbaguna Al-Ikhlas',
            'address'        => 'Jl. Masjid Raya No. 2, Jakarta',
            'google_maps_url' => 'https://maps.google.com/?q=-6.2090,106.8460',
            'sort_order'     => 2,
        ]);

        // 1e. Two CoupleProfiles
        // Schema: role (not type), full_name, father_name, mother_name, child_order_text
        CoupleProfile::create([
            'wedding_id'      => $wedding->id,
            'role'            => 'groom',
            'full_name'       => 'Ahmad Fulan',
            'father_name'     => 'Bakhtiar',
            'mother_name'     => 'Khadijah',
            'child_order_text' => 'Putra pertama dari',
            'sort_order'      => 1,
        ]);

        CoupleProfile::create([
            'wedding_id'      => $wedding->id,
            'role'            => 'bride',
            'full_name'       => 'Fatimah Fulanah',
            'father_name'     => 'Abdullah',
            'mother_name'     => 'Aminah',
            'child_order_text' => 'Putri kedua dari',
            'sort_order'      => 2,
        ]);

        // 1f. One WeddingVerse
        // Schema: source_label, arabic_text, translation
        WeddingVerse::create([
            'wedding_id'  => $wedding->id,
            'source_label' => 'QS Ar-Rum: 21',
            'arabic_text'  => 'وَمِنْ آيَاتِهِ أَنْ خَلَقَ لَكُم مِّنْ أَنفُسِكُمْ أَزْوَاجًا لِّتَسْكُنُوا إِلَيْهَا وَجَعَلَ بَيْنَكُم مَّوَدَّةً وَرَحْمَةً ۚ إِنَّ فِي ذَٰلِكَ لَآيَاتٍ لِّقَوْمٍ يَتَفَكَّرُونَ',
            'translation'  => 'Dan di antara tanda-tanda (kebesaran)-Nya ialah Dia menciptakan pasangan-pasangan untukmu dari jenismu sendiri, agar kamu cenderung dan merasa tenteram kepadanya, dan Dia menjadikan di antaramu rasa kasih dan sayang. Sungguh, pada yang demikian itu benar-benar terdapat tanda-tanda (kebesaran Allah) bagi kaum yang berpikir.',
        ]);

        // 1g. One GiftBankAccount
        // Schema: bank_name, account_number, account_holder
        GiftBankAccount::create([
            'wedding_id'     => $wedding->id,
            'bank_name'      => 'BCA',
            'account_number' => '1234567890',
            'account_holder' => 'Ahmad Fulan',
        ]);

        // 1h. One GiftAddress
        // Schema: recipient_name, phone_number, address, notes
        GiftAddress::create([
            'wedding_id'     => $wedding->id,
            'recipient_name' => 'Fulan',
            'address'        => 'Jl. Contoh No. 1, Jakarta',
            'phone_number'   => '081234567890',
        ]);

        // 1i. 10 Guests with varying max_pax and groups
        // Schema: name, group_name, max_pax, token, is_invitation_sent
        $guestData = [
            ['name' => 'Budi Santoso',    'group_name' => 'Family',   'max_pax' => 4, 'is_invitation_sent' => true],
            ['name' => 'Ani Rahayu',       'group_name' => 'Family',   'max_pax' => 4, 'is_invitation_sent' => true],
            ['name' => 'Citra Dewi',       'group_name' => 'Family',   'max_pax' => 2, 'is_invitation_sent' => true],
            ['name' => 'Doni Prasetyo',    'group_name' => 'Office',   'max_pax' => 1, 'is_invitation_sent' => true],
            ['name' => 'Eka Wijaya',       'group_name' => 'Office',   'max_pax' => 2, 'is_invitation_sent' => true],
            ['name' => 'Fikri Hidayat',    'group_name' => 'VIP',      'max_pax' => 2, 'is_invitation_sent' => true],
            ['name' => 'Gita Puspita',     'group_name' => 'VIP',      'max_pax' => 2, 'is_invitation_sent' => true],
            ['name' => 'Hadi Nugroho',     'group_name' => 'College',  'max_pax' => 1, 'is_invitation_sent' => true],
            ['name' => 'Iwan Setiawan',    'group_name' => 'College',  'max_pax' => 1, 'is_invitation_sent' => false],
            ['name' => 'Joko Susanto',     'group_name' => 'College',  'max_pax' => 2, 'is_invitation_sent' => false],
        ];

        $guests = collect();
        foreach ($guestData as $data) {
            $guests->push(Guest::create(array_merge($data, [
                'wedding_id' => $wedding->id,
                'token'      => Str::random(64),
            ])));
        }

        // 1j. RSVPs
        // Schema: wedding_id, guest_id, attendance_status, pax_count
        // 3 attending
        Rsvp::create([
            'wedding_id'       => $wedding->id,
            'guest_id'         => $guests[0]->id,
            'attendance_status' => 'attending',
            'pax_count'        => 3,
            'submitted_at'     => now(),
        ]);
        Rsvp::create([
            'wedding_id'       => $wedding->id,
            'guest_id'         => $guests[1]->id,
            'attendance_status' => 'attending',
            'pax_count'        => 4,
            'submitted_at'     => now(),
        ]);
        Rsvp::create([
            'wedding_id'       => $wedding->id,
            'guest_id'         => $guests[3]->id,
            'attendance_status' => 'attending',
            'pax_count'        => 1,
            'submitted_at'     => now(),
        ]);
        // 1 not attending
        Rsvp::create([
            'wedding_id'       => $wedding->id,
            'guest_id'         => $guests[4]->id,
            'attendance_status' => 'not_attending',
            'pax_count'        => 0,
            'submitted_at'     => now(),
        ]);
        // Guest index 5 (Fikri Hidayat) — no RSVP (leaving index 5 out)

        // 1k. 3 Wishes
        // Schema: wedding_id, guest_id, name, message, moderation_status
        Wish::create([
            'wedding_id'       => $wedding->id,
            'guest_id'         => $guests[0]->id,
            'name'             => $guests[0]->name,
            'message'          => 'Selamat menempuh hidup baru, semoga sakinah mawaddah warahmah!',
            'moderation_status' => 'approved',
        ]);
        Wish::create([
            'wedding_id'       => $wedding->id,
            'guest_id'         => $guests[1]->id,
            'name'             => $guests[1]->name,
            'message'          => 'Lancar sampai hari H ya, semoga bahagia selalu!',
            'moderation_status' => 'approved',
        ]);
        Wish::create([
            'wedding_id'       => $wedding->id,
            'guest_id'         => $guests[3]->id,
            'name'             => $guests[3]->name,
            'message'          => 'Congrats bro! Wishing you a lifetime of happiness!',
            'moderation_status' => 'approved',
        ]);
    }
}
