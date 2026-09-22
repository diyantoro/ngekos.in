<?php

return [
    'trial_days' => 7,

    'free' => [
        'name' => 'Free',
        'price' => 0,
        'limits' => [
            'properties' => 1,
            'rooms' => 10,
            'staff' => 1,
        ],
        'features' => [
            'basic_dashboard',
            'basic_property',
            'basic_room',
            'basic_tenant',
            'basic_billing',
            'basic_report',
            'export_pdf_basic',
        ],
        'report' => [
            'tier' => 'basic',
            'max_periode' => 3,
            'pdf' => true,
            'excel' => true,
            'watermark' => true,
        ],
    ],

    'pro' => [
        'name' => 'Pro',
        'price' => 49000,
        'limits' => [
            'properties' => 5,
            'rooms' => 100,
            'staff' => 3,
        ],
        'features' => [
            'advanced_analytics',
            'advanced_report',
            'export_report',
            'automatic_invoice',
            'automatic_fine',
            'broadcast',
            'maintenance',
            'multi_property',
            'multi_user',
        ],
        'report' => [
            'tier' => 'pro',
            'max_periode' => 12,
            'pdf' => true,
            'excel' => true,
            'watermark' => false,
            'sheets' => ['ringkasan', 'tagihan_belum_bayar', 'kos_pemasukan_terbesar', 'naik_turun_bulanan', 'kategori_pengeluaran'],
        ],
    ],

    'business' => [
        'name' => 'Business',
        'price' => 99000,
        'limits' => [
            'properties' => null,
            'rooms' => null,
            'staff' => 10,
        ],
        // Hanya fitur yang benar-benar ada di aplikasi + otomatis
        // mewarisi semua fitur PRO via SubscriptionService::hasFeature().
        'features' => [
            'unlimited_property',
            'unlimited_room',
            'laporan_24_bulan',
            'excel_7_sheet',
        ],
        'report' => [
            'tier' => 'business',
            'max_periode' => 24,
            'pdf' => true,
            'excel' => true,
            'watermark' => false,
            'sheets' => ['ringkasan', 'tagihan_belum_bayar', 'kos_pemasukan_terbesar', 'naik_turun_bulanan', 'kategori_pengeluaran', 'rincian_tiap_kos', 'daftar_transaksi_detail'],
        ],
    ],
];
