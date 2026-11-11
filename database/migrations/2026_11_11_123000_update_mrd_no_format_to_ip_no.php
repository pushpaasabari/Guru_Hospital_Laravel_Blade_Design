<?php

use App\Models\Admission;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Backfill empty or legacy `MRD-{UHID}` values to `MRD-{IP No}`.
     */
    public function up(): void
    {
        DB::table('admissions')
            ->select(['id', 'uhid', 'ip_no', 'mrd_no'])
            ->whereNotNull('ip_no')
            ->where('ip_no', '!=', '')
            ->orderBy('id')
            ->chunkById(100, function ($rows) {
                foreach ($rows as $row) {
                    $uhid = trim((string) $row->uhid);
                    $legacyDefault = $uhid !== ''
                        ? (str_starts_with($uhid, 'MRD-') ? $uhid : 'MRD-' . $uhid)
                        : '';

                    $currentMrd = trim((string) $row->mrd_no);
                    if ($currentMrd === '' || ($legacyDefault !== '' && $currentMrd === $legacyDefault)) {
                        DB::table('admissions')
                            ->where('id', $row->id)
                            ->update(['mrd_no' => Admission::defaultMrdNo($row->ip_no)]);
                    }
                }
            });
    }

    public function down(): void
    {
        // Non-destructive data backfill; no rollback needed.
    }
};

