<?php

namespace App\Services;

use App\Models\AppSetting;

class AppSettingService
{
    /**
     * @var array<int, array{key:string,label:string,value:string,type:string,description:string}>
     */
    public const DEFAULTS = [
        ['key' => 'school_name', 'label' => 'Nama Sekolah', 'value' => 'SMP IP YAKIN', 'type' => 'text', 'description' => 'Nama sekolah yang tampil di portal.'],
        ['key' => 'academic_year', 'label' => 'Tahun Ajaran Aktif', 'value' => '2025/2026', 'type' => 'text', 'description' => 'Tahun ajaran default untuk data akademik.'],
        ['key' => 'active_semester', 'label' => 'Semester Aktif', 'value' => 'ganjil', 'type' => 'text', 'description' => 'Semester aktif untuk kalender akademik dan agenda dashboard.'],
        ['key' => 'school_start_date', 'label' => 'Tanggal Awal Masuk Sekolah', 'value' => '2025-07-14', 'type' => 'text', 'description' => 'Tanggal pertama awal masuk sekolah / mulai KBM semester aktif (format: YYYY-MM-DD).'],
        ['key' => 'contact_phone', 'label' => 'Kontak Sekolah', 'value' => '', 'type' => 'text', 'description' => 'Nomor kontak sekolah.'],
    ];

    public function ensureDefaults(): void
    {
        foreach (self::DEFAULTS as $setting) {
            AppSetting::query()->firstOrCreate(['key' => $setting['key']], $setting);
        }
    }

    public function value(string $key, ?string $default = null): ?string
    {
        $value = AppSetting::query()
            ->where('key', $key)
            ->value('value');

        if ($value !== null) {
            return $value;
        }

        foreach (self::DEFAULTS as $setting) {
            if ($setting['key'] === $key) {
                return $setting['value'];
            }
        }

        return $default;
    }

    public function clearCache(): void
    {
    }
}
