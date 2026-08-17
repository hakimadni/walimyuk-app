<?php

namespace App\Services;

use App\Models\Guest;
use App\Models\Rsvp;
use App\Models\Wedding;
use App\Models\Wish;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class RsvpService
{
    /**
     * Submit or update an RSVP for a guest.
     *
     * Business rules:
     * - attendance_status is required (validated at FormRequest level)
     * - If attending: pax_count >= 1 AND <= guest.max_pax
     * - If not_attending: force pax_count = 0
     * - Upserts by guest_id (one active RSVP per guest)
     * - Records submitted_at, ip_address, user_agent
     * - If comment is provided, creates/updates a Wish
     *
     * @param  Guest  $guest  The guest submitting the RSVP (resolved via token middleware)
     * @param  array  $data   Validated request data (attendance_status, pax_count, comment)
     * @return Rsvp
     *
     * @throws RuntimeException When pax validation fails
     */
    public function submitRsvp(Guest $guest, array $data): Rsvp
    {
        $attendanceStatus = $data['attendance_status'];

        // Determine pax_count based on attendance status
        if ($attendanceStatus === 'not_attending' || $attendanceStatus === 'maybe') {
            $paxCount = 0;
        } else {
            // attending
            $paxCount = (int) ($data['pax_count'] ?? 0);

            if ($paxCount < 1) {
                throw new RuntimeException('Jumlah tamu minimal 1 orang.');
            }

            if ($paxCount > $guest->max_pax) {
                throw new RuntimeException(
                    "Jumlah tamu maksimal {$guest->max_pax} orang."
                );
            }
        }

        return DB::transaction(function () use ($guest, $data, $attendanceStatus, $paxCount) {
            // Upsert: update existing RSVP by guest_id, or create new
            $rsvp = Rsvp::updateOrCreate(
                ['guest_id' => $guest->id],
                [
                    'wedding_id'          => $guest->wedding_id,
                    'attendance_status'   => $attendanceStatus,
                    'pax_count'           => $paxCount,
                    'guest_name_confirmed' => $guest->name,
                    'phone_number'        => $guest->phone_number,
                    'comment'             => $data['comment'] ?? null,
                    'submitted_at'        => now(),
                    'ip_address'          => request()->ip(),
                    'user_agent'          => request()->userAgent(),
                ]
            );

            // If comment is provided, create or update a Wish from this guest
            $comment = $data['comment'] ?? null;
            if ($comment && trim($comment) !== '') {
                Wish::updateOrCreate(
                    [
                        'wedding_id' => $guest->wedding_id,
                        'guest_id'   => $guest->id,
                    ],
                    [
                        'name'              => $guest->name,
                        'message'           => trim($comment),
                        'moderation_status' => 'approved', // Auto-approve RSVP-attached wishes
                    ]
                );
            }

            return $rsvp;
        });
    }

    /**
     * Calculate catering estimate for a wedding.
     *
     * Returns aggregated RSVP metrics and a recommended catering pax count
     * that includes a buffer percentage for safety.
     *
     * @param  Wedding  $wedding
     * @return array{
     *     invited_guest_count: int,
     *     total_invitation_quota: int,
     *     rsvp_submitted_count: int,
     *     pending_rsvp_count: int,
     *     attending_guest_count: int,
     *     declined_guest_count: int,
     *     maybe_guest_count: int,
     *     confirmed_pax: int,
     *     buffer_percentage: int,
     *     recommended_catering_pax: int,
     * }
     */
    public function calculateCateringEstimate(Wedding $wedding): array
    {
        $weddingId = $wedding->id;

        // Count all invited guests (including soft-deleted excluded by default scope)
        $invitedGuestCount = Guest::where('wedding_id', $weddingId)->count();

        // Sum of max_pax across all guests = total invitation capacity
        $totalInvitationQuota = (int) Guest::where('wedding_id', $weddingId)
            ->sum('max_pax');

        // Count RSVPs that have been submitted
        $rsvpSubmittedCount = Rsvp::where('wedding_id', $weddingId)->count();

        // Pending = invited but hasn't submitted RSVP yet
        $pendingRsvpCount = $invitedGuestCount - $rsvpSubmittedCount;

        // Breakdown by attendance status
        $attendingGuestCount = Rsvp::where('wedding_id', $weddingId)
            ->where('attendance_status', 'attending')
            ->count();

        $declinedGuestCount = Rsvp::where('wedding_id', $weddingId)
            ->where('attendance_status', 'not_attending')
            ->count();

        $maybeGuestCount = Rsvp::where('wedding_id', $weddingId)
            ->where('attendance_status', 'maybe')
            ->count();

        // Sum of pax_count for attending guests only
        $confirmedPax = (int) Rsvp::where('wedding_id', $weddingId)
            ->where('attendance_status', 'attending')
            ->sum('pax_count');

        // Buffer percentage from wedding config (default 10%)
        $bufferPercentage = (int) ($wedding->pax_buffer_percentage ?? 10);

        // Recommended catering pax = ceil(confirmed_pax * (1 + buffer% / 100))
        $recommendedCateringPax = (int) ceil($confirmedPax * (1 + $bufferPercentage / 100));

        return [
            'invited_guest_count'   => $invitedGuestCount,
            'total_invitation_quota' => $totalInvitationQuota,
            'rsvp_submitted_count'  => $rsvpSubmittedCount,
            'pending_rsvp_count'    => max(0, $pendingRsvpCount),
            'attending_guest_count' => $attendingGuestCount,
            'declined_guest_count'  => $declinedGuestCount,
            'maybe_guest_count'     => $maybeGuestCount,
            'confirmed_pax'         => $confirmedPax,
            'buffer_percentage'     => $bufferPercentage,
            'recommended_catering_pax' => $recommendedCateringPax,
        ];
    }
}
