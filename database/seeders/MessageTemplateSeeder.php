<?php
namespace Database\Seeders;

use App\Models\MessageTemplate;
use Illuminate\Database\Seeder;

class MessageTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $privacyNote = "\n\n*Catatan: Undangan ini bersifat privat, mohon untuk tidak disebarluaskan.";
        $templates = [
            ['name' => '1. Formal Islam (Standar)', 'content' => "Assalamu'alaikum Wr. Wb.\nBismillahirahmanirrahim.\nYth. Bpk/Ibu/Sdr/i [NAMA_TAMU],\nTanpa mengurangi rasa hormat, perkenankan kami mengundang Anda ke acara pernikahan kami.\n\nDetail acara dapat dilihat pada tautan berikut:\n[LINK_UNDANGAN]\n\nKehadiran dan doa restu Anda adalah kebahagiaan bagi kami.\nWassalamu'alaikum Wr. Wb."],
            ['name' => '2. Formal Nasional', 'content' => "Dengan hormat,\nYth. Bpk/Ibu/Sdr/i [NAMA_TAMU],\nBersama pesan ini, kami bermaksud mengundang Anda untuk hadir pada hari bahagia pernikahan kami.\n\nInformasi lengkap mengenai acara:\n[LINK_UNDANGAN]\n\nMerupakan suatu kehormatan bagi kami apabila Bapak/Ibu/Saudara/i berkenan hadir. Terima kasih."],
            ['name' => '3. Casual Sahabat', 'content' => "Halo [NAMA_TAMU]! 👋\nAkhirnya hari yang ditunggu tiba juga. Aku mau ngundang kamu buat datang ke acara pernikahanku!\n\nBisa cek info lengkapnya di link ini ya:\n[LINK_UNDANGAN]\n\nDitunggu banget kedatangannya, jangan sampai nggak datang ya! 🥳"],
            ['name' => '4. Bahasa Inggris (Formal)', 'content' => "Dear [NAMA_TAMU],\nWe are overjoyed to invite you to celebrate our wedding day.\n\nPlease find the details of our special day here:\n[LINK_UNDANGAN]\n\nYour presence will make our day truly complete. We look forward to seeing you!"],
            ['name' => '5. Singkat & Padat', 'content' => "Halo [NAMA_TAMU],\nMohon doa dan kehadirannya di acara pernikahan kami.\n\nUndangan lengkap: [LINK_UNDANGAN]\n\nTerima kasih!"],
            ['name' => '6. Pantun', 'content' => "Jalan-jalan ke kota tua\nJangan lupa membeli baju\nHari bahagia sudah tiba\n[NAMA_TAMU] ditunggu kehadirannya di hari pernikahanku!\n\nDetail undangan: [LINK_UNDANGAN]"],
            ['name' => '7. Kristen', 'content' => "Syalom [NAMA_TAMU],\nDalam kasih karunia Tuhan Yesus Kristus, kami mengundang Bapak/Ibu/Saudara/i untuk hadir dalam pemberkatan dan resepsi pernikahan kami.\n\nDetail acara dapat dilihat di sini:\n[LINK_UNDANGAN]\n\nKehadiran Anda adalah sukacita bagi kami. Tuhan memberkati."],
            ['name' => '8. Katolik', 'content' => "Salam Damai dalam Kristus [NAMA_TAMU],\nDengan penuh rasa syukur, kami bermaksud mengundang Bapak/Ibu/Saudara/i untuk menghadiri Sakramen Perkawinan dan resepsi kami.\n\nInformasi undangan:\n[LINK_UNDANGAN]\n\nTerima kasih atas doa dan kehadirannya. Berkah Dalem."],
            ['name' => '9. Hindu', 'content' => "Om Swastyastu,\nYth. Bapak/Ibu/Saudara/i [NAMA_TAMU],\nAtas asung kertha wara nugraha Ida Sang Hyang Widhi Wasa, kami mengundang Anda untuk hadir pada upacara pernikahan kami.\n\nDetail acara:\n[LINK_UNDANGAN]\n\nMerupakan kebahagiaan bagi kami atas kehadiran dan doa restunya. Om Shanti, Shanti, Shanti, Om."],
            ['name' => '10. Bahasa Jawa (Krama Alus)', 'content' => "Assalamu'alaikum Wr. Wb.\nKatur dumateng Bpk/Ibu/Sdr/i [NAMA_TAMU],\nKanthi nyuwun rida Allah SWT, kula nyuwun donga pangestu saha rawuh panjenengan wonten ing adicara pawiwahan kula.\n\nUndangan saged dipun waos wonten ing:\n[LINK_UNDANGAN]\n\nCekap semanten, atur panuwun. Wassalamu'alaikum Wr. Wb."],
            ['name' => '11. Bahasa Sunda', 'content' => "Assalamu'alaikum Wr. Wb.\nKahatur Bpk/Ibu/Sadérék [NAMA_TAMU],\nKalayan asmana Allah SWT, sim kuring seja ngulem Bpk/Ibu/Sadérék kanggo sumping dina acara pikahareupeun sim kuring.\n\nWincikanana tiasa ditingal dina ieu tautan:\n[LINK_UNDANGAN]\n\nKasumpingan Bpk/Ibu/Sadérék mangrupi kabingah kanggo sim kuring. Wassalamu'alaikum Wr. Wb."],
            ['name' => '12. Fun / Humoris', 'content' => "Woy [NAMA_TAMU]! 😎\nDaripada malam minggu sendirian terus, mending lu dateng ke nikahan gue sekalian numpang makan enak!\n\nJangan lupa catet tanggalnya di sini ya:\n[LINK_UNDANGAN]\n\nAwas kalau nggak dateng, tiket masuknya hangus! Hahaha. Ditunggu bro/sis! 🎉"],
            ['name' => '13. Aesthetic / Soft', 'content' => "Dear [NAMA_TAMU] 🌸\nEvery love story is beautiful, but ours is my favorite. We invite you to share our joy as we exchange our vows.\n\nOpen our invitation story here:\n[LINK_UNDANGAN]\n\nCan't wait to celebrate this magical moment with you ✨"],
            ['name' => '14. Puitis', 'content' => "Teruntuk [NAMA_TAMU],\nSeperti dua bait puisi yang akhirnya menemukan rima, begitulah kami melangkah menuju hari bahagia. Kami berharap langkah ini dapat diiringi oleh kehadiran dan doa tulus darimu.\n\nSimak untaian cerita kami di:\n[LINK_UNDANGAN]\n\nKehadiranmu adalah bagian terindah dari puisi pernikahan kami."],
            ['name' => '15. Modern Minimalist', 'content' => "Hi [NAMA_TAMU],\nYou're invited! Join us in celebrating our wedding day.\n\nDetails & RSVP:\n[LINK_UNDANGAN]\n\nCheers! 🥂"],
            ['name' => '16. Keluarga Jauh', 'content' => "Assalamu'alaikum Wr. Wb. / Dengan hormat,\nTeruntuk keluarga tercinta [NAMA_TAMU],\nMeski jarak memisahkan, doa dan restu keluarga besar sangat berarti bagi kami. Kami mengundang Bapak/Ibu sekalian untuk hadir di acara pernikahan kami.\n\nInformasi lebih lanjut:\n[LINK_UNDANGAN]\n\nSemoga kita bisa bersilaturahmi di hari bahagia ini."],
            ['name' => '17. Rekan Kerja', 'content' => "Dengan hormat,\nYth. Rekan [NAMA_TAMU],\nMelalui pesan ini, kami ingin mengundang rekan-rekan sekalian untuk hadir pada acara pernikahan kami.\n\nDetail waktu dan tempat:\n[LINK_UNDANGAN]\n\nKehadiran dan doa dari rekan-rekan sekalian sangat kami nantikan. Terima kasih."],
            ['name' => '18. Nostalgia / Teman Sekolah', 'content' => "Halo [NAMA_TAMU]! 🎓\nUdah lama banget sejak zaman sekolah! Sekarang akhirnya gue mau nikah nih, dan gue berharap lo bisa dateng buat reuni kecil-kecilan di hari bahagia gue.\n\nCek lokasinya di sini ya:\n[LINK_UNDANGAN]\n\nSampai ketemu! Jangan lupa dandan yang cakep! 🤩"],
            ['name' => '19. Bilingual (ID-EN)', 'content' => "Dear [NAMA_TAMU],\nDengan penuh suka cita, kami mengundang Anda / We joyfully invite you to our wedding celebration.\n\nDetail lengkap / Full details:\n[LINK_UNDANGAN]\n\nTerima kasih atas doa dan kehadirannya / Thank you for your blessings and presence."],
            ['name' => '20. Semi-Formal', 'content' => "Halo Bapak/Ibu/Sdr/i [NAMA_TAMU],\nSemoga hari Anda menyenangkan. Kami dengan penuh bahagia ingin membagikan kabar gembira mengenai pernikahan kami dan mengundang Anda untuk hadir.\n\nUntuk detail acara, silakan buka tautan berikut:\n[LINK_UNDANGAN]\n\nTerima kasih dan sampai jumpa!"]
        ];

        foreach ($templates as $t) {
            MessageTemplate::create([
                'name' => $t['name'],
                'content' => $t['content'] . $privacyNote,
                'user_id' => null
            ]);
        }
    }
}
