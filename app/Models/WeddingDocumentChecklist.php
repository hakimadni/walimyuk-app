<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeddingDocumentChecklist extends Model
{
    use HasFactory;

    protected $fillable = [
        'wedding_id',
        'stage_key',
        'stage_title',
        'document_name',
        'notes',
        'is_groom_checked',
        'is_bride_checked',
        'groom_checked_at',
        'bride_checked_at',
        'sort_order',
    ];

    protected $casts = [
        'is_groom_checked' => 'boolean',
        'is_bride_checked' => 'boolean',
        'groom_checked_at' => 'datetime',
        'bride_checked_at' => 'datetime',
        'sort_order' => 'integer',
    ];

    public function wedding(): BelongsTo
    {
        return $this->belongsTo(Wedding::class);
    }

    /**
     * Default standard checklist requirements.
     */
    public static function defaultTemplate(): array
    {
        return [
            // 1. RT/RW
            [
                'stage_key' => 'rt_rw',
                'stage_title' => '1. RT/RW (Persyaratan yang Dibawa)',
                'documents' => [
                    'Fotocopy KK',
                    'Fotocopy KTP',
                ],
            ],

            // 2. PUSKESMAS
            [
                'stage_key' => 'puskesmas',
                'stage_title' => '2. PUSKESMAS (Pemeriksaan Kesehatan & Sertifikat Layak Nikah)',
                'documents' => [
                    'Surat Pengantar dari RT & RW',
                    'Fotocopy KTP',
                    'Fotocopy KK',
                ],
            ],

            // 3. KELURAHAN (SCAN DOKSLI) upload di jakevo
            [
                'stage_key' => 'kelurahan',
                'stage_title' => '3. KELURAHAN (SCAN DOKSLI - Upload di Jakevo)',
                'documents' => [
                    'Surat Pengantar dari RT & RW',
                    'Fotocopy KK',
                    'Fotocopy KTP',
                    'Fotocopy Ijazah & Akte kelahiran',
                    'Fotocopy KTP Orang Tua',
                    'Fotocopy 2 KTP Saksi',
                    'Fotocopy Sertifikat Layak Nikah Puskesmas',
                    'Surat Pernyataan Belum Menikah Bermaterai',
                ],
            ],

            // 4. KUA DOMISILI ASAL (UNTUK DAPAT SURAT REKOMENDASI NIKAH)
            [
                'stage_key' => 'kua_rekomendasi',
                'stage_title' => '4. KUA Domisili Asal (Surat Rekomendasi Nikah / Numpang Nikah)',
                'documents' => [
                    'Surat Pengantar Kelurahan N1-N4',
                    'Fotocopy Surat Pengantar Kelurahan N1-N4',
                    'Fotocopy KK',
                    'Fotocopy KTP',
                    'Fotocopy KTP Orang tua',
                    'Fotocopy Ijazah Terakhir',
                    'Fotocopy Akte Kelahiran',
                    'Fotocopy Sertifikat Layak Nikah Puskesmas',
                    'Fotocopy 2 KTP Saksi',
                    'Fotocopy Buku Nikah Ortu CPW',
                    'Foto (2x3= 4 lbr) (4x6= 2 lbr) Background Biru',
                    'Surat Pernyataan Belum Menikah Bermaterai',
                ],
            ],

            // 5. KUA VENUE (DAFTAR ONLINE SIMKAH DULU, BARU BAWA BERKAS ASLI KE KUA Venue)
            [
                'stage_key' => 'kua_venue',
                'stage_title' => '5. KUA Venue Pernikahan (Daftar Online SIMKAH Dulu, Baru Bawa Berkas Asli)',
                'documents' => [
                    'Surat Rekomendasi dari Domisili',
                    'Surat Pengantar Kelurahan N1-N4',
                    'Surat Rekomendasi dari KUA Domisili',
                    'Fotocopy KK',
                    'Fotocopy KTP',
                    'Fotocopy KTP Orang tua',
                    'Fotocopy Ijazah Terakhir',
                    'Fotocopy Akte Kelahiran',
                    'Fotocopy Sertifikat Layak Nikah Puskesmas',
                    'Fotocopy 2 KTP Saksi',
                    'Foto (2x3= 4 lbr) (4x6= 2 lbr) Background Biru',
                    'Surat Pernyataan Belum Menikah Bermaterai',
                ],
            ],
        ];
    }
}
