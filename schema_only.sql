CREATE TABLE `cabors` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nama` varchar(255) NOT NULL,
  `tipe` enum('olahraga_prestasi','olahraga_masyarakat') NOT NULL DEFAULT 'olahraga_prestasi' COMMENT 'Jenis cabor: KONI (prestasi) atau KORMI (masyarakat)',
  `nama_pengprov` varchar(255) DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `cabor_event` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `event_id` bigint(20) UNSIGNED NOT NULL,
  `cabor_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `cabor_prasarana` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `prasarana_id` bigint(20) UNSIGNED NOT NULL,
  `cabor_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `ekstrakurikuler_sekolah` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `sekolah_id` bigint(20) UNSIGNED NOT NULL,
  `jenis_ekskul_id` bigint(20) UNSIGNED NOT NULL,
  `nama_pembina` varchar(255) NOT NULL,
  `jumlah_anggota_putra` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `jumlah_anggota_putri` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `dokumen_jumlah_anggota` varchar(255) DEFAULT NULL,
  `jadwal_pertemuan` varchar(255) DEFAULT NULL,
  `status_ekstrakurikuler` enum('Aktif','Non-Aktif') NOT NULL DEFAULT 'Aktif',
  `narahubung` varchar(255) DEFAULT NULL,
  `telepon` varchar(20) DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `events` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `kab_kota_id` bigint(20) UNSIGNED DEFAULT NULL,
  `jenis_id` bigint(20) UNSIGNED NOT NULL,
  `nama` varchar(255) NOT NULL,
  `tahun` smallint(5) UNSIGNED DEFAULT NULL COMMENT 'Tahun kegiatan (bisa beda dengan tahun pelaksanaan)',
  `skala_id` bigint(20) UNSIGNED DEFAULT NULL,
  `jenis_event` enum('single event','multi event','pelatihan','perlombaan') NOT NULL DEFAULT 'perlombaan',
  `penyelenggara` varchar(255) NOT NULL,
  `lokasi_kegiatan` varchar(255) DEFAULT NULL,
  `tanggal_mulai` date DEFAULT NULL,
  `tanggal_selesai` date DEFAULT NULL,
  `status` enum('aktif','selesai','dibatalkan') NOT NULL DEFAULT 'aktif',
  `disabilitas` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `fasilitas_prasarana` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `prasarana_id` bigint(20) UNSIGNED NOT NULL,
  `nama` varchar(255) NOT NULL,
  `jumlah` int(11) NOT NULL DEFAULT 1,
  `kondisi` enum('Baik','Rusak Ringan','Rusak Berat') NOT NULL DEFAULT 'Baik',
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `foto_prasarana` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `prasarana_id` bigint(20) UNSIGNED NOT NULL,
  `foto` varchar(255) NOT NULL,
  `deskripsi` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `informasi` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `judul` varchar(255) NOT NULL,
  `isi` longtext NOT NULL,
  `file_pendukung` varchar(255) DEFAULT NULL,
  `slug` varchar(255) NOT NULL,
  `gambar` varchar(255) DEFAULT NULL,
  `author_id` bigint(20) UNSIGNED DEFAULT NULL,
  `status` enum('draft','published') NOT NULL DEFAULT 'draft',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `jenis` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nama` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `jenis_ekstrakurikuler` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nama` varchar(255) NOT NULL,
  `kategori` enum('olahraga','kepemimpinan','seni_budaya','akademik_sains','keagamaan') NOT NULL DEFAULT 'olahraga',
  `cabor_id` bigint(20) UNSIGNED DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `kab_kota` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(10) NOT NULL,
  `type` enum('kabupaten','kota') NOT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `log_sistem` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `user_name` varchar(255) DEFAULT NULL,
  `user_role` varchar(255) DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `module` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `old_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_data`)),
  `new_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_data`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `nomor_tanding` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `cabor_id` bigint(20) UNSIGNED NOT NULL,
  `nama` varchar(255) NOT NULL,
  `kategori` enum('Tim','Individu') NOT NULL DEFAULT 'Individu',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `notifications` (
  `id` char(36) NOT NULL,
  `type` varchar(255) NOT NULL,
  `notifiable_type` varchar(255) NOT NULL,
  `notifiable_id` bigint(20) UNSIGNED NOT NULL,
  `data` text NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `orang` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `sportif_id` varchar(20) DEFAULT NULL,
  `nik` varchar(16) DEFAULT NULL,
  `nama` varchar(255) NOT NULL,
  `tgl_lahir` date DEFAULT NULL,
  `telp` varchar(20) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `gender` enum('L','P') DEFAULT NULL,
  `disabilitas` tinyint(1) NOT NULL DEFAULT 0,
  `jenis_disabilitas` enum('fisik','intelektual','mental','sensorik_netra','sensorik_rungu','ganda') DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `tinggi` decimal(5,2) DEFAULT NULL COMMENT 'cm',
  `berat` decimal(5,2) DEFAULT NULL COMMENT 'kg',
  `gol_darah` enum('A','B','AB','O') DEFAULT NULL,
  `domisili_id` bigint(20) UNSIGNED DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `orang_status` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `orang_id` bigint(20) UNSIGNED NOT NULL,
  `jenis_id` bigint(20) UNSIGNED NOT NULL,
  `peran_id` bigint(20) UNSIGNED NOT NULL,
  `cabor_id` bigint(20) UNSIGNED DEFAULT NULL,
  `organisasi_id` bigint(20) UNSIGNED DEFAULT NULL,
  `id_sitenor` varchar(255) DEFAULT NULL,
  `sertifikat_profesi` varchar(255) DEFAULT NULL,
  `skala_id` bigint(20) UNSIGNED DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `organisasi` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `jenis_id` bigint(20) UNSIGNED NOT NULL,
  `nama` varchar(255) NOT NULL,
  `alamat` text DEFAULT NULL,
  `telp` varchar(20) DEFAULT NULL,
  `narahubung` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `sk_pendirian` varchar(255) DEFAULT NULL,
  `tgl_sk_pendirian` date DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `status` enum('Aktif','Non-Aktif') NOT NULL DEFAULT 'Aktif',
  `skala_id` bigint(20) UNSIGNED DEFAULT NULL,
  `kab_kota_id` bigint(20) UNSIGNED DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `pengumuman` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `judul` varchar(255) NOT NULL,
  `isi` text NOT NULL,
  `file_lampiran` varchar(255) DEFAULT NULL,
  `author_id` bigint(20) UNSIGNED DEFAULT NULL,
  `status` enum('draft','active','expired') NOT NULL DEFAULT 'draft',
  `target` enum('all','public','admin') NOT NULL DEFAULT 'all',
  `tampil_mulai` datetime DEFAULT NULL,
  `tampil_selesai` datetime DEFAULT NULL,
  `is_pinned` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `pengurus_organisasi` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `organisasi_id` bigint(20) UNSIGNED NOT NULL,
  `ketua_id` bigint(20) UNSIGNED DEFAULT NULL,
  `sekretaris_id` bigint(20) UNSIGNED DEFAULT NULL,
  `bendahara_id` bigint(20) UNSIGNED DEFAULT NULL,
  `jumlah_anggota` int(11) DEFAULT NULL,
  `sk_kepengurusan` varchar(255) DEFAULT NULL,
  `tgl_awal` date DEFAULT NULL,
  `tgl_akhir` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `peran` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `jenis_id` bigint(20) UNSIGNED NOT NULL,
  `nama` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` text NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `prasarana` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `jenis_id` bigint(20) UNSIGNED DEFAULT NULL,
  `lokasi_id` bigint(20) UNSIGNED DEFAULT NULL,
  `nama` varchar(255) NOT NULL,
  `kategori` varchar(255) DEFAULT NULL,
  `standar` varchar(255) NOT NULL DEFAULT 'Belum di Standarisasi',
  `longitude` decimal(11,8) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `pengelola` varchar(255) DEFAULT NULL,
  `narahubung` varchar(255) DEFAULT NULL,
  `telp_narahubung` varchar(20) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `kapasitas` int(11) DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `riwayat_event` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `event_id` bigint(20) UNSIGNED NOT NULL,
  `cabor_id` bigint(20) UNSIGNED DEFAULT NULL,
  `orang_id` bigint(20) UNSIGNED NOT NULL,
  `pelatih_id` bigint(20) UNSIGNED DEFAULT NULL,
  `wasit_id` bigint(20) UNSIGNED DEFAULT NULL,
  `kategori` varchar(255) DEFAULT NULL,
  `prestasi` varchar(255) DEFAULT NULL,
  `medali` enum('emas','perak','perunggu','-') DEFAULT NULL,
  `tanggal` date DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `sarana` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `kab_kota_id` bigint(20) UNSIGNED DEFAULT NULL,
  `jenis_id` bigint(20) UNSIGNED DEFAULT NULL,
  `kode_inventaris` varchar(100) DEFAULT NULL COMMENT 'Nomor registrasi aset/BMD',
  `nama_barang` varchar(255) NOT NULL,
  `spesifikasi` text DEFAULT NULL,
  `kondisi` enum('baik','rusak_ringan','rusak_berat','butuh_perbaikan','dalam_perbaikan','tidak_layak') NOT NULL DEFAULT 'baik',
  `status` enum('tersedia','dipakai','dipinjam','dipelihara','hilang','rusak_total','dijual','dimusnahkan') NOT NULL DEFAULT 'tersedia',
  `foto_barang` varchar(255) DEFAULT NULL,
  `cabor_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'Hanya diisi jika sarana olahraga',
  `posisi_aset` enum('prasarana','internal_dinas') NOT NULL DEFAULT 'internal_dinas' COMMENT 'Flagging posisi barang',
  `lokasi_barang` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'Diisi ID Prasarana jika posisi_aset = prasarana',
  `keterangan_lokasi` text DEFAULT NULL COMMENT 'Wajib diisi nama ruangan/gudang jika posisi_aset = internal_dinas',
  `jumlah` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `satuan` varchar(50) NOT NULL DEFAULT 'buah',
  `tahun_pengadaan` year(4) DEFAULT NULL,
  `sumber_dana` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `sekolah` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `kab_kota_id` bigint(20) UNSIGNED NOT NULL,
  `nama_sekolah` varchar(255) NOT NULL,
  `jenis_sekolah` enum('SMA','SMK','MA','SLB') NOT NULL,
  `status_sekolah` enum('Negeri','Swasta') NOT NULL,
  `narahubung` varchar(255) DEFAULT NULL,
  `telepon` varchar(20) DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `skala` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nama` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `tickets` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `kode_tiket` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `judul` varchar(255) NOT NULL,
  `kategori` varchar(255) NOT NULL,
  `deskripsi` text NOT NULL,
  `status` enum('open','in_progress','closed') NOT NULL DEFAULT 'open',
  `lampiran` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `ticket_replies` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `ticket_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `pesan` text NOT NULL,
  `lampiran` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `google2fa_secret` text DEFAULT NULL,
  `kab_kota_id` bigint(20) UNSIGNED DEFAULT NULL,
  `cabor_id` bigint(20) UNSIGNED DEFAULT NULL,
  `jenis_id` bigint(20) UNSIGNED DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `cabors`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `cabor_event`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cabor_event_event_id_cabor_id_unique` (`event_id`,`cabor_id`),
  ADD KEY `cabor_event_cabor_id_foreign` (`cabor_id`);

ALTER TABLE `cabor_prasarana`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cabor_prasarana_prasarana_id_foreign` (`prasarana_id`),
  ADD KEY `cabor_prasarana_cabor_id_foreign` (`cabor_id`);

ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

ALTER TABLE `ekstrakurikuler_sekolah`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ekstrakurikuler_sekolah_sekolah_id_foreign` (`sekolah_id`),
  ADD KEY `ekstrakurikuler_sekolah_jenis_ekskul_id_foreign` (`jenis_ekskul_id`),
  ADD KEY `ekstrakurikuler_sekolah_created_by_foreign` (`created_by`);

ALTER TABLE `events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `events_jenis_id_foreign` (`jenis_id`),
  ADD KEY `events_skala_id_foreign` (`skala_id`),
  ADD KEY `events_kab_kota_id_foreign` (`kab_kota_id`),
  ADD KEY `events_tahun_index` (`tahun`);

ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

ALTER TABLE `fasilitas_prasarana`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fasilitas_prasarana_prasarana_id_foreign` (`prasarana_id`);

ALTER TABLE `foto_prasarana`
  ADD PRIMARY KEY (`id`),
  ADD KEY `foto_prasarana_prasarana_id_foreign` (`prasarana_id`);

ALTER TABLE `informasi`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `informasi_slug_unique` (`slug`),
  ADD KEY `informasi_author_id_foreign` (`author_id`);

ALTER TABLE `jenis`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `jenis_ekstrakurikuler`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jenis_ekstrakurikuler_cabor_id_foreign` (`cabor_id`);

ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `kab_kota`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `log_sistem`
  ADD PRIMARY KEY (`id`),
  ADD KEY `log_sistem_user_id_foreign` (`user_id`);

ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `model_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  ADD KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`);

ALTER TABLE `model_has_roles`
  ADD PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  ADD KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`);

ALTER TABLE `nomor_tanding`
  ADD PRIMARY KEY (`id`),
  ADD KEY `nomor_tanding_cabor_id_foreign` (`cabor_id`);

ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`);

ALTER TABLE `orang`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `orang_sportif_id_unique` (`sportif_id`),
  ADD KEY `orang_domisili_id_foreign` (`domisili_id`);

ALTER TABLE `orang_status`
  ADD PRIMARY KEY (`id`),
  ADD KEY `orang_status_orang_id_foreign` (`orang_id`),
  ADD KEY `orang_status_jenis_id_foreign` (`jenis_id`),
  ADD KEY `orang_status_peran_id_foreign` (`peran_id`),
  ADD KEY `orang_status_cabor_id_foreign` (`cabor_id`),
  ADD KEY `orang_status_organisasi_id_foreign` (`organisasi_id`),
  ADD KEY `orang_status_skala_id_foreign` (`skala_id`);

ALTER TABLE `organisasi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `organisasi_jenis_id_foreign` (`jenis_id`),
  ADD KEY `organisasi_skala_id_foreign` (`skala_id`),
  ADD KEY `organisasi_kab_kota_id_foreign` (`kab_kota_id`);

ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

ALTER TABLE `pengumuman`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pengumuman_author_id_foreign` (`author_id`);

ALTER TABLE `pengurus_organisasi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pengurus_organisasi_organisasi_id_foreign` (`organisasi_id`),
  ADD KEY `pengurus_organisasi_ketua_id_foreign` (`ketua_id`),
  ADD KEY `pengurus_organisasi_sekretaris_id_foreign` (`sekretaris_id`),
  ADD KEY `pengurus_organisasi_bendahara_id_foreign` (`bendahara_id`);

ALTER TABLE `peran`
  ADD PRIMARY KEY (`id`),
  ADD KEY `peran_jenis_id_foreign` (`jenis_id`);

ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`);

ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  ADD KEY `personal_access_tokens_expires_at_index` (`expires_at`);

ALTER TABLE `prasarana`
  ADD PRIMARY KEY (`id`),
  ADD KEY `prasarana_jenis_id_foreign` (`jenis_id`),
  ADD KEY `prasarana_lokasi_id_foreign` (`lokasi_id`);

ALTER TABLE `riwayat_event`
  ADD PRIMARY KEY (`id`),
  ADD KEY `riwayat_event_event_id_foreign` (`event_id`),
  ADD KEY `riwayat_event_cabor_id_foreign` (`cabor_id`),
  ADD KEY `riwayat_event_pelatih_id_foreign` (`pelatih_id`),
  ADD KEY `riwayat_event_wasit_id_foreign` (`wasit_id`),
  ADD KEY `riwayat_event_orang_id_event_id_index` (`orang_id`,`event_id`);

ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`);

ALTER TABLE `role_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `role_has_permissions_role_id_foreign` (`role_id`);

ALTER TABLE `sarana`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sarana_kab_kota_id_foreign` (`kab_kota_id`),
  ADD KEY `sarana_cabor_id_foreign` (`cabor_id`),
  ADD KEY `sarana_jenis_id_foreign` (`jenis_id`);

ALTER TABLE `sekolah`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sekolah_kab_kota_id_foreign` (`kab_kota_id`),
  ADD KEY `sekolah_created_by_foreign` (`created_by`);

ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

ALTER TABLE `skala`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `tickets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tickets_kode_tiket_unique` (`kode_tiket`),
  ADD KEY `tickets_user_id_foreign` (`user_id`);

ALTER TABLE `ticket_replies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ticket_replies_ticket_id_foreign` (`ticket_id`),
  ADD KEY `ticket_replies_user_id_foreign` (`user_id`);

ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD UNIQUE KEY `users_username_unique` (`username`);

ALTER TABLE `cabors`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=161;

ALTER TABLE `cabor_event`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `cabor_prasarana`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `ekstrakurikuler_sekolah`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `events`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `fasilitas_prasarana`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=235;

ALTER TABLE `foto_prasarana`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `informasi`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `jenis`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

ALTER TABLE `jenis_ekstrakurikuler`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `kab_kota`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=77;

ALTER TABLE `log_sistem`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

ALTER TABLE `nomor_tanding`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `orang`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3780;

ALTER TABLE `orang_status`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3814;

ALTER TABLE `organisasi`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `pengumuman`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `pengurus_organisasi`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `peran`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `prasarana`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=91;

ALTER TABLE `riwayat_event`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=154;

ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

ALTER TABLE `sarana`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

ALTER TABLE `sekolah`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

ALTER TABLE `skala`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

ALTER TABLE `tickets`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

ALTER TABLE `ticket_replies`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=401;

ALTER TABLE `cabor_event`
  ADD CONSTRAINT `cabor_event_cabor_id_foreign` FOREIGN KEY (`cabor_id`) REFERENCES `cabors` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cabor_event_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE;

ALTER TABLE `cabor_prasarana`
  ADD CONSTRAINT `cabor_prasarana_cabor_id_foreign` FOREIGN KEY (`cabor_id`) REFERENCES `cabors` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cabor_prasarana_prasarana_id_foreign` FOREIGN KEY (`prasarana_id`) REFERENCES `prasarana` (`id`) ON DELETE CASCADE;

ALTER TABLE `ekstrakurikuler_sekolah`
  ADD CONSTRAINT `ekstrakurikuler_sekolah_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `ekstrakurikuler_sekolah_jenis_ekskul_id_foreign` FOREIGN KEY (`jenis_ekskul_id`) REFERENCES `jenis_ekstrakurikuler` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ekstrakurikuler_sekolah_sekolah_id_foreign` FOREIGN KEY (`sekolah_id`) REFERENCES `sekolah` (`id`) ON DELETE CASCADE;

ALTER TABLE `events`
  ADD CONSTRAINT `events_jenis_id_foreign` FOREIGN KEY (`jenis_id`) REFERENCES `jenis` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `events_kab_kota_id_foreign` FOREIGN KEY (`kab_kota_id`) REFERENCES `kab_kota` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `events_skala_id_foreign` FOREIGN KEY (`skala_id`) REFERENCES `skala` (`id`) ON DELETE SET NULL;

ALTER TABLE `fasilitas_prasarana`
  ADD CONSTRAINT `fasilitas_prasarana_prasarana_id_foreign` FOREIGN KEY (`prasarana_id`) REFERENCES `prasarana` (`id`) ON DELETE CASCADE;

ALTER TABLE `foto_prasarana`
  ADD CONSTRAINT `foto_prasarana_prasarana_id_foreign` FOREIGN KEY (`prasarana_id`) REFERENCES `prasarana` (`id`) ON DELETE CASCADE;

ALTER TABLE `informasi`
  ADD CONSTRAINT `informasi_author_id_foreign` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

ALTER TABLE `jenis_ekstrakurikuler`
  ADD CONSTRAINT `jenis_ekstrakurikuler_cabor_id_foreign` FOREIGN KEY (`cabor_id`) REFERENCES `cabors` (`id`) ON DELETE SET NULL;

ALTER TABLE `log_sistem`
  ADD CONSTRAINT `log_sistem_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

ALTER TABLE `model_has_permissions`
  ADD CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

ALTER TABLE `model_has_roles`
  ADD CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

ALTER TABLE `nomor_tanding`
  ADD CONSTRAINT `nomor_tanding_cabor_id_foreign` FOREIGN KEY (`cabor_id`) REFERENCES `cabors` (`id`) ON DELETE CASCADE;

ALTER TABLE `orang`
  ADD CONSTRAINT `orang_domisili_id_foreign` FOREIGN KEY (`domisili_id`) REFERENCES `kab_kota` (`id`) ON DELETE SET NULL;

ALTER TABLE `orang_status`
  ADD CONSTRAINT `orang_status_cabor_id_foreign` FOREIGN KEY (`cabor_id`) REFERENCES `cabors` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `orang_status_jenis_id_foreign` FOREIGN KEY (`jenis_id`) REFERENCES `jenis` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `orang_status_orang_id_foreign` FOREIGN KEY (`orang_id`) REFERENCES `orang` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `orang_status_organisasi_id_foreign` FOREIGN KEY (`organisasi_id`) REFERENCES `organisasi` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `orang_status_peran_id_foreign` FOREIGN KEY (`peran_id`) REFERENCES `peran` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `orang_status_skala_id_foreign` FOREIGN KEY (`skala_id`) REFERENCES `skala` (`id`) ON DELETE SET NULL;

ALTER TABLE `organisasi`
  ADD CONSTRAINT `organisasi_jenis_id_foreign` FOREIGN KEY (`jenis_id`) REFERENCES `jenis` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `organisasi_kab_kota_id_foreign` FOREIGN KEY (`kab_kota_id`) REFERENCES `kab_kota` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `organisasi_skala_id_foreign` FOREIGN KEY (`skala_id`) REFERENCES `skala` (`id`) ON DELETE SET NULL;

ALTER TABLE `pengumuman`
  ADD CONSTRAINT `pengumuman_author_id_foreign` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

ALTER TABLE `pengurus_organisasi`
  ADD CONSTRAINT `pengurus_organisasi_bendahara_id_foreign` FOREIGN KEY (`bendahara_id`) REFERENCES `orang` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `pengurus_organisasi_ketua_id_foreign` FOREIGN KEY (`ketua_id`) REFERENCES `orang` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `pengurus_organisasi_organisasi_id_foreign` FOREIGN KEY (`organisasi_id`) REFERENCES `organisasi` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pengurus_organisasi_sekretaris_id_foreign` FOREIGN KEY (`sekretaris_id`) REFERENCES `orang` (`id`) ON DELETE SET NULL;

ALTER TABLE `peran`
  ADD CONSTRAINT `peran_jenis_id_foreign` FOREIGN KEY (`jenis_id`) REFERENCES `jenis` (`id`) ON DELETE CASCADE;

ALTER TABLE `prasarana`
  ADD CONSTRAINT `prasarana_jenis_id_foreign` FOREIGN KEY (`jenis_id`) REFERENCES `jenis` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `prasarana_lokasi_id_foreign` FOREIGN KEY (`lokasi_id`) REFERENCES `kab_kota` (`id`) ON DELETE SET NULL;

ALTER TABLE `riwayat_event`
  ADD CONSTRAINT `riwayat_event_cabor_id_foreign` FOREIGN KEY (`cabor_id`) REFERENCES `cabors` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `riwayat_event_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `riwayat_event_orang_id_foreign` FOREIGN KEY (`orang_id`) REFERENCES `orang` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `riwayat_event_pelatih_id_foreign` FOREIGN KEY (`pelatih_id`) REFERENCES `orang` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `riwayat_event_wasit_id_foreign` FOREIGN KEY (`wasit_id`) REFERENCES `orang` (`id`) ON DELETE SET NULL;

ALTER TABLE `role_has_permissions`
  ADD CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

ALTER TABLE `sarana`
  ADD CONSTRAINT `sarana_cabor_id_foreign` FOREIGN KEY (`cabor_id`) REFERENCES `cabors` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `sarana_jenis_id_foreign` FOREIGN KEY (`jenis_id`) REFERENCES `jenis` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `sarana_kab_kota_id_foreign` FOREIGN KEY (`kab_kota_id`) REFERENCES `kab_kota` (`id`) ON DELETE SET NULL;

ALTER TABLE `sekolah`
  ADD CONSTRAINT `sekolah_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `sekolah_kab_kota_id_foreign` FOREIGN KEY (`kab_kota_id`) REFERENCES `kab_kota` (`id`) ON DELETE CASCADE;

ALTER TABLE `tickets`
  ADD CONSTRAINT `tickets_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `ticket_replies`
  ADD CONSTRAINT `ticket_replies_ticket_id_foreign` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ticket_replies_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

