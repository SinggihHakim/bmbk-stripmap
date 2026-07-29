<?php

/**
 * ============================================================
 * SegmentValidator
 * ============================================================
 * Helper statis untuk validasi segmen jalan (stripmap & perkerasan).
 * Mengekstrak logika yang sama agar tidak duplikasi antara
 * StripmapService dan PerkerasanService.
 */
class SegmentValidator
{
    /**
     * Deteksi tumpang tindih (overlap) antar segmen dalam sebuah batch.
     *
     * Segmen dalam $cleanRows harus sudah diurutkan berdasarkan sta_awal secara ascending.
     * Setiap elemen harus memiliki key:
     *   - 'sta_awal'      (float) stasiun awal dalam meter
     *   - 'sta_akhir'     (float) stasiun akhir dalam meter
     *   - 'original_index'(int)   nomor baris asli untuk pesan error
     *   - 'sta_awal_str'  (string) representasi STA awal asli
     *   - 'sta_akhir_str' (string) representasi STA akhir asli
     *
     * @param array $cleanRows Array segmen yang sudah divalidasi per-baris dan sudah disort
     * @return string[] Array pesan error (kosong jika tidak ada overlap)
     */
    public static function detectOverlaps(array $cleanRows): array
    {
        $errors = [];

        for ($i = 1, $count = count($cleanRows); $i < $count; $i++) {
            $prev = $cleanRows[$i - 1];
            $curr = $cleanRows[$i];

            if ($curr['sta_awal'] < $prev['sta_akhir']) {
                $errors[] = sprintf(
                    'Tumpang tindih terdeteksi antara Baris %d (%s s/d %s) dan Baris %d (%s s/d %s).',
                    $prev['original_index'],
                    $prev['sta_awal_str'],
                    $prev['sta_akhir_str'],
                    $curr['original_index'],
                    $curr['sta_awal_str'],
                    $curr['sta_akhir_str']
                );
            }
        }

        return $errors;
    }
}
