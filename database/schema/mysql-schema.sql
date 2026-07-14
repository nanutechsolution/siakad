/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `academic_history_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `academic_history_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `mahasiswa_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tahun_akademik_id` bigint unsigned NOT NULL,
  `previous_mode` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `new_mode` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `trigger_event` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `activity_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `activity_log` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `log_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject_id` varchar(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `event` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `causer_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `causer_id` varchar(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attribute_changes` json DEFAULT NULL,
  `properties` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `subject` (`subject_type`,`subject_id`),
  KEY `causer` (`causer_type`,`causer_id`),
  KEY `activity_log_log_name_index` (`log_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `akademik_ekuivalensi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `akademik_ekuivalensi` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `prodi_id` bigint unsigned NOT NULL,
  `mk_asal_id` bigint unsigned NOT NULL,
  `mk_tujuan_id` bigint unsigned NOT NULL,
  `minimal_nilai_asal` varchar(3) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'C' COMMENT 'Grade minimal dari MK Asal untuk syarat penyetaraan',
  `sks_diakui` int DEFAULT NULL COMMENT 'Jumlah SKS yang akan diakui di transkrip baru',
  `group_identifier` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'ID grup jika beberapa MK Asal digabung menjadi satu MK Tujuan',
  `nomor_sk` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `keterangan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_by` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_ekuivalensi_pair` (`mk_asal_id`,`mk_tujuan_id`),
  KEY `akademik_ekuivalensi_prodi_id_foreign` (`prodi_id`),
  KEY `akademik_ekuivalensi_mk_tujuan_id_foreign` (`mk_tujuan_id`),
  KEY `akademik_ekuivalensi_created_by_foreign` (`created_by`),
  CONSTRAINT `akademik_ekuivalensi_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `akademik_ekuivalensi_mk_asal_id_foreign` FOREIGN KEY (`mk_asal_id`) REFERENCES `master_mata_kuliahs` (`id`),
  CONSTRAINT `akademik_ekuivalensi_mk_tujuan_id_foreign` FOREIGN KEY (`mk_tujuan_id`) REFERENCES `master_mata_kuliahs` (`id`),
  CONSTRAINT `akademik_ekuivalensi_prodi_id_foreign` FOREIGN KEY (`prodi_id`) REFERENCES `ref_prodi` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `akademik_grade_revision_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `akademik_grade_revision_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `krs_detail_id` bigint unsigned NOT NULL,
  `old_nilai_angka` decimal(5,2) NOT NULL,
  `old_nilai_huruf` varchar(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `new_nilai_angka` decimal(5,2) NOT NULL,
  `new_nilai_huruf` varchar(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `alasan_perbaikan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nomor_sk_perbaikan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `executed_by` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `akademik_grade_revision_logs_krs_detail_id_foreign` (`krs_detail_id`),
  KEY `akademik_grade_revision_logs_executed_by_foreign` (`executed_by`),
  CONSTRAINT `akademik_grade_revision_logs_executed_by_foreign` FOREIGN KEY (`executed_by`) REFERENCES `users` (`id`),
  CONSTRAINT `akademik_grade_revision_logs_krs_detail_id_foreign` FOREIGN KEY (`krs_detail_id`) REFERENCES `krs_detail` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `akademik_transkrip`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `akademik_transkrip` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `mahasiswa_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `mata_kuliah_id` bigint unsigned NOT NULL,
  `krs_detail_id` bigint unsigned NOT NULL,
  `sks_diakui` int NOT NULL,
  `nilai_angka_final` decimal(5,2) NOT NULL,
  `nilai_huruf_final` varchar(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nilai_indeks_final` decimal(3,2) NOT NULL,
  `is_konversi` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unik_transkrip_mhs_mk` (`mahasiswa_id`,`mata_kuliah_id`),
  KEY `akademik_transkrip_mata_kuliah_id_foreign` (`mata_kuliah_id`),
  KEY `akademik_transkrip_krs_detail_id_foreign` (`krs_detail_id`),
  CONSTRAINT `akademik_transkrip_krs_detail_id_foreign` FOREIGN KEY (`krs_detail_id`) REFERENCES `krs_detail` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `akademik_transkrip_mahasiswa_id_foreign` FOREIGN KEY (`mahasiswa_id`) REFERENCES `mahasiswas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `akademik_transkrip_mata_kuliah_id_foreign` FOREIGN KEY (`mata_kuliah_id`) REFERENCES `master_mata_kuliahs` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `bank_kampuses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `bank_kampuses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nama_bank` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `no_rekening` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `atas_nama` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `logo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `dispensasi_akademik_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `dispensasi_akademik_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `dispensasi_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `aksi` enum('DIBUAT','DIUPDATE','DISETUJUI','DITOLAK','DIBATALKAN','EXPIRED') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `dilakukan_oleh` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `before_data` json DEFAULT NULL,
  `after_data` json DEFAULT NULL,
  `catatan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `dispensasi_akademik_logs_dilakukan_oleh_foreign` (`dilakukan_oleh`),
  KEY `dispensasi_akademik_logs_dispensasi_id_foreign` (`dispensasi_id`),
  CONSTRAINT `dispensasi_akademik_logs_dilakukan_oleh_foreign` FOREIGN KEY (`dilakukan_oleh`) REFERENCES `users` (`id`),
  CONSTRAINT `dispensasi_akademik_logs_dispensasi_id_foreign` FOREIGN KEY (`dispensasi_id`) REFERENCES `dispensasi_akademiks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `dispensasi_akademiks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `dispensasi_akademiks` (
  `id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `mahasiswa_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `jenis` enum('KRS') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `alasan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `berlaku_mulai` date NOT NULL,
  `berlaku_sampai` date NOT NULL,
  `status` enum('DRAFT','AKTIF','EXPIRED','DIBATALKAN') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'DRAFT',
  `disetujui_oleh` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `disetujui_pada` timestamp NULL DEFAULT NULL,
  `created_by` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `dispensasi_akademiks_mahasiswa_id_foreign` (`mahasiswa_id`),
  KEY `dispensasi_akademiks_disetujui_oleh_foreign` (`disetujui_oleh`),
  KEY `dispensasi_akademiks_created_by_foreign` (`created_by`),
  CONSTRAINT `dispensasi_akademiks_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `dispensasi_akademiks_disetujui_oleh_foreign` FOREIGN KEY (`disetujui_oleh`) REFERENCES `users` (`id`),
  CONSTRAINT `dispensasi_akademiks_mahasiswa_id_foreign` FOREIGN KEY (`mahasiswa_id`) REFERENCES `mahasiswas` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `jadwal_komponen_nilai`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jadwal_komponen_nilai` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `jadwal_kuliah_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `komponen_id` bigint unsigned NOT NULL,
  `bobot_persen` decimal(5,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `jkn_jadwal_komponen_unique` (`jadwal_kuliah_id`,`komponen_id`),
  KEY `jadwal_komponen_nilai_komponen_id_foreign` (`komponen_id`),
  CONSTRAINT `jadwal_komponen_nilai_jadwal_kuliah_id_foreign` FOREIGN KEY (`jadwal_kuliah_id`) REFERENCES `jadwal_kuliah` (`id`) ON DELETE CASCADE,
  CONSTRAINT `jadwal_komponen_nilai_komponen_id_foreign` FOREIGN KEY (`komponen_id`) REFERENCES `ref_komponen_nilai` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `jadwal_kuliah`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jadwal_kuliah` (
  `id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tahun_akademik_id` bigint unsigned NOT NULL,
  `kurikulum_id` bigint unsigned DEFAULT NULL,
  `mata_kuliah_id` bigint unsigned NOT NULL,
  `kelas_id` bigint unsigned NOT NULL,
  `hari` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jam_mulai` time DEFAULT NULL,
  `jam_selesai` time DEFAULT NULL,
  `ruang_id` bigint unsigned DEFAULT NULL,
  `kuota_kelas` int NOT NULL DEFAULT '40',
  `isi_kelas` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `jadwal_kuliah_tahun_akademik_id_foreign` (`tahun_akademik_id`),
  KEY `jadwal_kuliah_mata_kuliah_id_foreign` (`mata_kuliah_id`),
  KEY `jadwal_kuliah_kurikulum_id_foreign` (`kurikulum_id`),
  KEY `jadwal_kuliah_ruang_id_foreign` (`ruang_id`),
  KEY `jadwal_kuliah_kelas_id_foreign` (`kelas_id`),
  CONSTRAINT `jadwal_kuliah_kelas_id_foreign` FOREIGN KEY (`kelas_id`) REFERENCES `kelas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `jadwal_kuliah_kurikulum_id_foreign` FOREIGN KEY (`kurikulum_id`) REFERENCES `master_kurikulums` (`id`) ON DELETE SET NULL,
  CONSTRAINT `jadwal_kuliah_mata_kuliah_id_foreign` FOREIGN KEY (`mata_kuliah_id`) REFERENCES `master_mata_kuliahs` (`id`),
  CONSTRAINT `jadwal_kuliah_ruang_id_foreign` FOREIGN KEY (`ruang_id`) REFERENCES `ref_ruang` (`id`),
  CONSTRAINT `jadwal_kuliah_tahun_akademik_id_foreign` FOREIGN KEY (`tahun_akademik_id`) REFERENCES `ref_tahun_akademik` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `jadwal_kuliah_dosen`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jadwal_kuliah_dosen` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `jadwal_kuliah_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `dosen_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_koordinator` tinyint(1) NOT NULL DEFAULT '0',
  `is_penilai` tinyint(1) NOT NULL DEFAULT '0',
  `rencana_tatap_muka` int NOT NULL DEFAULT '14',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `jadwal_kuliah_dosen_jadwal_kuliah_id_foreign` (`jadwal_kuliah_id`),
  KEY `jadwal_kuliah_dosen_dosen_id_foreign` (`dosen_id`),
  CONSTRAINT `jadwal_kuliah_dosen_dosen_id_foreign` FOREIGN KEY (`dosen_id`) REFERENCES `trx_dosen` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `jadwal_kuliah_dosen_jadwal_kuliah_id_foreign` FOREIGN KEY (`jadwal_kuliah_id`) REFERENCES `jadwal_kuliah` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `jadwal_ujian_pengawas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jadwal_ujian_pengawas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `jadwal_ujian_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `person_id` bigint unsigned NOT NULL,
  `peran` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'PENGAWAS',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `jup_ujian_person_unique` (`jadwal_ujian_id`,`person_id`),
  KEY `jadwal_ujian_pengawas_person_id_foreign` (`person_id`),
  CONSTRAINT `jadwal_ujian_pengawas_jadwal_ujian_id_foreign` FOREIGN KEY (`jadwal_ujian_id`) REFERENCES `jadwal_ujians` (`id`) ON DELETE CASCADE,
  CONSTRAINT `jadwal_ujian_pengawas_person_id_foreign` FOREIGN KEY (`person_id`) REFERENCES `ref_person` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `jadwal_ujian_pesertas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jadwal_ujian_pesertas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `jadwal_ujian_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `krs_detail_id` bigint unsigned NOT NULL,
  `status_kehadiran` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'A',
  `nomor_kursi` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `waktu_check_in` datetime DEFAULT NULL,
  `catatan_pelanggaran` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `jup_ujian_krsd_unique` (`jadwal_ujian_id`,`krs_detail_id`),
  KEY `jadwal_ujian_pesertas_krs_detail_id_foreign` (`krs_detail_id`),
  CONSTRAINT `jadwal_ujian_pesertas_jadwal_ujian_id_foreign` FOREIGN KEY (`jadwal_ujian_id`) REFERENCES `jadwal_ujians` (`id`) ON DELETE CASCADE,
  CONSTRAINT `jadwal_ujian_pesertas_krs_detail_id_foreign` FOREIGN KEY (`krs_detail_id`) REFERENCES `krs_detail` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `jadwal_ujians`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jadwal_ujians` (
  `id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `jadwal_kuliah_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `jenis_ujian` enum('UTS','UAS','SUSULAN','LAINNYA') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tanggal_ujian` date NOT NULL,
  `jam_mulai` time NOT NULL,
  `jam_selesai` time NOT NULL,
  `ruang_id` bigint unsigned DEFAULT NULL,
  `metode_ujian` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'TERTULIS',
  `keterangan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `jadwal_ujians_ruang_id_foreign` (`ruang_id`),
  KEY `jadwal_ujians_jadwal_kuliah_id_foreign` (`jadwal_kuliah_id`),
  CONSTRAINT `jadwal_ujians_jadwal_kuliah_id_foreign` FOREIGN KEY (`jadwal_kuliah_id`) REFERENCES `jadwal_kuliah` (`id`) ON DELETE CASCADE,
  CONSTRAINT `jadwal_ujians_ruang_id_foreign` FOREIGN KEY (`ruang_id`) REFERENCES `ref_ruang` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `kelas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kelas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nama_kelas` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `prodi_id` bigint unsigned NOT NULL,
  `program_id` bigint unsigned NOT NULL,
  `angkatan_id` int NOT NULL,
  `kapasitas` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_kelas` (`nama_kelas`,`prodi_id`,`program_id`,`angkatan_id`),
  KEY `kelas_prodi_id_foreign` (`prodi_id`),
  KEY `kelas_program_id_foreign` (`program_id`),
  CONSTRAINT `kelas_prodi_id_foreign` FOREIGN KEY (`prodi_id`) REFERENCES `ref_prodi` (`id`),
  CONSTRAINT `kelas_program_id_foreign` FOREIGN KEY (`program_id`) REFERENCES `ref_program` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `kelas_dosen_wali`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kelas_dosen_wali` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `kelas_id` bigint unsigned NOT NULL,
  `dosen_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `kelas_dosen_wali_kelas_id_foreign` (`kelas_id`),
  KEY `kelas_dosen_wali_dosen_id_foreign` (`dosen_id`),
  CONSTRAINT `kelas_dosen_wali_dosen_id_foreign` FOREIGN KEY (`dosen_id`) REFERENCES `trx_dosen` (`id`) ON DELETE CASCADE,
  CONSTRAINT `kelas_dosen_wali_kelas_id_foreign` FOREIGN KEY (`kelas_id`) REFERENCES `kelas` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `keuangan_adjustments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `keuangan_adjustments` (
  `id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nomor_adjustment` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tagihan_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `jenis_adjustment` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nominal` decimal(15,2) NOT NULL,
  `keterangan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'DRAFT',
  `created_by` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `diajukan_oleh` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `diajukan_at` timestamp NULL DEFAULT NULL,
  `disetujui_oleh` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `disetujui_at` timestamp NULL DEFAULT NULL,
  `catatan_approval` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `diposting_at` timestamp NULL DEFAULT NULL,
  `dibatalkan_oleh` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dibatalkan_at` timestamp NULL DEFAULT NULL,
  `alasan_pembatalan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `adjustment_pembalik_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tindak_lanjut_kelebihan_bayar` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'TIDAK_ADA',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `keuangan_adjustments_nomor_adjustment_unique` (`nomor_adjustment`),
  KEY `keuangan_adjustments_tagihan_id_foreign` (`tagihan_id`),
  KEY `keuangan_adjustments_created_by_foreign` (`created_by`),
  KEY `keuangan_adjustments_tagihan_id_status_index` (`tagihan_id`,`status`),
  KEY `keuangan_adjustments_diajukan_oleh_foreign` (`diajukan_oleh`),
  KEY `keuangan_adjustments_disetujui_oleh_foreign` (`disetujui_oleh`),
  KEY `keuangan_adjustments_dibatalkan_oleh_foreign` (`dibatalkan_oleh`),
  KEY `keuangan_adjustments_adjustment_pembalik_id_foreign` (`adjustment_pembalik_id`),
  CONSTRAINT `keuangan_adjustments_adjustment_pembalik_id_foreign` FOREIGN KEY (`adjustment_pembalik_id`) REFERENCES `keuangan_adjustments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `keuangan_adjustments_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `keuangan_adjustments_diajukan_oleh_foreign` FOREIGN KEY (`diajukan_oleh`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `keuangan_adjustments_dibatalkan_oleh_foreign` FOREIGN KEY (`dibatalkan_oleh`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `keuangan_adjustments_disetujui_oleh_foreign` FOREIGN KEY (`disetujui_oleh`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `keuangan_adjustments_tagihan_id_foreign` FOREIGN KEY (`tagihan_id`) REFERENCES `tagihan_mahasiswas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `keuangan_beasiswa_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `keuangan_beasiswa_details` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `beasiswa_id` bigint unsigned NOT NULL,
  `komponen_biaya_id` bigint unsigned NOT NULL,
  `tipe_diskon` enum('PERSENTASE','NOMINAL') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nilai_diskon` decimal(15,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unik_beasiswa_komponen` (`beasiswa_id`,`komponen_biaya_id`),
  KEY `keuangan_beasiswa_details_komponen_biaya_id_foreign` (`komponen_biaya_id`),
  CONSTRAINT `keuangan_beasiswa_details_beasiswa_id_foreign` FOREIGN KEY (`beasiswa_id`) REFERENCES `keuangan_master_beasiswas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `keuangan_beasiswa_details_komponen_biaya_id_foreign` FOREIGN KEY (`komponen_biaya_id`) REFERENCES `keuangan_komponen_biaya` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `keuangan_detail_tarif`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `keuangan_detail_tarif` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `skema_tarif_id` bigint unsigned NOT NULL,
  `komponen_biaya_id` bigint unsigned NOT NULL,
  `nominal` decimal(19,2) NOT NULL DEFAULT '0.00',
  `berlaku_semester` int DEFAULT NULL,
  `penerapan` enum('FLAT','ONCE') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'FLAT' COMMENT 'FLAT: Tiap Semester, ONCE: Sekali Saja',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `keuangan_detail_tarif_skema_tarif_id_foreign` (`skema_tarif_id`),
  KEY `keuangan_detail_tarif_komponen_biaya_id_foreign` (`komponen_biaya_id`),
  CONSTRAINT `keuangan_detail_tarif_komponen_biaya_id_foreign` FOREIGN KEY (`komponen_biaya_id`) REFERENCES `keuangan_komponen_biaya` (`id`),
  CONSTRAINT `keuangan_detail_tarif_skema_tarif_id_foreign` FOREIGN KEY (`skema_tarif_id`) REFERENCES `keuangan_skema_tarif` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `keuangan_general_ledgers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `keuangan_general_ledgers` (
  `id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `mahasiswa_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `referensi_dokumen` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipe_transaksi` enum('TAGIHAN','PEMBAYARAN','ADJUSTMENT','REFUND') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `debit` decimal(19,2) NOT NULL DEFAULT '0.00',
  `kredit` decimal(19,2) NOT NULL DEFAULT '0.00',
  `saldo_berjalan` decimal(19,2) NOT NULL,
  `keterangan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `keuangan_general_ledgers_mahasiswa_id_created_at_index` (`mahasiswa_id`,`created_at`),
  KEY `keuangan_general_ledgers_referensi_dokumen_index` (`referensi_dokumen`),
  CONSTRAINT `keuangan_general_ledgers_mahasiswa_id_foreign` FOREIGN KEY (`mahasiswa_id`) REFERENCES `mahasiswas` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `keuangan_komponen_biaya`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `keuangan_komponen_biaya` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `kode_komponen` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_komponen` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipe_biaya` enum('TETAP','SKS','SEKALI','INSIDENTAL') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `urutan_prioritas` int NOT NULL DEFAULT '99',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_kode_komponen` (`kode_komponen`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `keuangan_mahasiswa_beasiswas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `keuangan_mahasiswa_beasiswas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `mahasiswa_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `beasiswa_id` bigint unsigned NOT NULL,
  `tahun_akademik_mulai_id` bigint unsigned NOT NULL,
  `tahun_akademik_akhir_id` bigint unsigned DEFAULT NULL,
  `nomor_sk` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `keuangan_mahasiswa_beasiswas_beasiswa_id_foreign` (`beasiswa_id`),
  KEY `keuangan_mahasiswa_beasiswas_tahun_akademik_mulai_id_foreign` (`tahun_akademik_mulai_id`),
  KEY `keuangan_mahasiswa_beasiswas_tahun_akademik_akhir_id_foreign` (`tahun_akademik_akhir_id`),
  KEY `keuangan_mahasiswa_beasiswas_mahasiswa_id_is_active_index` (`mahasiswa_id`,`is_active`),
  CONSTRAINT `keuangan_mahasiswa_beasiswas_beasiswa_id_foreign` FOREIGN KEY (`beasiswa_id`) REFERENCES `keuangan_master_beasiswas` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `keuangan_mahasiswa_beasiswas_mahasiswa_id_foreign` FOREIGN KEY (`mahasiswa_id`) REFERENCES `mahasiswas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `keuangan_mahasiswa_beasiswas_tahun_akademik_akhir_id_foreign` FOREIGN KEY (`tahun_akademik_akhir_id`) REFERENCES `ref_tahun_akademik` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `keuangan_mahasiswa_beasiswas_tahun_akademik_mulai_id_foreign` FOREIGN KEY (`tahun_akademik_mulai_id`) REFERENCES `ref_tahun_akademik` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `keuangan_master_beasiswas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `keuangan_master_beasiswas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nama_beasiswa` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `kategori` enum('INTERNAL','EKSTERNAL','PEMERINTAH') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `keterangan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `keuangan_saldo_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `keuangan_saldo_transactions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `saldo_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipe` enum('IN','OUT') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nominal` decimal(15,2) NOT NULL,
  `referensi_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `keterangan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `keuangan_saldo_transactions_saldo_id_foreign` (`saldo_id`),
  CONSTRAINT `keuangan_saldo_transactions_saldo_id_foreign` FOREIGN KEY (`saldo_id`) REFERENCES `keuangan_saldos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `keuangan_saldos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `keuangan_saldos` (
  `id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `mahasiswa_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `saldo` decimal(15,2) NOT NULL DEFAULT '0.00',
  `last_updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `keuangan_saldos_mahasiswa_id_foreign` (`mahasiswa_id`),
  CONSTRAINT `keuangan_saldos_mahasiswa_id_foreign` FOREIGN KEY (`mahasiswa_id`) REFERENCES `mahasiswas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `keuangan_skema_tarif`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `keuangan_skema_tarif` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nama_skema` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `angkatan_id` int NOT NULL,
  `prodi_id` bigint unsigned NOT NULL,
  `program_kelas_id` bigint unsigned NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_skema_tarif` (`angkatan_id`,`prodi_id`,`program_kelas_id`),
  KEY `keuangan_skema_tarif_prodi_id_foreign` (`prodi_id`),
  KEY `keuangan_skema_tarif_program_kelas_id_foreign` (`program_kelas_id`),
  CONSTRAINT `keuangan_skema_tarif_angkatan_id_foreign` FOREIGN KEY (`angkatan_id`) REFERENCES `ref_angkatan` (`id_tahun`),
  CONSTRAINT `keuangan_skema_tarif_prodi_id_foreign` FOREIGN KEY (`prodi_id`) REFERENCES `ref_prodi` (`id`),
  CONSTRAINT `keuangan_skema_tarif_program_kelas_id_foreign` FOREIGN KEY (`program_kelas_id`) REFERENCES `ref_program` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `krs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `krs` (
  `id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `mahasiswa_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tahun_akademik_id` bigint unsigned NOT NULL,
  `kelas_id` bigint unsigned DEFAULT NULL,
  `tgl_krs` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status_krs` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'DRAFT',
  `is_paket_snapshot` tinyint(1) DEFAULT NULL,
  `dosen_wali_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `diajukan_at` timestamp NULL DEFAULT NULL,
  `disetujui_oleh` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `disetujui_pada` timestamp NULL DEFAULT NULL,
  `ditolak_oleh` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ditolak_pada` timestamp NULL DEFAULT NULL,
  `catatan_admin` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `is_financial_verified` tinyint(1) NOT NULL DEFAULT '0',
  `financial_override_by` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `financial_override_reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `total_sks_diambil` int NOT NULL DEFAULT '0',
  `dispensasi_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `krs_mahasiswa_id_tahun_akademik_id_unique` (`mahasiswa_id`,`tahun_akademik_id`),
  KEY `krs_tahun_akademik_id_foreign` (`tahun_akademik_id`),
  KEY `krs_dosen_wali_id_foreign` (`dosen_wali_id`),
  KEY `krs_kelas_id_foreign` (`kelas_id`),
  KEY `krs_disetujui_oleh_foreign` (`disetujui_oleh`),
  KEY `krs_ditolak_oleh_foreign` (`ditolak_oleh`),
  KEY `krs_financial_override_by_foreign` (`financial_override_by`),
  KEY `krs_dispensasi_id_foreign` (`dispensasi_id`),
  CONSTRAINT `krs_disetujui_oleh_foreign` FOREIGN KEY (`disetujui_oleh`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `krs_dispensasi_id_foreign` FOREIGN KEY (`dispensasi_id`) REFERENCES `dispensasi_akademiks` (`id`) ON DELETE SET NULL,
  CONSTRAINT `krs_ditolak_oleh_foreign` FOREIGN KEY (`ditolak_oleh`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `krs_dosen_wali_id_foreign` FOREIGN KEY (`dosen_wali_id`) REFERENCES `trx_dosen` (`id`) ON DELETE SET NULL,
  CONSTRAINT `krs_financial_override_by_foreign` FOREIGN KEY (`financial_override_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `krs_kelas_id_foreign` FOREIGN KEY (`kelas_id`) REFERENCES `kelas` (`id`) ON DELETE SET NULL,
  CONSTRAINT `krs_mahasiswa_id_foreign` FOREIGN KEY (`mahasiswa_id`) REFERENCES `mahasiswas` (`id`),
  CONSTRAINT `krs_tahun_akademik_id_foreign` FOREIGN KEY (`tahun_akademik_id`) REFERENCES `ref_tahun_akademik` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `krs_detail`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `krs_detail` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `krs_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `jadwal_kuliah_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mata_kuliah_id` bigint unsigned DEFAULT NULL,
  `kode_mk_snapshot` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nama_mk_snapshot` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sks_snapshot` int DEFAULT NULL,
  `activity_type_snapshot` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'REGULAR',
  `ekuivalensi_id` bigint unsigned DEFAULT NULL,
  `status_ambil` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'B',
  `nilai_angka` decimal(5,2) NOT NULL DEFAULT '0.00',
  `nilai_huruf` varchar(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nilai_indeks` decimal(3,2) NOT NULL DEFAULT '0.00',
  `is_published` tinyint(1) NOT NULL DEFAULT '0',
  `is_locked` tinyint(1) NOT NULL DEFAULT '0',
  `is_edom_filled` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `krs_detail_krs_id_jadwal_kuliah_id_unique` (`krs_id`,`jadwal_kuliah_id`),
  UNIQUE KEY `krs_detail_prevent_double_mk` (`krs_id`,`mata_kuliah_id`),
  KEY `krs_detail_jadwal_kuliah_id_foreign` (`jadwal_kuliah_id`),
  KEY `krs_detail_ekuivalensi_id_foreign` (`ekuivalensi_id`),
  KEY `krs_detail_mata_kuliah_id_foreign` (`mata_kuliah_id`),
  CONSTRAINT `krs_detail_ekuivalensi_id_foreign` FOREIGN KEY (`ekuivalensi_id`) REFERENCES `akademik_ekuivalensi` (`id`) ON DELETE SET NULL,
  CONSTRAINT `krs_detail_jadwal_kuliah_id_foreign` FOREIGN KEY (`jadwal_kuliah_id`) REFERENCES `jadwal_kuliah` (`id`),
  CONSTRAINT `krs_detail_krs_id_foreign` FOREIGN KEY (`krs_id`) REFERENCES `krs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `krs_detail_mata_kuliah_id_foreign` FOREIGN KEY (`mata_kuliah_id`) REFERENCES `master_mata_kuliahs` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `krs_detail_nilai`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `krs_detail_nilai` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `krs_detail_id` bigint unsigned NOT NULL,
  `komponen_id` bigint unsigned NOT NULL,
  `nilai_angka` decimal(5,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `krs_detail_nilai_krs_detail_id_foreign` (`krs_detail_id`),
  KEY `krs_detail_nilai_komponen_id_foreign` (`komponen_id`),
  CONSTRAINT `krs_detail_nilai_komponen_id_foreign` FOREIGN KEY (`komponen_id`) REFERENCES `ref_komponen_nilai` (`id`),
  CONSTRAINT `krs_detail_nilai_krs_detail_id_foreign` FOREIGN KEY (`krs_detail_id`) REFERENCES `krs_detail` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `krs_status_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `krs_status_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `krs_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `aksi` enum('DIAJUKAN','DISETUJUI','DITOLAK','DIBATALKAN','DIBUKA_KEMBALI','DIUBAH_ADMIN') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `dilakukan_oleh` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `before_data` json DEFAULT NULL,
  `after_data` json DEFAULT NULL,
  `catatan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `krs_status_logs_krs_id_foreign` (`krs_id`),
  KEY `krs_status_logs_dilakukan_oleh_foreign` (`dilakukan_oleh`),
  CONSTRAINT `krs_status_logs_dilakukan_oleh_foreign` FOREIGN KEY (`dilakukan_oleh`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `krs_status_logs_krs_id_foreign` FOREIGN KEY (`krs_id`) REFERENCES `krs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `kurikulum_komponen_nilai`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kurikulum_komponen_nilai` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `kurikulum_id` bigint unsigned NOT NULL,
  `komponen_id` bigint unsigned NOT NULL,
  `bobot_persen` decimal(5,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `kurikulum_komponen_nilai_kurikulum_id_foreign` (`kurikulum_id`),
  KEY `kurikulum_komponen_nilai_komponen_id_foreign` (`komponen_id`),
  CONSTRAINT `kurikulum_komponen_nilai_komponen_id_foreign` FOREIGN KEY (`komponen_id`) REFERENCES `ref_komponen_nilai` (`id`),
  CONSTRAINT `kurikulum_komponen_nilai_kurikulum_id_foreign` FOREIGN KEY (`kurikulum_id`) REFERENCES `master_kurikulums` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `kurikulum_mata_kuliah`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kurikulum_mata_kuliah` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `kurikulum_id` bigint unsigned NOT NULL,
  `mata_kuliah_id` bigint unsigned NOT NULL,
  `semester_paket` int NOT NULL,
  `sks_tatap_muka` int NOT NULL,
  `sks_praktek` int NOT NULL DEFAULT '0',
  `sks_lapangan` int NOT NULL DEFAULT '0',
  `sifat_mk` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'W',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `kurikulum_mata_kuliah_kurikulum_id_mata_kuliah_id_unique` (`kurikulum_id`,`mata_kuliah_id`),
  KEY `kurikulum_mata_kuliah_mata_kuliah_id_foreign` (`mata_kuliah_id`),
  CONSTRAINT `kurikulum_mata_kuliah_kurikulum_id_foreign` FOREIGN KEY (`kurikulum_id`) REFERENCES `master_kurikulums` (`id`) ON DELETE CASCADE,
  CONSTRAINT `kurikulum_mata_kuliah_mata_kuliah_id_foreign` FOREIGN KEY (`mata_kuliah_id`) REFERENCES `master_mata_kuliahs` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `kurikulum_mk_prasyarat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kurikulum_mk_prasyarat` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `kurikulum_mk_id` bigint unsigned NOT NULL,
  `min_nilai_huruf` varchar(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'D',
  `prasyarat_kurikulum_mk_id` bigint unsigned NOT NULL,
  `min_nilai` decimal(3,2) NOT NULL DEFAULT '2.00',
  `logic_type` enum('AND','OR') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'AND',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unik_prasyarat` (`kurikulum_mk_id`,`prasyarat_kurikulum_mk_id`),
  KEY `kurikulum_mk_prasyarat_kurikulum_mk_id_foreign` (`kurikulum_mk_id`),
  KEY `kurikulum_mk_prasyarat_prasyarat_kurikulum_mk_id_foreign` (`prasyarat_kurikulum_mk_id`),
  CONSTRAINT `kurikulum_mk_prasyarat_kurikulum_mk_id_foreign` FOREIGN KEY (`kurikulum_mk_id`) REFERENCES `kurikulum_mata_kuliah` (`id`) ON DELETE CASCADE,
  CONSTRAINT `kurikulum_mk_prasyarat_prasyarat_kurikulum_mk_id_foreign` FOREIGN KEY (`prasyarat_kurikulum_mk_id`) REFERENCES `kurikulum_mata_kuliah` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `lpm_ami_discussions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lpm_ami_discussions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `finding_id` bigint unsigned NOT NULL,
  `user_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `attachment_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lpm_ami_discussions_finding_id_foreign` (`finding_id`),
  KEY `lpm_ami_discussions_user_id_foreign` (`user_id`),
  CONSTRAINT `lpm_ami_discussions_finding_id_foreign` FOREIGN KEY (`finding_id`) REFERENCES `lpm_ami_findings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lpm_ami_discussions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `lpm_ami_findings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lpm_ami_findings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `periode_id` bigint unsigned NOT NULL,
  `prodi_id` bigint unsigned NOT NULL,
  `jenis_temuan` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'OBSERVASI' COMMENT 'MAYOR, MINOR, OBSERVASI',
  `standar_id` bigint unsigned NOT NULL,
  `auditor_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `klasifikasi` enum('OB','KTS_MINOR','KTS_MAYOR') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `deskripsi_temuan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `rekomendasi` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `akar_masalah` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `rencana_tindak_lanjut` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `deadline_perbaikan` date DEFAULT NULL,
  `is_closed` tinyint(1) NOT NULL DEFAULT '0',
  `status_workflow` enum('OPEN','ACTION_PLAN','VERIFICATION','CLOSED') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'OPEN',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lpm_ami_findings_periode_id_foreign` (`periode_id`),
  KEY `lpm_ami_findings_prodi_id_foreign` (`prodi_id`),
  KEY `lpm_ami_findings_standar_id_foreign` (`standar_id`),
  CONSTRAINT `lpm_ami_findings_periode_id_foreign` FOREIGN KEY (`periode_id`) REFERENCES `lpm_ami_periodes` (`id`),
  CONSTRAINT `lpm_ami_findings_prodi_id_foreign` FOREIGN KEY (`prodi_id`) REFERENCES `ref_prodi` (`id`),
  CONSTRAINT `lpm_ami_findings_standar_id_foreign` FOREIGN KEY (`standar_id`) REFERENCES `lpm_standars` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `lpm_ami_periodes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lpm_ami_periodes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nama_periode` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tahun` year DEFAULT NULL,
  `tanggal_mulai` date DEFAULT NULL,
  `tanggal_selesai` date DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `tgl_mulai` date NOT NULL,
  `tgl_selesai` date NOT NULL,
  `status` enum('DRAFT','ON-GOING','FINISHED') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'DRAFT',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `lpm_dokumens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lpm_dokumens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `kode_dokumen` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nama_dokumen` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `jenis` enum('KEBIJAKAN','MANUAL','STANDAR','FORMULIR') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `prodi_id` bigint unsigned DEFAULT NULL,
  `file_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `deskripsi` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `versi` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1.0',
  `status` enum('DRAFT','PUBLISHED','ARCHIVED') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'PUBLISHED',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `tgl_berlaku` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `lpm_dokumens_kode_dokumen_unique` (`kode_dokumen`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `lpm_edom_jawaban`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lpm_edom_jawaban` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `krs_detail_id` bigint unsigned NOT NULL,
  `pertanyaan_id` bigint unsigned NOT NULL,
  `jawaban_nilai` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Bisa skor angka atau isian teks/esai',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lpm_edom_jawaban_krs_detail_id_foreign` (`krs_detail_id`),
  KEY `lpm_edom_jawaban_pertanyaan_id_foreign` (`pertanyaan_id`),
  CONSTRAINT `lpm_edom_jawaban_krs_detail_id_foreign` FOREIGN KEY (`krs_detail_id`) REFERENCES `krs_detail` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `lpm_edom_jawaban_pertanyaan_id_foreign` FOREIGN KEY (`pertanyaan_id`) REFERENCES `lpm_kuisioner_pertanyaan` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `lpm_edom_saran`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lpm_edom_saran` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `krs_detail_id` bigint unsigned NOT NULL,
  `catatan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lpm_edom_saran_krs_detail_id_foreign` (`krs_detail_id`),
  CONSTRAINT `lpm_edom_saran_krs_detail_id_foreign` FOREIGN KEY (`krs_detail_id`) REFERENCES `krs_detail` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `lpm_iku_targets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lpm_iku_targets` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `indikator_id` bigint unsigned NOT NULL,
  `prodi_id` bigint unsigned DEFAULT NULL,
  `tahun` int NOT NULL,
  `target_nilai` decimal(10,2) NOT NULL,
  `capaian_nilai` decimal(10,2) NOT NULL DEFAULT '0.00',
  `file_bukti_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('DRAFT','SUBMITTED','VALIDATED') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'DRAFT',
  `verified_by` varchar(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `analisis_kendala` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `tindakan_koreksi` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_target_iku` (`indikator_id`,`prodi_id`,`tahun`),
  KEY `lpm_iku_targets_indikator_id_foreign` (`indikator_id`),
  CONSTRAINT `lpm_iku_targets_indikator_id_foreign` FOREIGN KEY (`indikator_id`) REFERENCES `lpm_indikators` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `lpm_indikators`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lpm_indikators` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `standar_id` bigint unsigned NOT NULL,
  `kode_indikator` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nama_indikator` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `satuan` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '%, Orang, Dokumen, dll',
  `deskripsi` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `bobot` decimal(5,2) NOT NULL DEFAULT '0.00',
  `is_iku` tinyint(1) NOT NULL DEFAULT '1',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `sumber_data_siakad` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `calculation_method` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `calculation_params` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `lpm_indikators_slug_unique` (`slug`),
  KEY `lpm_indikators_standar_id_foreign` (`standar_id`),
  CONSTRAINT `lpm_indikators_standar_id_foreign` FOREIGN KEY (`standar_id`) REFERENCES `lpm_standars` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `lpm_kuisioner_kelompok`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lpm_kuisioner_kelompok` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tahun_akademik_id` bigint unsigned DEFAULT NULL,
  `nama_kelompok` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `urutan` int NOT NULL DEFAULT '1',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lpm_kuisioner_kelompok_tahun_akademik_id_foreign` (`tahun_akademik_id`),
  CONSTRAINT `lpm_kuisioner_kelompok_tahun_akademik_id_foreign` FOREIGN KEY (`tahun_akademik_id`) REFERENCES `ref_tahun_akademik` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `lpm_kuisioner_pertanyaan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lpm_kuisioner_pertanyaan` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `kelompok_id` bigint unsigned NOT NULL,
  `bunyi_pertanyaan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `jenis_input` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'RATING_4' COMMENT 'RATING_4, RATING_5, ESSAY, BOOLEAN',
  `is_required` tinyint(1) NOT NULL DEFAULT '1',
  `urutan` int NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lpm_kuisioner_pertanyaan_kelompok_id_foreign` (`kelompok_id`),
  CONSTRAINT `lpm_kuisioner_pertanyaan_kelompok_id_foreign` FOREIGN KEY (`kelompok_id`) REFERENCES `lpm_kuisioner_kelompok` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `lpm_standars`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lpm_standars` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `kode_standar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_standar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `kategori` enum('AKADEMIK','NON-AKADEMIK') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `pernyataan_standar` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `target_pencapaian` int NOT NULL DEFAULT '100',
  `satuan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '%',
  `versi` int NOT NULL DEFAULT '1',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `lpm_standars_kode_versi_unique` (`kode_standar`,`versi`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `lppm_luarans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lppm_luarans` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `dosen_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `jenis_luaran_id` bigint unsigned NOT NULL,
  `judul_luaran` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_penerbit_jurnal` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tautan_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tahun_terbit` year NOT NULL,
  `status_verifikasi` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'PENDING',
  `verified_by` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lppm_luarans_jenis_luaran_id_foreign` (`jenis_luaran_id`),
  KEY `lppm_luarans_dosen_id_foreign` (`dosen_id`),
  KEY `lppm_luarans_verified_by_foreign` (`verified_by`),
  CONSTRAINT `lppm_luarans_dosen_id_foreign` FOREIGN KEY (`dosen_id`) REFERENCES `trx_dosen` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lppm_luarans_jenis_luaran_id_foreign` FOREIGN KEY (`jenis_luaran_id`) REFERENCES `lppm_ref_jenis_luarans` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `lppm_luarans_verified_by_foreign` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `lppm_ref_jenis_luarans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lppm_ref_jenis_luarans` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `kode_luaran` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_luaran` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `bobot_bkd` decimal(5,2) NOT NULL DEFAULT '0.00',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `lppm_ref_jenis_luarans_kode_luaran_unique` (`kode_luaran`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `lppm_ref_jenis_skemas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lppm_ref_jenis_skemas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `kode_jenis` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_jenis` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `lppm_ref_jenis_skemas_kode_jenis_unique` (`kode_jenis`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `lppm_skemas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lppm_skemas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tahun_akademik_id` bigint unsigned NOT NULL,
  `jenis_skema_id` bigint unsigned NOT NULL,
  `nama_skema` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `maksimal_dana` decimal(19,2) NOT NULL DEFAULT '0.00',
  `tgl_mulai_daftar` date DEFAULT NULL,
  `tgl_tutup_daftar` date DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lppm_skemas_tahun_akademik_id_foreign` (`tahun_akademik_id`),
  KEY `lppm_skemas_jenis_skema_id_foreign` (`jenis_skema_id`),
  CONSTRAINT `lppm_skemas_jenis_skema_id_foreign` FOREIGN KEY (`jenis_skema_id`) REFERENCES `lppm_ref_jenis_skemas` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `lppm_skemas_tahun_akademik_id_foreign` FOREIGN KEY (`tahun_akademik_id`) REFERENCES `ref_tahun_akademik` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `lppm_usulan_anggotas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lppm_usulan_anggotas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `usulan_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `person_id` bigint unsigned NOT NULL,
  `peran_anggota` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ANGGOTA',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unik_anggota_usulan` (`usulan_id`,`person_id`),
  KEY `lppm_usulan_anggotas_person_id_foreign` (`person_id`),
  CONSTRAINT `lppm_usulan_anggotas_person_id_foreign` FOREIGN KEY (`person_id`) REFERENCES `ref_person` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lppm_usulan_anggotas_usulan_id_foreign` FOREIGN KEY (`usulan_id`) REFERENCES `lppm_usulans` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `lppm_usulans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lppm_usulans` (
  `id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `skema_id` bigint unsigned NOT NULL,
  `dosen_ketua_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `judul_usulan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `abstrak` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `dana_diajukan` decimal(19,2) NOT NULL DEFAULT '0.00',
  `dana_disetujui` decimal(19,2) DEFAULT NULL,
  `file_proposal_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status_usulan` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'DRAFT',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lppm_usulans_skema_id_foreign` (`skema_id`),
  KEY `lppm_usulans_dosen_ketua_id_foreign` (`dosen_ketua_id`),
  CONSTRAINT `lppm_usulans_dosen_ketua_id_foreign` FOREIGN KEY (`dosen_ketua_id`) REFERENCES `trx_dosen` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `lppm_usulans_skema_id_foreign` FOREIGN KEY (`skema_id`) REFERENCES `lppm_skemas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mahasiswa_kelas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mahasiswa_kelas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `mahasiswa_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `kelas_id` bigint unsigned NOT NULL,
  `tanggal_masuk` date DEFAULT NULL,
  `tanggal_keluar` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mahasiswa_kelas_mahasiswa_id_foreign` (`mahasiswa_id`),
  KEY `mahasiswa_kelas_kelas_id_foreign` (`kelas_id`),
  CONSTRAINT `mahasiswa_kelas_kelas_id_foreign` FOREIGN KEY (`kelas_id`) REFERENCES `kelas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `mahasiswa_kelas_mahasiswa_id_foreign` FOREIGN KEY (`mahasiswa_id`) REFERENCES `mahasiswas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mahasiswas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mahasiswas` (
  `id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `person_id` bigint unsigned DEFAULT NULL,
  `nim` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `angkatan_id` int NOT NULL,
  `prodi_id` bigint unsigned NOT NULL,
  `program_id` bigint unsigned DEFAULT NULL,
  `kurikulum_id` bigint unsigned DEFAULT NULL,
  `data_tambahan` json DEFAULT NULL,
  `id_pd_feeder` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_synced_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mahasiswas_nim_unique` (`nim`),
  KEY `mahasiswas_prodi_id_foreign` (`prodi_id`),
  KEY `mahasiswas_angkatan_id_foreign` (`angkatan_id`),
  KEY `mahasiswas_id_pd_feeder_index` (`id_pd_feeder`),
  KEY `mahasiswas_person_id_foreign` (`person_id`),
  KEY `mahasiswas_kurikulum_id_foreign` (`kurikulum_id`),
  KEY `idx_mhs_nim` (`nim`),
  KEY `mahasiswas_program_id_foreign` (`program_id`),
  CONSTRAINT `mahasiswas_angkatan_id_foreign` FOREIGN KEY (`angkatan_id`) REFERENCES `ref_angkatan` (`id_tahun`),
  CONSTRAINT `mahasiswas_kurikulum_id_foreign` FOREIGN KEY (`kurikulum_id`) REFERENCES `master_kurikulums` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `mahasiswas_person_id_foreign` FOREIGN KEY (`person_id`) REFERENCES `ref_person` (`id`) ON DELETE CASCADE,
  CONSTRAINT `mahasiswas_prodi_id_foreign` FOREIGN KEY (`prodi_id`) REFERENCES `ref_prodi` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `mahasiswas_program_id_foreign` FOREIGN KEY (`program_id`) REFERENCES `ref_program` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `master_kurikulums`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `master_kurikulums` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `prodi_id` bigint unsigned NOT NULL,
  `nama_kurikulum` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tahun_mulai` int NOT NULL,
  `id_semester_mulai` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `jumlah_sks_lulus` int NOT NULL DEFAULT '144' COMMENT 'Total SKS minimal untuk lulus',
  `jumlah_sks_wajib` int NOT NULL DEFAULT '0',
  `jumlah_sks_pilihan` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `no_sk_kurikulum` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tgl_sk_kurikulum` date DEFAULT NULL,
  `id_kurikulum_feeder` varchar(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `keterangan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `master_kurikulums_prodi_id_foreign` (`prodi_id`),
  CONSTRAINT `master_kurikulums_prodi_id_foreign` FOREIGN KEY (`prodi_id`) REFERENCES `ref_prodi` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `master_mata_kuliahs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `master_mata_kuliahs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `prodi_id` bigint unsigned NOT NULL,
  `kode_mk` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_mk` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `sks_default` int NOT NULL DEFAULT '3',
  `sks_tatap_muka` int NOT NULL DEFAULT '0',
  `sks_praktek` int NOT NULL DEFAULT '0',
  `sks_lapangan` int NOT NULL DEFAULT '0',
  `jenis_mk` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'A',
  `activity_type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'REGULAR',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `master_mata_kuliahs_prodi_id_kode_mk_unique` (`prodi_id`,`kode_mk`),
  CONSTRAINT `master_mata_kuliahs_prodi_id_foreign` FOREIGN KEY (`prodi_id`) REFERENCES `ref_prodi` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `model_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `model_has_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_roles` (
  `role_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `data` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `payment_policies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payment_policies` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tahun_akademik_id` bigint unsigned NOT NULL,
  `nama` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `prodi_id` bigint unsigned DEFAULT NULL,
  `program_kelas_id` bigint unsigned DEFAULT NULL,
  `angkatan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `aktif` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payment_policies_tahun_akademik_id_foreign` (`tahun_akademik_id`),
  KEY `payment_policies_prodi_id_foreign` (`prodi_id`),
  KEY `payment_policies_program_kelas_id_foreign` (`program_kelas_id`),
  CONSTRAINT `payment_policies_prodi_id_foreign` FOREIGN KEY (`prodi_id`) REFERENCES `ref_prodi` (`id`),
  CONSTRAINT `payment_policies_program_kelas_id_foreign` FOREIGN KEY (`program_kelas_id`) REFERENCES `ref_program` (`id`),
  CONSTRAINT `payment_policies_tahun_akademik_id_foreign` FOREIGN KEY (`tahun_akademik_id`) REFERENCES `ref_tahun_akademik` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `payment_policy_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payment_policy_details` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `payment_policy_id` bigint unsigned NOT NULL,
  `komponen_biaya_id` bigint unsigned NOT NULL,
  `minimal_persen` decimal(5,2) NOT NULL DEFAULT '100.00',
  `minimal_nominal` decimal(15,2) DEFAULT NULL,
  `wajib` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payment_policy_details_payment_policy_id_foreign` (`payment_policy_id`),
  KEY `payment_policy_details_komponen_biaya_id_foreign` (`komponen_biaya_id`),
  CONSTRAINT `payment_policy_details_komponen_biaya_id_foreign` FOREIGN KEY (`komponen_biaya_id`) REFERENCES `keuangan_komponen_biaya` (`id`),
  CONSTRAINT `payment_policy_details_payment_policy_id_foreign` FOREIGN KEY (`payment_policy_id`) REFERENCES `payment_policies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pembayaran_mahasiswas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pembayaran_mahasiswas` (
  `id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `idempotency_key` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tagihan_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nominal_bayar` decimal(19,2) NOT NULL,
  `tanggal_bayar` datetime NOT NULL,
  `metode_pembayaran` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'MANUAL',
  `bukti_bayar_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `keterangan_pengirim` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status_verifikasi_id` tinyint unsigned NOT NULL DEFAULT '1',
  `verified_by` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `verified_at` datetime DEFAULT NULL,
  `catatan_verifikasi` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pembayaran_mahasiswas_idempotency_key_unique` (`idempotency_key`),
  KEY `pembayaran_mahasiswas_tagihan_id_foreign` (`tagihan_id`),
  KEY `pembayaran_mahasiswas_verified_by_foreign` (`verified_by`),
  KEY `pembayaran_mahasiswas_status_verifikasi_id_index` (`status_verifikasi_id`),
  CONSTRAINT `pembayaran_mahasiswas_status_verifikasi_id_foreign` FOREIGN KEY (`status_verifikasi_id`) REFERENCES `ref_status_verifikasi_pembayaran` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `pembayaran_mahasiswas_tagihan_id_foreign` FOREIGN KEY (`tagihan_id`) REFERENCES `tagihan_mahasiswas` (`id`),
  CONSTRAINT `pembayaran_mahasiswas_verified_by_foreign` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `perkuliahan_absensi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `perkuliahan_absensi` (
  `id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `perkuliahan_sesi_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `krs_detail_id` bigint unsigned NOT NULL,
  `status_kehadiran` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'A',
  `waktu_check_in` datetime DEFAULT NULL,
  `bukti_validasi` json DEFAULT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `device_fingerprint` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_flagged_duplikat` tinyint(1) NOT NULL DEFAULT '0',
  `is_manual_update` tinyint(1) NOT NULL DEFAULT '0',
  `modified_by_user_id` varchar(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alasan_perubahan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `perkuliahan_absensi_perkuliahan_sesi_id_foreign` (`perkuliahan_sesi_id`),
  KEY `perkuliahan_absensi_krs_detail_id_status_kehadiran_index` (`krs_detail_id`,`status_kehadiran`),
  KEY `perkuliahan_absensi_status_kehadiran_index` (`status_kehadiran`),
  KEY `perkuliahan_absensi_perkuliahan_sesi_id_device_fingerprint_index` (`perkuliahan_sesi_id`,`device_fingerprint`),
  KEY `perkuliahan_absensi_perkuliahan_sesi_id_ip_address_index` (`perkuliahan_sesi_id`,`ip_address`),
  CONSTRAINT `perkuliahan_absensi_krs_detail_id_foreign` FOREIGN KEY (`krs_detail_id`) REFERENCES `krs_detail` (`id`) ON DELETE CASCADE,
  CONSTRAINT `perkuliahan_absensi_perkuliahan_sesi_id_foreign` FOREIGN KEY (`perkuliahan_sesi_id`) REFERENCES `perkuliahan_sesi` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `perkuliahan_sesi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `perkuliahan_sesi` (
  `id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `jadwal_kuliah_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `pertemuan_ke` int NOT NULL,
  `waktu_mulai_rencana` datetime NOT NULL,
  `waktu_mulai_realisasi` datetime DEFAULT NULL,
  `waktu_selesai_realisasi` datetime DEFAULT NULL,
  `materi_kuliah` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `catatan_dosen` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `token_sesi` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `token_generated_at` timestamp NULL DEFAULT NULL,
  `metode_validasi` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'QR',
  `status_sesi` enum('terjadwal','dibuka','selesai','dibatalkan') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'terjadwal',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `perkuliahan_sesi_jadwal_kuliah_id_pertemuan_ke_index` (`jadwal_kuliah_id`,`pertemuan_ke`),
  KEY `perkuliahan_sesi_token_sesi_index` (`token_sesi`),
  KEY `perkuliahan_sesi_status_sesi_index` (`status_sesi`),
  CONSTRAINT `perkuliahan_sesi_jadwal_kuliah_id_foreign` FOREIGN KEY (`jadwal_kuliah_id`) REFERENCES `jadwal_kuliah` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` varchar(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  KEY `personal_access_tokens_expires_at_index` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pmb_camaba_staging`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pmb_camaba_staging` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `external_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nomor pendaftaran dari PMB',
  `payload` json NOT NULL COMMENT 'Raw payload dari PMB',
  `status` enum('pending','processing','processed','failed') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `error_log` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Pesan error jika gagal diproses',
  `retry_count` tinyint unsigned NOT NULL DEFAULT '0',
  `last_retry_at` timestamp NULL DEFAULT NULL,
  `processed_at` timestamp NULL DEFAULT NULL,
  `source` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'PMB',
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mahasiswa_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pmb_camaba_staging_external_id_unique` (`external_id`),
  KEY `pmb_camaba_staging_status_created_at_index` (`status`,`created_at`),
  KEY `pmb_camaba_staging_status_index` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ref_angkatan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ref_angkatan` (
  `id_tahun` int NOT NULL,
  `batas_tahun_studi` int DEFAULT NULL,
  `is_active_pmb` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_tahun`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ref_aturan_sks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ref_aturan_sks` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `min_ips` decimal(4,2) NOT NULL,
  `max_ips` decimal(4,2) NOT NULL,
  `max_sks` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ref_dokumen_dosen`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ref_dokumen_dosen` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `kode` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Kode unik tanpa spasi, cth: ktp, ijazah',
  `nama_dokumen` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nama label dokumen untuk UI, cth: Scan Kartu Identitas (KTP)',
  `allowed_types` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pdf,jpg,jpeg,png' COMMENT 'Format file yang diizinkan dipisah koma',
  `max_size_kb` int NOT NULL DEFAULT '2048' COMMENT 'Batas ukuran file maksimal dalam satuan KB',
  `is_active` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Status aktif dokumen yang harus diupload',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ref_dokumen_dosen_kode_unique` (`kode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ref_fakultas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ref_fakultas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `kode_fakultas` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_fakultas` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_feeder` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ref_fakultas_kode_fakultas_unique` (`kode_fakultas`),
  KEY `ref_fakultas_id_feeder_index` (`id_feeder`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ref_gelar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ref_gelar` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `kode` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `nama` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `posisi` enum('DEPAN','BELAKANG') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'BELAKANG',
  `jenjang` enum('D3','D4','S1','S2','S3','PROFESI') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ref_gelar_kode_unique` (`kode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ref_jabatan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ref_jabatan` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `kode_jabatan` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_jabatan` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `jenis` enum('STRUKTURAL','FUNGSIONAL') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ref_jabatan_kode_jabatan_unique` (`kode_jabatan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ref_komponen_nilai`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ref_komponen_nilai` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nama_komponen` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ref_komponen_nilai_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ref_person`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ref_person` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nama_lengkap` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nik` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_hp` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `jenis_kelamin` enum('L','P') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tempat_lahir` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `photo_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ref_person_nik_unique` (`nik`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ref_person_role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ref_person_role` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `kode_role` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_role` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ref_person_role_kode_role_unique` (`kode_role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ref_prodi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ref_prodi` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `fakultas_id` bigint unsigned NOT NULL,
  `kode_prodi_dikti` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kode_prodi_internal` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_prodi` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_paket` tinyint(1) NOT NULL DEFAULT '1',
  `jenjang` enum('D3','D4','S1','S2','S3','PROFESI') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `gelar_lulusan` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `format_nim` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Pattern: {THN}=24, {TAHUN}=2024, {KODE}=KodeProdi, {NO:4}=0001',
  `last_nim_seq` int NOT NULL DEFAULT '0',
  `id_feeder` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ref_prodi_kode_prodi_internal_unique` (`kode_prodi_internal`),
  KEY `ref_prodi_fakultas_id_foreign` (`fakultas_id`),
  KEY `ref_prodi_kode_prodi_dikti_index` (`kode_prodi_dikti`),
  KEY `ref_prodi_id_feeder_index` (`id_feeder`),
  CONSTRAINT `ref_prodi_fakultas_id_foreign` FOREIGN KEY (`fakultas_id`) REFERENCES `ref_fakultas` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ref_program`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ref_program` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nama_program` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `kode_internal` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_jenis_kelas_feeder` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `deskripsi` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ref_program_kode_internal_unique` (`kode_internal`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ref_ruang`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ref_ruang` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `kode_ruang` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_ruang` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `kapasitas` int NOT NULL DEFAULT '40',
  `latitude` decimal(10,8) DEFAULT NULL COMMENT 'Koordinat garis lintang ruangan',
  `longitude` decimal(11,8) DEFAULT NULL COMMENT 'Koordinat garis bujur ruangan',
  `radius_meter` int NOT NULL DEFAULT '50' COMMENT 'Radius jangkauan absen dari titik koordinat',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `ref_ruang_kode_ruang_unique` (`kode_ruang`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ref_skala_nilai`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ref_skala_nilai` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `huruf` varchar(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `bobot_indeks` decimal(3,2) NOT NULL,
  `nilai_min` decimal(6,2) NOT NULL,
  `nilai_max` decimal(6,2) NOT NULL,
  `is_lulus` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ref_status_verifikasi_pembayaran`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ref_status_verifikasi_pembayaran` (
  `id` tinyint unsigned NOT NULL AUTO_INCREMENT,
  `kode` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `deskripsi` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `is_final` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ref_status_verifikasi_pembayaran_kode_unique` (`kode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ref_tahun_akademik`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ref_tahun_akademik` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `kode_tahun` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_tahun` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `semester` int NOT NULL COMMENT '1=Ganjil, 2=Genap, 3=Pendek',
  `tanggal_mulai` date DEFAULT NULL,
  `tanggal_selesai` date DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '0',
  `buka_krs` tinyint(1) NOT NULL DEFAULT '0',
  `is_locked_krs` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Lock manual KRS',
  `buka_input_nilai` tinyint(1) NOT NULL DEFAULT '0',
  `is_locked_nilai` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Lock manual input nilai',
  `feeder_semester_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'ID semester feeder',
  `last_sync_at` timestamp NULL DEFAULT NULL COMMENT 'Sinkronisasi feeder terakhir',
  `is_feeder_locked` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Lock sinkronisasi feeder',
  `config` json DEFAULT NULL COMMENT 'Konfigurasi tambahan',
  `created_by` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_by` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `activated_by` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `activated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `tgl_mulai_krs` date DEFAULT NULL,
  `tgl_selesai_krs` date DEFAULT NULL,
  `tgl_mulai_perkuliahan` date DEFAULT NULL COMMENT 'Tanggal mulai perkuliahan',
  `tgl_selesai_perkuliahan` date DEFAULT NULL COMMENT 'Tanggal selesai perkuliahan',
  `tgl_mulai_uts` date DEFAULT NULL COMMENT 'Tanggal mulai UTS',
  `tgl_selesai_uts` date DEFAULT NULL COMMENT 'Tanggal selesai UTS',
  `tgl_mulai_uas` date DEFAULT NULL COMMENT 'Tanggal mulai UAS',
  `tgl_selesai_uas` date DEFAULT NULL COMMENT 'Tanggal selesai UAS',
  `tgl_mulai_input_nilai` date DEFAULT NULL COMMENT 'Tanggal mulai input nilai',
  `tgl_selesai_input_nilai` date DEFAULT NULL COMMENT 'Batas akhir input nilai',
  `tgl_publish_nilai` date DEFAULT NULL COMMENT 'Tanggal publish nilai/KHS',
  PRIMARY KEY (`id`),
  UNIQUE KEY `ref_tahun_akademik_kode_tahun_unique` (`kode_tahun`),
  KEY `ref_tahun_akademik_created_by_foreign` (`created_by`),
  KEY `ref_tahun_akademik_updated_by_foreign` (`updated_by`),
  KEY `ref_tahun_akademik_activated_by_foreign` (`activated_by`),
  KEY `ref_tahun_akademik_is_active_index` (`is_active`),
  KEY `ref_tahun_akademik_tgl_publish_nilai_index` (`tgl_publish_nilai`),
  CONSTRAINT `ref_tahun_akademik_activated_by_foreign` FOREIGN KEY (`activated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ref_tahun_akademik_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ref_tahun_akademik_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `riwayat_prodi_mahasiswas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `riwayat_prodi_mahasiswas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `mahasiswa_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `prodi_id` bigint unsigned NOT NULL,
  `nomor_sk` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tanggal_berlaku` date NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `riwayat_prodi_mahasiswas_mahasiswa_id_is_active_unique` (`mahasiswa_id`,`is_active`),
  KEY `riwayat_prodi_mahasiswas_prodi_id_foreign` (`prodi_id`),
  CONSTRAINT `riwayat_prodi_mahasiswas_mahasiswa_id_foreign` FOREIGN KEY (`mahasiswa_id`) REFERENCES `mahasiswas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `riwayat_prodi_mahasiswas_prodi_id_foreign` FOREIGN KEY (`prodi_id`) REFERENCES `ref_prodi` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `riwayat_status_mahasiswas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `riwayat_status_mahasiswas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `mahasiswa_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tahun_akademik_id` bigint unsigned NOT NULL,
  `status_kuliah` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'A',
  `ips` decimal(4,2) NOT NULL DEFAULT '0.00',
  `ipk` decimal(4,2) NOT NULL DEFAULT '0.00',
  `sks_semester` int NOT NULL DEFAULT '0',
  `sks_total` int NOT NULL DEFAULT '0',
  `nomor_sk` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_status_per_semester` (`mahasiswa_id`,`tahun_akademik_id`),
  KEY `riwayat_status_mahasiswas_tahun_akademik_id_foreign` (`tahun_akademik_id`),
  KEY `riwayat_status_mahasiswas_status_kuliah_index` (`status_kuliah`),
  CONSTRAINT `riwayat_status_mahasiswas_mahasiswa_id_foreign` FOREIGN KEY (`mahasiswa_id`) REFERENCES `mahasiswas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `riwayat_status_mahasiswas_tahun_akademik_id_foreign` FOREIGN KEY (`tahun_akademik_id`) REFERENCES `ref_tahun_akademik` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `role_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `role_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `group` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `locked` tinyint(1) NOT NULL DEFAULT '0',
  `payload` json NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `settings_group_name_unique` (`group`,`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `tagihan_mahasiswas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tagihan_mahasiswas` (
  `id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `mahasiswa_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tahun_akademik_id` bigint unsigned DEFAULT NULL,
  `kode_transaksi` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `deskripsi` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_tagihan` decimal(19,2) NOT NULL,
  `total_bayar` decimal(19,2) NOT NULL DEFAULT '0.00',
  `sisa_tagihan` decimal(19,2) GENERATED ALWAYS AS ((`total_tagihan` - `total_bayar`)) VIRTUAL,
  `status_bayar` enum('BELUM','CICIL','LUNAS') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'BELUM',
  `created_by` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tenggat_waktu` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tagihan_mahasiswas_kode_transaksi_unique` (`kode_transaksi`),
  KEY `tagihan_mahasiswas_mahasiswa_id_foreign` (`mahasiswa_id`),
  KEY `tagihan_mahasiswas_tahun_akademik_id_foreign` (`tahun_akademik_id`),
  KEY `tagihan_mahasiswas_status_bayar_index` (`status_bayar`),
  KEY `tagihan_mahasiswas_created_by_foreign` (`created_by`),
  CONSTRAINT `tagihan_mahasiswas_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tagihan_mahasiswas_mahasiswa_id_foreign` FOREIGN KEY (`mahasiswa_id`) REFERENCES `mahasiswas` (`id`),
  CONSTRAINT `tagihan_mahasiswas_tahun_akademik_id_foreign` FOREIGN KEY (`tahun_akademik_id`) REFERENCES `ref_tahun_akademik` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `tagihan_mahasiswas_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tagihan_mahasiswas_details` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tagihan_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `komponen_biaya_id` bigint unsigned NOT NULL,
  `nama_komponen_snapshot` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nominal_dasar` decimal(19,2) NOT NULL,
  `nominal_diskon` decimal(19,2) NOT NULL DEFAULT '0.00',
  `nominal_tagihan` decimal(19,2) GENERATED ALWAYS AS ((`nominal_dasar` - `nominal_diskon`)) STORED,
  `nominal_terbayar` decimal(19,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unik_tagihan_komponen` (`tagihan_id`,`komponen_biaya_id`),
  KEY `tagihan_mahasiswas_details_komponen_biaya_id_foreign` (`komponen_biaya_id`),
  CONSTRAINT `tagihan_mahasiswas_details_komponen_biaya_id_foreign` FOREIGN KEY (`komponen_biaya_id`) REFERENCES `keuangan_komponen_biaya` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `tagihan_mahasiswas_details_tagihan_id_foreign` FOREIGN KEY (`tagihan_id`) REFERENCES `tagihan_mahasiswas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `trx_dosen`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `trx_dosen` (
  `id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `person_id` bigint unsigned NOT NULL,
  `prodi_id` bigint unsigned NOT NULL,
  `jenis_dosen` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'TETAP',
  `asal_institusi` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nidn` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nuptk` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `data_tambahan` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `trx_dosen_nidn_unique` (`nidn`),
  UNIQUE KEY `trx_dosen_nuptk_unique` (`nuptk`),
  KEY `trx_dosen_person_id_foreign` (`person_id`),
  KEY `trx_dosen_prodi_id_foreign` (`prodi_id`),
  KEY `idx_dosen_nidn` (`nidn`),
  CONSTRAINT `trx_dosen_person_id_foreign` FOREIGN KEY (`person_id`) REFERENCES `ref_person` (`id`) ON DELETE CASCADE,
  CONSTRAINT `trx_dosen_prodi_id_foreign` FOREIGN KEY (`prodi_id`) REFERENCES `ref_prodi` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `trx_pegawai`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `trx_pegawai` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `person_id` bigint unsigned NOT NULL,
  `nip` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jenis_pegawai` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `trx_pegawai_nip_unique` (`nip`),
  KEY `trx_pegawai_person_id_foreign` (`person_id`),
  CONSTRAINT `trx_pegawai_person_id_foreign` FOREIGN KEY (`person_id`) REFERENCES `ref_person` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `trx_person_gelar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `trx_person_gelar` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `person_id` bigint unsigned NOT NULL,
  `gelar_id` bigint unsigned NOT NULL,
  `urutan` tinyint unsigned NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `trx_person_gelar_person_id_gelar_id_unique` (`person_id`,`gelar_id`),
  KEY `trx_person_gelar_gelar_id_foreign` (`gelar_id`),
  CONSTRAINT `trx_person_gelar_gelar_id_foreign` FOREIGN KEY (`gelar_id`) REFERENCES `ref_gelar` (`id`) ON DELETE CASCADE,
  CONSTRAINT `trx_person_gelar_person_id_foreign` FOREIGN KEY (`person_id`) REFERENCES `ref_person` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `trx_person_jabatan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `trx_person_jabatan` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `person_id` bigint unsigned NOT NULL,
  `jabatan_id` bigint unsigned NOT NULL,
  `fakultas_id` bigint unsigned DEFAULT NULL,
  `prodi_id` bigint unsigned DEFAULT NULL,
  `tanggal_mulai` date NOT NULL,
  `tanggal_selesai` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `trx_person_jabatan_person_id_foreign` (`person_id`),
  KEY `trx_person_jabatan_jabatan_id_foreign` (`jabatan_id`),
  KEY `trx_person_jabatan_fakultas_id_foreign` (`fakultas_id`),
  KEY `trx_person_jabatan_prodi_id_foreign` (`prodi_id`),
  CONSTRAINT `trx_person_jabatan_fakultas_id_foreign` FOREIGN KEY (`fakultas_id`) REFERENCES `ref_fakultas` (`id`),
  CONSTRAINT `trx_person_jabatan_jabatan_id_foreign` FOREIGN KEY (`jabatan_id`) REFERENCES `ref_jabatan` (`id`),
  CONSTRAINT `trx_person_jabatan_person_id_foreign` FOREIGN KEY (`person_id`) REFERENCES `ref_person` (`id`) ON DELETE CASCADE,
  CONSTRAINT `trx_person_jabatan_prodi_id_foreign` FOREIGN KEY (`prodi_id`) REFERENCES `ref_prodi` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `trx_person_role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `trx_person_role` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `person_id` bigint unsigned NOT NULL,
  `role_id` bigint unsigned NOT NULL,
  `tanggal_mulai` date NOT NULL,
  `tanggal_selesai` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `trx_person_role_person_id_foreign` (`person_id`),
  KEY `trx_person_role_role_id_foreign` (`role_id`),
  CONSTRAINT `trx_person_role_person_id_foreign` FOREIGN KEY (`person_id`) REFERENCES `ref_person` (`id`),
  CONSTRAINT `trx_person_role_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `ref_person_role` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `person_id` bigint unsigned DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `last_login_at` timestamp NULL DEFAULT NULL,
  `last_login_ip` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_username_unique` (`username`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_person_id_foreign` (`person_id`),
  CONSTRAINT `users_person_id_foreign` FOREIGN KEY (`person_id`) REFERENCES `ref_person` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (1,'0001_01_01_000000_create_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (2,'0001_01_01_000001_create_cache_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (3,'0001_01_01_000002_create_jobs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (4,'2026_01_28_012404_create_personal_access_tokens_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (5,'2026_01_28_013820_create_ref_fakultas_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (6,'2026_01_28_013821_create_ref_prodi_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (7,'2026_01_28_013821_create_ref_program_kelas_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (8,'2026_01_28_013823_create_ref_tahun_akademik_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (9,'2026_01_28_013828_create_mahasiswas_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (10,'2026_01_28_013829_create_riwayat_status_mahasiswas_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (11,'2026_01_28_013830_create_dosens_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (12,'2026_01_28_013834_create_master_kurikulums_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (13,'2026_01_28_013834_create_master_mata_kuliahs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (14,'2026_01_28_013835_create_jadwal_kuliah_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (15,'2026_01_28_013835_create_krs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (16,'2026_01_28_013835_create_kurikulum_mata_kuliah_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (17,'2026_01_28_013837_create_krs_detail_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (18,'2026_01_28_013842_create_keuangan_detail_tarif_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (19,'2026_01_28_013842_create_keuangan_komponen_biaya_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (20,'2026_01_28_013842_create_keuangan_skema_tarif_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (21,'2026_01_28_013843_create_tagihan_mahasiswas_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (22,'2026_01_28_013844_create_pembayaran_mahasiswas_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (23,'2026_01_28_035350_add_role_to_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (24,'2026_01_28_041308_add_is_active_to_tahun_akademiks',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (25,'2026_01_28_045628_add_rincian_sks_to_master_mata_kuliahs',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (26,'2026_01_28_054424_create_activity_log_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (27,'2026_01_28_054425_add_event_column_to_activity_log_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (28,'2026_01_28_054426_add_batch_uuid_column_to_activity_log_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (29,'2026_01_28_102117_add_dosen_wali_id_to_mahasiswas',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (30,'2026_01_28_140325_create_ref_aturan_sks_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (32,'2026_01_28_160902_add_format_nim_to_ref_prodi',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (33,'2026_01_28_163335_update_kode_transaksi_length_in_tagihan_mahasiswas',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (34,'2026_01_28_172132_add_min_bayar_to_program_kelas',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (35,'2026_01_28_181644_add_feeder_columns_to_master_kurikulums',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (36,'2026_01_28_182103_modify_id_semester_length_in_master_kurikulums',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (37,'2026_01_28_182824_add_prasyarat_to_kurikulum_mata_kuliah',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (38,'2026_01_28_185449_create_ref_skala_nilai_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (39,'2026_01_29_025555_create_ref_person_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (40,'2026_01_29_025602_create_ref_person_role_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (41,'2026_01_29_025607_create_trx_person_role_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (42,'2026_01_29_025612_create_trx_dosen_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (43,'2026_01_29_025618_create_trx_pegawai_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (44,'2026_01_29_025622_create_ref_gelar_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (45,'2026_01_29_025627_create_trx_person_gelar_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (46,'2026_01_29_031749_create_ref_jabatan_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (47,'2026_01_29_031839_create_trx_person_jabatan_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (48,'2026_01_30_013833_upgrade_trx_dosen_structure',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (49,'2026_01_30_014702_drop_old_dosens_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (50,'2026_01_30_034434_finalize_user_person_ssot_architecture',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (51,'2026_01_30_041810_cleanup_mahasiswa_redundant_columns',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (52,'2026_01_30_042723_remove_nama_dekan',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (53,'2026_01_30_152532_add_columnto_dosen',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (54,'2026_01_30_232040_add_is_paket_to_prodi_and_krs',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (55,'2026_01_31_020110_create_financial_adjustment_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (56,'2026_01_31_110933_audit_log_keuangan',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (57,'2026_02_01_001920_add_column_person',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (58,'2026_02_01_002108_add_columns_to_ref_person_and_dosen',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (59,'2026_02_04_124848_ref_komponen_nilai',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (60,'2026_02_04_134114_delete_column',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (61,'2026_02_04_135606_create_lpm_standars_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (62,'2026_02_04_143113_create_table_lpm_iku_targets',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (63,'2026_02_04_144741_create_lpm_edom_jawaban',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (64,'2026_02_05_134446_jadwal_kuliah',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (65,'2026_02_05_140007_akademik_ekuivalensi',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (66,'2026_02_05_140920_krs_detail',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (67,'2026_02_06_145118_create_master_mata_kuliahs',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (68,'2026_02_06_183059_change_krs_detail',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (69,'2026_02_06_202925_create_akademik_grade_revision_logs',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (70,'2026_02_06_204350_create_perkuliahan_sesi',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (71,'2026_03_08_062753_fix_sanctum_for_uuid',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (72,'2026_03_11_031044_upgrade_siakad_architecture_v2',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (73,'2026_03_11_060554_upgrade_siakad_architecture_v3_exams_and_contracts',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (74,'2026_03_11_070659_add_security_and_soft_deletes_to_core_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (75,'2026_03_11_075518_add_photo_path_to_ref_person_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (76,'2026_03_18_052100_remove_role_id_from_users',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (77,'2026_03_21_232841_create_kelas_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (78,'2026_03_21_232901_create_mahasiswa_kelas_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (79,'2026_03_21_232922_create_kelas_dosen_wali_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (80,'2026_03_21_232958_add_kelas_id_to_mahasiswas',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (81,'2026_03_21_233048_drop_dosen_wali_id_from_mahasiswas',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (82,'2026_03_21_235349_remove_kelas_id_from_mahasiswas_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (83,'2026_03_22_000018_update_fk_program_kelas',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (84,'2026_03_22_000732_drop_program_kelas_id_from_mahasiswas',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (85,'2026_03_22_001623_create_riwayat_prodi_mahasiswas_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (86,'2026_03_22_004842_add_unique_kelas_constraint_to_kelas_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (87,'2026_03_22_025122_add_column_nama_kelas_to_jadwal_kuliah_table',3);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (88,'2026_03_22_025225_add_column_kelas_id_to_krs',3);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (89,'2026_03_30_130911_alter_kurikulum_mk_prasyarat_fk',4);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (90,'2026_03_30_153827_add_policy_columns_to_akademik_ekuivalensi_table',4);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (91,'2026_04_01_140018_create_lppm_ref_jenis_luarans_table',5);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (92,'2026_04_01_140018_create_lppm_ref_jenis_skemas_table',5);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (93,'2026_04_01_140019_create_lppm_skemas_table',5);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (94,'2026_04_01_140020_create_lppm_usulans_table',5);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (95,'2026_04_01_140021_create_lppm_luarans_table',5);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (96,'2026_04_01_140021_create_lppm_usulan_anggotas_table',5);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (97,'2026_04_01_150325_enrich_lpm_ami_tables',6);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (98,'2026_04_01_150901_enhance_lpm_dokumens_table',7);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (99,'2026_04_01_151129_enhance_lpm_iku_targets',8);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (100,'2026_04_01_151656_enhance_lpm_indikators',9);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (101,'2026_04_03_172446_enhance_maste_kurikulum',10);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (102,'2026_04_07_160348_change_lpm',11);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (103,'2026_04_07_162224_add_column_lpm_kuisioner_kelompok',12);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (104,'2026_04_08_124545_add_automation_to_lpm_indikators',13);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (105,'2026_04_08_124615_enhance_lpm_ami_workflow',13);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (106,'2026_04_08_135926_fix_verified_by_in_lpm_iku_targets',13);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (107,'2026_04_12_141839_add_column_to_ref_ruang_tables',14);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (108,'2026_04_13_021716_create_lpm_edom_saran',14);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (109,'2026_04_16_104440_create_ref_dokumen_dosen_table',14);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (110,'2026_04_17_232652_create_pmb_camaba_staging_table',14);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (111,'2026_04_18_010110_remove_profileable_columns_from_users_table',14);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (112,'2026_04_18_014240_create_bank_kampuses_table',14);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (113,'2026_04_18_105714_add_program_id_to_mahasiswas_table',14);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (114,'2026_04_18_105849_set_all_mahasiswa_to_program_reguler',14);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (115,'2026_04_21_225610_add_penerapan_to_keuangan_detail_tarif_table',14);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (116,'2026_05_01_230348_create_keuangan_master_beasiswas_table',15);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (117,'2026_05_01_230349_create_keuangan_beasiswa_details_table',15);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (118,'2026_05_01_230351_create_keuangan_mahasiswa_beasiswas_table',15);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (119,'2026_05_01_230357_create_tagihan_mahasiswas_details_table',15);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (120,'2026_05_01_230402_create_keuangan_general_ledgers_table',15);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (121,'2026_05_01_230407_add_idempotency_key_to_pembayaran_mahasiswas_table',15);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (122,'2026_05_02_001310_remove_rincian_item_from_tagihan_mahasiswas_table',15);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (123,'2026_05_02_010333_alter_status_verifikasi_on_pembayaran_mahasiswas',15);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (124,'2026_05_02_010758_create_ref_status_verifikasi_pembayaran_table',15);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (125,'2026_05_02_010859_refactor_status_verifikasi_on_pembayaran_mahasiswas',15);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (126,'2026_05_04_121028_create_dispensasi_akademiks_table',16);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (127,'2026_05_04_121037_create_dispensasi_akademik_logs_table',16);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (128,'2026_05_05_133345_add_is_penilai_to_jadwal_kuliah_dosen_table',17);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (129,'2026_05_07_222043_alter_ref_tahun_akademik_add_workflow_fields',17);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (133,'2026_05_07_231434_create_notifications_table',18);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (134,'2026_07_10_003545_create_payment_policies_table',18);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (135,'2026_07_10_003557_create_payment_policy_details_table',19);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (136,'2026_07_10_004019_remove_min_pembayaran_persen_from_program_kelas',20);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (137,'2026_07_10_021420_add_urutan_prioritas_to_keuangan_komponen_biaya_table',21);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (138,'2026_07_10_122043_create_permission_tables',22);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (139,'2026_07_10_123848_change_model_id_to_uuid_in_permission_tables',23);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (140,'2026_07_11_094639_create_activity_log_table',24);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (141,'2026_07_11_100313_alter_activity_log_ids_to_string',25);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (142,'2026_07_11_123201_alter_keuangan_adjustments_add_approval_workflow',26);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (143,'2026_07_12_011906_alter_krs_and_create_krs_status_logs_table',27);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (144,'2026_07_12_205730_alter_ref_skala_nilai_precision',28);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (145,'2026_07_13_161719_add_security_columns_to_presensi_tables',29);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (146,'2022_12_14_083707_create_settings_table',30);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (147,'2026_07_13_175940_create_kampus_settings',30);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (148,'2026_07_14_120923_add_unique_kode_komponen_to_keuangan_komponen_biaya_table',30);
