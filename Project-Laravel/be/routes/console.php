<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;

Schedule::call(function () {
    DB::statement("
        UPDATE campaign
        SET status = 'selesai',
            updated_at = NOW()
        WHERE status = 'aktif'
          AND tanggal_selesai IS NOT NULL
          AND tanggal_selesai < CURRENT_DATE
          AND deleted_at IS NULL
    ");
})->dailyAt('00:00')->name('close-expired-campaigns');

Schedule::call(function () {
    DB::statement("
        UPDATE notifikasi
        SET is_archived = TRUE,
            archived_at = NOW()
        WHERE is_archived = FALSE
          AND expires_at < NOW()
    ");
})->dailyAt('01:00')->name('archive-expired-notifications');

Schedule::call(function () {
    DB::statement("
        UPDATE campaign
        SET status = 'selesai',
            saldo_terkunci = 0,
            updated_at = NOW()
        WHERE status = 'aktif'
          AND dana_terkumpul >= target_dana
          AND deleted_at IS NULL
    ");
})->hourly()->name('auto-complete-funded-campaigns');
