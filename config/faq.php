<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Basis Pengetahuan Chatbot Bantuan
    |--------------------------------------------------------------------------
    |
    | Setiap item berisi kata kunci yang dicocokkan dengan pertanyaan user
    | (tidak peka huruf besar/kecil). Item dengan jumlah kecocokan terbanyak
    | akan dipilih sebagai jawaban. Jika tidak ada yang cocok, pertanyaan
    | otomatis diteruskan ke inbox admin/super admin.
    |
    */

    [
        'kata_kunci' => ['daftar', 'register', 'buat akun', 'akun baru', 'mendaftar', 'sign up'],
        'jawaban' => 'Untuk mendaftar, klik tombol "Daftar" di halaman utama lalu isi nama lengkap, email, dan password. Kamu bisa memilih peran: "Anak Kos" untuk mencari kamar, atau "Pemilik Kos" untuk mendaftarkan kos-mu. Setelah itu kamu bisa langsung masuk ke dashboard.',
    ],

    [
        'kata_kunci' => ['login', 'masuk', 'lupa password', 'reset password', 'lupa sandi'],
        'jawaban' => 'Kamu bisa masuk dengan email dan password di halaman Masuk. Jika lupa password, klik tautan "Lupa password?" di halaman masuk untuk menerima tautan reset melalui email.',
    ],

    [
        'kata_kunci' => ['peran', 'role', 'anak kos', 'pemilik kos', 'super admin', 'admin'],
        'jawaban' => 'Aplikasi ini memiliki 4 peran: 1) Anak Kos: mencari dan menyewa kamar, membayar tagihan. 2) Pemilik Kos: mendaftarkan dan mempromosikan kos, mengelola kamar, melihat pendapatan. 3) Admin: membantu pemilik mengelola kamar dan memverifikasi pembayaran. 4) Super Admin: mengelola seluruh sistem.',
    ],

    [
        'kata_kunci' => ['cari kos', 'katalog', 'pencarian', 'filter', 'promosi', 'tampil'],
        'jawaban' => 'Halaman "Cari Kos" menampilkan semua kos yang aktif dari berbagai pemilik. Kamu bisa mencari berdasarkan nama, kota, alamat, harga maksimal, dan kapasitas. Kos yang berstatus "Aktif" oleh pemiliknya akan tampil di halaman ini.',
    ],

    [
        'kata_kunci' => ['booking', 'pesan kamar', 'pesan', 'sewa kamar', 'menyewa'],
        'jawaban' => 'Untuk memesan kamar: masuk sebagai Anak Kos, buka halaman detail kos, pilih kamar yang berstatus "Tersedia", lalu klik "Pesan Kamar". Booking akan menunggu persetujuan admin/pemilik. Kamu bisa melihat status booking di dashboard Anak Kos.',
    ],

    [
        'kata_kunci' => ['pembayaran', 'bayar', 'transfer', 'verifikasi', 'bukti'],
        'jawaban' => 'Pembayaran dilakukan dengan transfer lalu mengajukan pembayaran pada tagihan di dashboard Anak Kos. Admin akan memverifikasi bukti pembayaranmu. Jika sudah diverifikasi, tagihan berubah menjadi "Lunas".',
    ],

    [
        'kata_kunci' => ['tagihan', 'denda', 'telat', 'jatuh tempo', 'terlambat'],
        'jawaban' => 'Tagihan dibuat setiap bulan sesuai periode sewa. Jika pembayaran melewati jatuh tempo, denda harian akan ditambahkan sesuai aturan masing-masing kos. Status tagihan bisa "Belum Bayar", "Lunas", atau "Terlambat".',
    ],

    [
        'kata_kunci' => ['kelola kos', 'tambah kos', 'properti', 'kamar', 'tambah kamar', 'kelola kamar'],
        'jawaban' => 'Sebagai Pemilik Kos, buka menu "Kelola Kos" di dashboard. Dari sana kamu bisa: menambah kos baru, mengisi deskripsi/fasilitas/aturan dan foto, menambah kamar beserta harganya, serta mengatur status tampil (Aktif = tampil di Cari Kos).',
    ],

    [
        'kata_kunci' => ['pendapatan', 'income', 'laba', 'uang', 'hasil'],
        'jawaban' => 'Di dashboard Pemilik Kos terdapat ringkasan pendapatan bulan ini, jumlah properti, total kamar, dan kamar terisi. Semua data otomatis dihitung dari penyewaan dan pembayaran yang terverifikasi.',
    ],

    [
        'kata_kunci' => ['kontak', 'hubungi', 'bantuan', 'admin', 'super admin', 'keluhan', 'masalah', 'error', 'bug'],
        'jawaban' => 'Kamu bisa menghubungi admin melalui form "Hubungi Admin" di halaman Bantuan, atau kirim pesan melalui chatbot ini dengan memilih opsi "Hubungi Admin". Admin akan membalas pesanmu dan balasannya bisa dilihat di halaman "Riwayat Bantuan".',
    ],

    [
        'kata_kunci' => ['verifikasi email', 'email belum', 'verified', 'surat elektronik'],
        'jawaban' => 'Beberapa akun perlu memverifikasi email sebelum mengakses dashboard. Cek kotak masuk email kamu untuk tautan verifikasi. Jika tidak menerima email, hubungi admin melalui halaman Bantuan.',
    ],

    [
        'kata_kunci' => ['password', 'sandi', 'kata sandi', 'ganti password', 'ubah password'],
        'jawaban' => 'Untuk mengganti password, buka menu Profile lalu bagian "Update Password". Isi password lama, password baru, dan konfirmasi. Pastikan password baru minimal 8 karakter.',
    ],

    [
        'kata_kunci' => ['hapus akun', 'delete akun', 'keluar', 'logout'],
        'jawaban' => 'Untuk keluar, klik menu dropdown nama kamu lalu pilih "Log Out". Penghapusan akun bisa dilakukan di halaman Profile bagian "Delete Account" dengan memasukkan password.',
    ],

];
