<?php

return [
    'webhook_url' => env('SPMB_WEBHOOK_URL'),
    'webhook_timeout' => 10,
    'webhook_session' => env('SPMB_WEBHOOK_SESSION', 'default'),
    'portal_url' => env('SPMB_PORTAL_URL', 'https://erp.asy-syifaa.com'),

    'mandatory_documents' => [
        'ijazah' => 'STTB/Ijazah Terakhir',
        'kartu_keluarga' => 'Kartu Keluarga Terbaru',
        'akta_kelahiran' => 'Akta Kelahiran',
        'ktp_ortu' => 'KTP Orangtua/Wali',
        'pas_foto' => 'Pas Foto Latar Biru (Peci+Koko Putih / Kerudung+Gamis Hitam)',
        'surat_kelakuan_baik' => 'Surat Keterangan Kelakuan Baik (Desa/Sekolah)',
        'surat_pernyataan' => 'Surat Pernyataan Bermaterai 10.000',
        'surat_bebas_tbc' => 'Surat Keterangan Bebas TBC',
    ],

    'biaya_pendaftaran' => [
        ['nama' => 'Seragam Santri', 'amount' => 0],
        ['nama' => 'Lemari Pakaian Gantung', 'amount' => 0],
        ['nama' => 'Lemari Pakaian Lipat', 'amount' => 0],
        ['nama' => 'Lemari Kitab', 'amount' => 0],
        ['nama' => 'Bangku Belajar', 'amount' => 0],
        ['nama' => 'Perlengkapan Tidur (Kasur, Bantal, Selimut)', 'amount' => 0],
        ['nama' => 'Jariyah Pembangunan', 'amount' => 0],
    ],
    'total_daftar_ulang' => 7000000,
    'spp_bulan_pertama' => 750000,
    'keterangan_spp' => 'Untuk keperluan makan dan laundry',

    'pernyataan_kebijakan' => [
        'Kondisi Calon Santri wajib sehat, baik jasmani maupun akalnya serta betul-betul terbebas dari kebiasaan merokok dan obat-obatan yang dilarang, tidak ada tato di anggota tubuh, terbebas dari kebiasaan LGBT, tidak bersikap kasar dan sifat-sifat buruk lainnya yang merugikan pihak lain.',
        'Calon Santri harus terbebas dari penyakit gila dan ayan (kesurupan).',
        'Calon Santri ketika masuk ke Pondok, wajib diantar oleh orangtua/wali.',
        'Semua administrasi harus diselesaikan sebelum santri masuk pondok.',
        'Tidak ada pengembalian biaya pendaftaran bagi santri yang sudah masuk pondok.',
        'Wajib membayar uang bulanan (Syahriyyah) paling lambat tanggal 10 Masehi tiap bulannya.',
    ],

    'timeline' => [
        ['tanggal' => 'Januari - Juni', 'kegiatan' => 'Pendaftaran Online', 'icon' => 'heroicon-o-pencil-square'],
        ['tanggal' => 'Juli Minggu 1', 'kegiatan' => 'Verifikasi Dokumen', 'icon' => 'heroicon-o-document-check'],
        ['tanggal' => 'Juli Minggu 2', 'kegiatan' => 'Tes/Ujian Masuk', 'icon' => 'heroicon-o-academic-cap'],
        ['tanggal' => 'Juli Minggu 3', 'kegiatan' => 'Pengumuman Hasil Seleksi', 'icon' => 'heroicon-o-megaphone'],
        ['tanggal' => 'Juli Minggu 4', 'kegiatan' => 'Daftar Ulang & Pembayaran', 'icon' => 'heroicon-o-banknotes'],
        ['tanggal' => 'Agustus Minggu 1', 'kegiatan' => 'Masuk Pondok', 'icon' => 'heroicon-o-home'],
    ],
];
