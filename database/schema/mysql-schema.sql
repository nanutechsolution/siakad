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
  PRIMARY KEY (`id`),
  KEY `idx_mhs_ta` (`mahasiswa_id`,`tahun_akademik_id`),
  KEY `academic_history_logs_tahun_akademik_id_foreign` (`tahun_akademik_id`),
  CONSTRAINT `academic_history_logs_mahasiswa_id_foreign` FOREIGN KEY (`mahasiswa_id`) REFERENCES `mahasiswas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `academic_history_logs_tahun_akademik_id_foreign` FOREIGN KEY (`tahun_akademik_id`) REFERENCES `ref_tahun_akademik` (`id`) ON DELETE CASCADE
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
  KEY `causer` (`causer_type`,`causer_id`),
  KEY `subject` (`subject_type`,`subject_id`),
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
  KEY `dispensasi_akademik_logs_dispensasi_id_foreign` (`dispensasi_id`),
  KEY `dispensasi_akademik_logs_dilakukan_oleh_foreign` (`dilakukan_oleh`),
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
DROP TABLE IF EXISTS `dosen_biodata`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `dosen_biodata` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `dosen_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `alamat_domisili` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `kode_pos` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_hp_kantor` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bidang_keahlian` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `minat_penelitian` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `sinta_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `scopus_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `orcid_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `google_scholar_id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `h_index_scopus` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `h_index_scholar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `agama` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status_pernikahan` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `dosen_biodata_dosen_id_unique` (`dosen_id`),
  CONSTRAINT `dosen_biodata_dosen_id_foreign` FOREIGN KEY (`dosen_id`) REFERENCES `trx_dosen` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `dosen_dokumen`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `dosen_dokumen` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `dosen_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ref_dokumen_dosen_id` bigint unsigned NOT NULL,
  `file_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_file_asli` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ukuran_kb` int unsigned DEFAULT NULL,
  `status` enum('pending','approved','rejected') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `reviewed_by` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `rejection_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `dosen_dokumen_dosen_id_ref_dokumen_dosen_id_unique` (`dosen_id`,`ref_dokumen_dosen_id`),
  KEY `dosen_dokumen_ref_dokumen_dosen_id_foreign` (`ref_dokumen_dosen_id`),
  KEY `dosen_dokumen_reviewed_by_foreign` (`reviewed_by`),
  CONSTRAINT `dosen_dokumen_dosen_id_foreign` FOREIGN KEY (`dosen_id`) REFERENCES `trx_dosen` (`id`) ON DELETE CASCADE,
  CONSTRAINT `dosen_dokumen_ref_dokumen_dosen_id_foreign` FOREIGN KEY (`ref_dokumen_dosen_id`) REFERENCES `ref_dokumen_dosen` (`id`) ON DELETE CASCADE,
  CONSTRAINT `dosen_dokumen_reviewed_by_foreign` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `dosen_profile_change_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `dosen_profile_change_requests` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `dosen_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `field_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `old_value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `new_value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `attachment_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('pending','approved','rejected') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `reviewed_by` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `rejection_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `dosen_profile_change_requests_dosen_id_status_index` (`dosen_id`,`status`),
  KEY `dosen_profile_change_requests_reviewed_by_foreign` (`reviewed_by`),
  CONSTRAINT `dosen_profile_change_requests_dosen_id_foreign` FOREIGN KEY (`dosen_id`) REFERENCES `trx_dosen` (`id`) ON DELETE CASCADE,
  CONSTRAINT `dosen_profile_change_requests_reviewed_by_foreign` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `dosen_riwayat_pendidikan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `dosen_riwayat_pendidikan` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `dosen_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `jenjang` enum('D3','D4','S1','S2','S3','PROFESI') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nama_institusi` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `program_studi` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tahun_lulus` year DEFAULT NULL,
  `judul_tugas_akhir` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_ijazah_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `dosen_riwayat_pendidikan_dosen_id_index` (`dosen_id`),
  CONSTRAINT `dosen_riwayat_pendidikan_dosen_id_foreign` FOREIGN KEY (`dosen_id`) REFERENCES `trx_dosen` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `exports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `exports` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `completed_at` timestamp NULL DEFAULT NULL,
  `file_disk` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `exporter` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `processed_rows` int unsigned NOT NULL DEFAULT '0',
  `total_rows` int unsigned NOT NULL,
  `successful_rows` int unsigned NOT NULL DEFAULT '0',
  `user_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `exports_user_id_foreign` (`user_id`),
  CONSTRAINT `exports_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `failed_import_rows`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_import_rows` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `data` json NOT NULL,
  `import_id` bigint unsigned NOT NULL,
  `validation_error` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `failed_import_rows_import_id_foreign` (`import_id`),
  CONSTRAINT `failed_import_rows_import_id_foreign` FOREIGN KEY (`import_id`) REFERENCES `imports` (`id`) ON DELETE CASCADE
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
DROP TABLE IF EXISTS `generator_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `generator_batches` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tahun_akademik_id` bigint unsigned NOT NULL,
  `status` enum('PROCESSING','COMPLETED','FAILED') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'PROCESSING',
  `parameter_snapshot` json NOT NULL,
  `summary_snapshot` json DEFAULT NULL,
  `total_mahasiswa` int unsigned NOT NULL DEFAULT '0',
  `total_berhasil` int unsigned NOT NULL DEFAULT '0',
  `total_gagal` int unsigned NOT NULL DEFAULT '0',
  `total_skip` int unsigned NOT NULL DEFAULT '0',
  `error_message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `started_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_by` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `generator_batches_created_by_foreign` (`created_by`),
  KEY `generator_batches_tahun_akademik_id_status_index` (`tahun_akademik_id`,`status`),
  KEY `generator_batches_status_created_at_index` (`status`,`created_at`),
  CONSTRAINT `generator_batches_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `generator_batches_tahun_akademik_id_foreign` FOREIGN KEY (`tahun_akademik_id`) REFERENCES `ref_tahun_akademik` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `generator_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `generator_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `generator_batch_id` bigint unsigned NOT NULL,
  `mahasiswa_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('BERHASIL','GAGAL','DILEWATI') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_tagihan` decimal(19,2) DEFAULT NULL,
  `pesan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `generator_logs_generator_batch_id_status_index` (`generator_batch_id`,`status`),
  KEY `generator_logs_mahasiswa_id_created_at_index` (`mahasiswa_id`,`created_at`),
  KEY `generator_logs_status_index` (`status`),
  CONSTRAINT `generator_logs_generator_batch_id_foreign` FOREIGN KEY (`generator_batch_id`) REFERENCES `generator_batches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `generator_logs_mahasiswa_id_foreign` FOREIGN KEY (`mahasiswa_id`) REFERENCES `mahasiswas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `imports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `imports` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `completed_at` timestamp NULL DEFAULT NULL,
  `file_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `importer` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `processed_rows` int unsigned NOT NULL DEFAULT '0',
  `total_rows` int unsigned NOT NULL,
  `successful_rows` int unsigned NOT NULL DEFAULT '0',
  `user_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `imports_user_id_foreign` (`user_id`),
  CONSTRAINT `imports_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
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
  KEY `jadwal_kuliah_kurikulum_id_foreign` (`kurikulum_id`),
  KEY `jadwal_kuliah_mata_kuliah_id_foreign` (`mata_kuliah_id`),
  KEY `jadwal_kuliah_kelas_id_foreign` (`kelas_id`),
  KEY `jadwal_kuliah_ruang_id_foreign` (`ruang_id`),
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
  KEY `jadwal_ujians_jadwal_kuliah_id_foreign` (`jadwal_kuliah_id`),
  KEY `jadwal_ujians_ruang_id_foreign` (`ruang_id`),
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
  KEY `keuangan_adjustments_tagihan_id_status_index` (`tagihan_id`,`status`),
  KEY `keuangan_adjustments_tagihan_id_foreign` (`tagihan_id`),
  KEY `keuangan_adjustments_created_by_foreign` (`created_by`),
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
  UNIQUE KEY `uniq_ledger_referensi_tipe` (`referensi_dokumen`,`tipe_transaksi`),
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
  KEY `keuangan_mahasiswa_beasiswas_mahasiswa_id_is_active_index` (`mahasiswa_id`,`is_active`),
  KEY `keuangan_mahasiswa_beasiswas_beasiswa_id_foreign` (`beasiswa_id`),
  KEY `keuangan_mahasiswa_beasiswas_tahun_akademik_mulai_id_foreign` (`tahun_akademik_mulai_id`),
  KEY `keuangan_mahasiswa_beasiswas_tahun_akademik_akhir_id_foreign` (`tahun_akademik_akhir_id`),
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
DROP TABLE IF EXISTS `konfigurasi_pembimbing_akademik`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `konfigurasi_pembimbing_akademik` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `prodi_id` bigint unsigned NOT NULL,
  `angkatan_id` int NOT NULL,
  `mode` enum('PER_KELAS','PER_MAHASISWA') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'PER_KELAS',
  `aktif` tinyint(1) NOT NULL DEFAULT '1',
  `keterangan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_konfigurasi_pembimbing` (`prodi_id`,`angkatan_id`),
  KEY `konfigurasi_pembimbing_akademik_angkatan_id_foreign` (`angkatan_id`),
  KEY `konfigurasi_pembimbing_akademik_mode_index` (`mode`),
  KEY `konfigurasi_pembimbing_akademik_aktif_index` (`aktif`),
  CONSTRAINT `konfigurasi_pembimbing_akademik_angkatan_id_foreign` FOREIGN KEY (`angkatan_id`) REFERENCES `ref_angkatan` (`id_tahun`) ON DELETE RESTRICT,
  CONSTRAINT `konfigurasi_pembimbing_akademik_prodi_id_foreign` FOREIGN KEY (`prodi_id`) REFERENCES `ref_prodi` (`id`) ON DELETE RESTRICT
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
  KEY `krs_kelas_id_foreign` (`kelas_id`),
  KEY `krs_dosen_wali_id_foreign` (`dosen_wali_id`),
  KEY `krs_disetujui_oleh_foreign` (`disetujui_oleh`),
  KEY `krs_ditolak_oleh_foreign` (`ditolak_oleh`),
  KEY `krs_financial_override_by_foreign` (`financial_override_by`),
  KEY `krs_dispensasi_id_foreign` (`dispensasi_id`),
  KEY `idx_status_ta` (`tahun_akademik_id`,`status_krs`),
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
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `krs_detail_krs_id_jadwal_kuliah_id_unique` (`krs_id`,`jadwal_kuliah_id`),
  UNIQUE KEY `krs_detail_prevent_double_mk` (`krs_id`,`mata_kuliah_id`),
  KEY `krs_detail_jadwal_kuliah_id_foreign` (`jadwal_kuliah_id`),
  KEY `krs_detail_mata_kuliah_id_foreign` (`mata_kuliah_id`),
  KEY `krs_detail_ekuivalensi_id_foreign` (`ekuivalensi_id`),
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
DROP TABLE IF EXISTS `lpm_akreditasi_elemens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lpm_akreditasi_elemens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `kriteria_id` bigint unsigned NOT NULL,
  `kode_elemen` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `deskripsi` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `urutan` int unsigned NOT NULL DEFAULT '1',
  `status_kelengkapan` enum('BELUM','PROSES','LENGKAP') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'BELUM',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lpm_akreditasi_elemens_kriteria_id_urutan_index` (`kriteria_id`,`urutan`),
  CONSTRAINT `lpm_akreditasi_elemens_kriteria_id_foreign` FOREIGN KEY (`kriteria_id`) REFERENCES `lpm_akreditasi_kriterias` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `lpm_akreditasi_evidences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lpm_akreditasi_evidences` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `elemen_id` bigint unsigned NOT NULL,
  `indikator_id` bigint unsigned DEFAULT NULL,
  `file_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `keterangan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uploaded_by_person_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lpm_akreditasi_evidences_elemen_id_foreign` (`elemen_id`),
  KEY `lpm_akreditasi_evidences_indikator_id_foreign` (`indikator_id`),
  KEY `lpm_akreditasi_evidences_uploaded_by_person_id_foreign` (`uploaded_by_person_id`),
  CONSTRAINT `lpm_akreditasi_evidences_elemen_id_foreign` FOREIGN KEY (`elemen_id`) REFERENCES `lpm_akreditasi_elemens` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lpm_akreditasi_evidences_indikator_id_foreign` FOREIGN KEY (`indikator_id`) REFERENCES `lpm_akreditasi_indikators` (`id`) ON DELETE SET NULL,
  CONSTRAINT `lpm_akreditasi_evidences_uploaded_by_person_id_foreign` FOREIGN KEY (`uploaded_by_person_id`) REFERENCES `ref_person` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `lpm_akreditasi_indikators`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lpm_akreditasi_indikators` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `elemen_id` bigint unsigned NOT NULL,
  `deskripsi` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `bobot` decimal(5,2) DEFAULT NULL,
  `indikator_siakad_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lpm_akreditasi_indikators_elemen_id_foreign` (`elemen_id`),
  KEY `lpm_akreditasi_indikators_indikator_siakad_id_foreign` (`indikator_siakad_id`),
  CONSTRAINT `lpm_akreditasi_indikators_elemen_id_foreign` FOREIGN KEY (`elemen_id`) REFERENCES `lpm_akreditasi_elemens` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lpm_akreditasi_indikators_indikator_siakad_id_foreign` FOREIGN KEY (`indikator_siakad_id`) REFERENCES `lpm_indikators` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `lpm_akreditasi_kriterias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lpm_akreditasi_kriterias` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `akreditasi_id` bigint unsigned NOT NULL,
  `kode_kriteria` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_kriteria` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `urutan` int unsigned NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lpm_akreditasi_kriterias_akreditasi_id_urutan_index` (`akreditasi_id`,`urutan`),
  CONSTRAINT `lpm_akreditasi_kriterias_akreditasi_id_foreign` FOREIGN KEY (`akreditasi_id`) REFERENCES `lpm_akreditasis` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `lpm_akreditasi_lembagas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lpm_akreditasi_lembagas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `kode` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `jenis` enum('INSTITUSI','PRODI') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'PRODI',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `lpm_akreditasi_lembagas_kode_unique` (`kode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `lpm_akreditasis`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lpm_akreditasis` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `lembaga_id` bigint unsigned NOT NULL,
  `prodi_id` bigint unsigned DEFAULT NULL,
  `jenis_akreditasi` enum('INSTITUSI','PRODI') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `instrumen` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'mis. IAPS 4.0, IAPT 3.0',
  `status` enum('PERSIAPAN','PENGISIAN','SUBMIT','VISITASI','SELESAI') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'PERSIAPAN',
  `peringkat_target` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `peringkat_hasil` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tanggal_submit` date DEFAULT NULL,
  `tanggal_visitasi` date DEFAULT NULL,
  `berlaku_sampai` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lpm_akreditasis_lembaga_id_foreign` (`lembaga_id`),
  KEY `lpm_akreditasis_prodi_id_foreign` (`prodi_id`),
  KEY `lpm_akreditasis_jenis_akreditasi_status_index` (`jenis_akreditasi`,`status`),
  CONSTRAINT `lpm_akreditasis_lembaga_id_foreign` FOREIGN KEY (`lembaga_id`) REFERENCES `lpm_akreditasi_lembagas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lpm_akreditasis_prodi_id_foreign` FOREIGN KEY (`prodi_id`) REFERENCES `ref_prodi` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `lpm_ami_checklist_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lpm_ami_checklist_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `checklist_id` bigint unsigned NOT NULL,
  `pertanyaan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `urutan` int unsigned NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lpm_ami_checklist_items_checklist_id_urutan_index` (`checklist_id`,`urutan`),
  CONSTRAINT `lpm_ami_checklist_items_checklist_id_foreign` FOREIGN KEY (`checklist_id`) REFERENCES `lpm_ami_checklists` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `lpm_ami_checklist_jawabans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lpm_ami_checklist_jawabans` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `program_id` bigint unsigned NOT NULL,
  `checklist_item_id` bigint unsigned NOT NULL,
  `jawaban` enum('SESUAI','TIDAK_SESUAI','OBSERVASI') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `catatan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `finding_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_jawaban_per_program_item` (`program_id`,`checklist_item_id`),
  KEY `lpm_ami_checklist_jawabans_checklist_item_id_foreign` (`checklist_item_id`),
  KEY `lpm_ami_checklist_jawabans_finding_id_foreign` (`finding_id`),
  CONSTRAINT `lpm_ami_checklist_jawabans_checklist_item_id_foreign` FOREIGN KEY (`checklist_item_id`) REFERENCES `lpm_ami_checklist_items` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lpm_ami_checklist_jawabans_finding_id_foreign` FOREIGN KEY (`finding_id`) REFERENCES `lpm_ami_findings` (`id`) ON DELETE SET NULL,
  CONSTRAINT `lpm_ami_checklist_jawabans_program_id_foreign` FOREIGN KEY (`program_id`) REFERENCES `lpm_ami_programs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `lpm_ami_checklists`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lpm_ami_checklists` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `standar_id` bigint unsigned NOT NULL,
  `kriteria` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `urutan` int unsigned NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lpm_ami_checklists_standar_id_urutan_index` (`standar_id`,`urutan`),
  CONSTRAINT `lpm_ami_checklists_standar_id_foreign` FOREIGN KEY (`standar_id`) REFERENCES `lpm_standars` (`id`) ON DELETE CASCADE
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
DROP TABLE IF EXISTS `lpm_ami_evidences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lpm_ami_evidences` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `checklist_jawaban_id` bigint unsigned DEFAULT NULL,
  `finding_id` bigint unsigned DEFAULT NULL,
  `file_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `keterangan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uploaded_by_person_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lpm_ami_evidences_checklist_jawaban_id_foreign` (`checklist_jawaban_id`),
  KEY `lpm_ami_evidences_finding_id_foreign` (`finding_id`),
  KEY `lpm_ami_evidences_uploaded_by_person_id_foreign` (`uploaded_by_person_id`),
  CONSTRAINT `lpm_ami_evidences_checklist_jawaban_id_foreign` FOREIGN KEY (`checklist_jawaban_id`) REFERENCES `lpm_ami_checklist_jawabans` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lpm_ami_evidences_finding_id_foreign` FOREIGN KEY (`finding_id`) REFERENCES `lpm_ami_findings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lpm_ami_evidences_uploaded_by_person_id_foreign` FOREIGN KEY (`uploaded_by_person_id`) REFERENCES `ref_person` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `lpm_ami_findings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lpm_ami_findings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `periode_id` bigint unsigned NOT NULL,
  `program_id` bigint unsigned DEFAULT NULL,
  `prodi_id` bigint unsigned NOT NULL,
  `jenis_temuan` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'OBSERVASI' COMMENT 'MAYOR, MINOR, OBSERVASI',
  `standar_id` bigint unsigned NOT NULL,
  `auditor_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `auditor_id` bigint unsigned DEFAULT NULL,
  `klasifikasi` enum('OB','KTS_MINOR','KTS_MAYOR') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `deskripsi_temuan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `rekomendasi` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `akar_masalah` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `rencana_tindak_lanjut` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `preventive_action` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `deadline_perbaikan` date DEFAULT NULL,
  `is_closed` tinyint(1) NOT NULL DEFAULT '0',
  `status_workflow` enum('OPEN','ACTION_PLAN','VERIFICATION','CLOSED') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'OPEN',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lpm_ami_findings_periode_id_foreign` (`periode_id`),
  KEY `lpm_ami_findings_prodi_id_foreign` (`prodi_id`),
  KEY `lpm_ami_findings_standar_id_foreign` (`standar_id`),
  KEY `lpm_ami_findings_program_id_foreign` (`program_id`),
  KEY `lpm_ami_findings_auditor_id_foreign` (`auditor_id`),
  CONSTRAINT `lpm_ami_findings_auditor_id_foreign` FOREIGN KEY (`auditor_id`) REFERENCES `lpm_auditors` (`id`) ON DELETE SET NULL,
  CONSTRAINT `lpm_ami_findings_periode_id_foreign` FOREIGN KEY (`periode_id`) REFERENCES `lpm_ami_periodes` (`id`),
  CONSTRAINT `lpm_ami_findings_prodi_id_foreign` FOREIGN KEY (`prodi_id`) REFERENCES `ref_prodi` (`id`),
  CONSTRAINT `lpm_ami_findings_program_id_foreign` FOREIGN KEY (`program_id`) REFERENCES `lpm_ami_programs` (`id`) ON DELETE SET NULL,
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
DROP TABLE IF EXISTS `lpm_ami_program_auditors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lpm_ami_program_auditors` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `program_id` bigint unsigned NOT NULL,
  `auditor_id` bigint unsigned NOT NULL,
  `peran` enum('KETUA_TIM','ANGGOTA') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ANGGOTA',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `lpm_ami_program_auditors_program_id_auditor_id_unique` (`program_id`,`auditor_id`),
  KEY `lpm_ami_program_auditors_auditor_id_foreign` (`auditor_id`),
  CONSTRAINT `lpm_ami_program_auditors_auditor_id_foreign` FOREIGN KEY (`auditor_id`) REFERENCES `lpm_auditors` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lpm_ami_program_auditors_program_id_foreign` FOREIGN KEY (`program_id`) REFERENCES `lpm_ami_programs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `lpm_ami_programs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lpm_ami_programs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `periode_id` bigint unsigned NOT NULL,
  `unit_kerja_id` bigint unsigned NOT NULL,
  `tanggal_pelaksanaan` date DEFAULT NULL,
  `status` enum('DIJADWALKAN','BERLANGSUNG','SELESAI') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'DIJADWALKAN',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lpm_ami_programs_unit_kerja_id_foreign` (`unit_kerja_id`),
  KEY `lpm_ami_programs_periode_id_unit_kerja_id_index` (`periode_id`,`unit_kerja_id`),
  CONSTRAINT `lpm_ami_programs_periode_id_foreign` FOREIGN KEY (`periode_id`) REFERENCES `lpm_ami_periodes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lpm_ami_programs_unit_kerja_id_foreign` FOREIGN KEY (`unit_kerja_id`) REFERENCES `lpm_unit_kerjas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `lpm_auditors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lpm_auditors` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `person_id` bigint unsigned NOT NULL,
  `no_sertifikat_auditor` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kompetensi` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `lpm_auditors_person_id_unique` (`person_id`),
  CONSTRAINT `lpm_auditors_person_id_foreign` FOREIGN KEY (`person_id`) REFERENCES `ref_person` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `lpm_benchmark_institusis`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lpm_benchmark_institusis` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nama_institusi` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `jenis` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'mis. PTN, PTS, Internasional',
  `catatan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `lpm_benchmarks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lpm_benchmarks` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `indikator_id` bigint unsigned NOT NULL,
  `institusi_pembanding_id` bigint unsigned NOT NULL,
  `tahun` smallint unsigned NOT NULL,
  `nilai_internal` decimal(10,2) DEFAULT NULL,
  `nilai_eksternal` decimal(10,2) DEFAULT NULL,
  `analisis_gap` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `sumber_data` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_benchmark_per_tahun` (`indikator_id`,`institusi_pembanding_id`,`tahun`),
  KEY `lpm_benchmarks_institusi_pembanding_id_foreign` (`institusi_pembanding_id`),
  CONSTRAINT `lpm_benchmarks_indikator_id_foreign` FOREIGN KEY (`indikator_id`) REFERENCES `lpm_indikators` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lpm_benchmarks_institusi_pembanding_id_foreign` FOREIGN KEY (`institusi_pembanding_id`) REFERENCES `lpm_benchmark_institusis` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `lpm_bukti_pelaksanaans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lpm_bukti_pelaksanaans` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `iku_target_id` bigint unsigned DEFAULT NULL,
  `finding_id` bigint unsigned DEFAULT NULL,
  `unit_kerja_id` bigint unsigned NOT NULL,
  `judul` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `keterangan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `uploaded_by_person_id` bigint unsigned DEFAULT NULL,
  `tanggal` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lpm_bukti_pelaksanaans_finding_id_foreign` (`finding_id`),
  KEY `lpm_bukti_pelaksanaans_unit_kerja_id_foreign` (`unit_kerja_id`),
  KEY `lpm_bukti_pelaksanaans_uploaded_by_person_id_foreign` (`uploaded_by_person_id`),
  KEY `lpm_bukti_pelaksanaans_iku_target_id_finding_id_index` (`iku_target_id`,`finding_id`),
  CONSTRAINT `lpm_bukti_pelaksanaans_finding_id_foreign` FOREIGN KEY (`finding_id`) REFERENCES `lpm_ami_findings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lpm_bukti_pelaksanaans_iku_target_id_foreign` FOREIGN KEY (`iku_target_id`) REFERENCES `lpm_iku_targets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lpm_bukti_pelaksanaans_unit_kerja_id_foreign` FOREIGN KEY (`unit_kerja_id`) REFERENCES `lpm_unit_kerjas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lpm_bukti_pelaksanaans_uploaded_by_person_id_foreign` FOREIGN KEY (`uploaded_by_person_id`) REFERENCES `ref_person` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `lpm_dokumen_approvals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lpm_dokumen_approvals` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `dokumen_id` bigint unsigned NOT NULL,
  `person_id` bigint unsigned NOT NULL,
  `peran` enum('PENYUSUN','PEMERIKSA','PENGESAH') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('PENDING','APPROVED','REJECTED') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'PENDING',
  `catatan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lpm_dokumen_approvals_person_id_foreign` (`person_id`),
  KEY `lpm_dokumen_approvals_dokumen_id_peran_index` (`dokumen_id`,`peran`),
  CONSTRAINT `lpm_dokumen_approvals_dokumen_id_foreign` FOREIGN KEY (`dokumen_id`) REFERENCES `lpm_dokumens` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lpm_dokumen_approvals_person_id_foreign` FOREIGN KEY (`person_id`) REFERENCES `ref_person` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `lpm_dokumen_riwayats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lpm_dokumen_riwayats` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `dokumen_id` bigint unsigned NOT NULL,
  `versi_lama` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `versi_baru` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `changelog` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `diubah_oleh_person_id` bigint unsigned DEFAULT NULL,
  `tanggal` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lpm_dokumen_riwayats_diubah_oleh_person_id_foreign` (`diubah_oleh_person_id`),
  KEY `lpm_dokumen_riwayats_dokumen_id_tanggal_index` (`dokumen_id`,`tanggal`),
  CONSTRAINT `lpm_dokumen_riwayats_diubah_oleh_person_id_foreign` FOREIGN KEY (`diubah_oleh_person_id`) REFERENCES `ref_person` (`id`) ON DELETE SET NULL,
  CONSTRAINT `lpm_dokumen_riwayats_dokumen_id_foreign` FOREIGN KEY (`dokumen_id`) REFERENCES `lpm_dokumens` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `lpm_dokumens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lpm_dokumens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `kode_dokumen` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nama_dokumen` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `jenis` enum('KEBIJAKAN','MANUAL','STANDAR','FORMULIR','SOP','DOKUMEN_PENDUKUNG') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `prodi_id` bigint unsigned DEFAULT NULL,
  `unit_kerja_id` bigint unsigned DEFAULT NULL,
  `standar_id` bigint unsigned DEFAULT NULL,
  `file_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `deskripsi` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `versi` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1.0',
  `status` enum('DRAFT','REVIEW','PUBLISHED','ARCHIVED') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'DRAFT',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `tgl_berlaku` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `lpm_dokumens_kode_dokumen_unique` (`kode_dokumen`),
  KEY `lpm_dokumens_unit_kerja_id_foreign` (`unit_kerja_id`),
  KEY `lpm_dokumens_standar_id_foreign` (`standar_id`),
  CONSTRAINT `lpm_dokumens_standar_id_foreign` FOREIGN KEY (`standar_id`) REFERENCES `lpm_standars` (`id`) ON DELETE SET NULL,
  CONSTRAINT `lpm_dokumens_unit_kerja_id_foreign` FOREIGN KEY (`unit_kerja_id`) REFERENCES `lpm_unit_kerjas` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `lpm_edom_jawaban`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lpm_edom_jawaban` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `jadwal_kuliah_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `pertanyaan_id` bigint unsigned NOT NULL,
  `dosen_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jawaban_nilai` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Bisa skor angka atau isian teks/esai',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_edom_jawaban_agregasi` (`dosen_id`,`jadwal_kuliah_id`,`pertanyaan_id`),
  KEY `lpm_edom_jawaban_pertanyaan_id_foreign` (`pertanyaan_id`),
  KEY `idx_edom_pertanyaan` (`pertanyaan_id`),
  CONSTRAINT `lpm_edom_jawaban_dosen_id_foreign` FOREIGN KEY (`dosen_id`) REFERENCES `trx_dosen` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lpm_edom_jawaban_pertanyaan_id_foreign` FOREIGN KEY (`pertanyaan_id`) REFERENCES `lpm_kuisioner_pertanyaan` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `lpm_edom_progress`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lpm_edom_progress` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `mahasiswa_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `jadwal_kuliah_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `dosen_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_completed` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_mhs_jadwal_dosen_edom` (`mahasiswa_id`,`jadwal_kuliah_id`,`dosen_id`),
  KEY `lpm_edom_progress_jadwal_kuliah_id_foreign` (`jadwal_kuliah_id`),
  KEY `lpm_edom_progress_dosen_id_foreign` (`dosen_id`),
  CONSTRAINT `lpm_edom_progress_dosen_id_foreign` FOREIGN KEY (`dosen_id`) REFERENCES `trx_dosen` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lpm_edom_progress_jadwal_kuliah_id_foreign` FOREIGN KEY (`jadwal_kuliah_id`) REFERENCES `jadwal_kuliah` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lpm_edom_progress_mahasiswa_id_foreign` FOREIGN KEY (`mahasiswa_id`) REFERENCES `mahasiswas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `lpm_edom_saran`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lpm_edom_saran` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `jadwal_kuliah_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `dosen_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `catatan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_edom_saran_jadwal_dosen` (`jadwal_kuliah_id`,`dosen_id`),
  KEY `lpm_edom_saran_dosen_id_foreign` (`dosen_id`),
  CONSTRAINT `lpm_edom_saran_dosen_id_foreign` FOREIGN KEY (`dosen_id`) REFERENCES `trx_dosen` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lpm_edom_saran_jadwal_kuliah_id_foreign` FOREIGN KEY (`jadwal_kuliah_id`) REFERENCES `jadwal_kuliah` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `lpm_iku_targets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lpm_iku_targets` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `indikator_id` bigint unsigned NOT NULL,
  `prodi_id` bigint unsigned DEFAULT NULL,
  `unit_kerja_id` bigint unsigned DEFAULT NULL,
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
  KEY `lpm_iku_targets_unit_kerja_id_foreign` (`unit_kerja_id`),
  CONSTRAINT `lpm_iku_targets_indikator_id_foreign` FOREIGN KEY (`indikator_id`) REFERENCES `lpm_indikators` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lpm_iku_targets_unit_kerja_id_foreign` FOREIGN KEY (`unit_kerja_id`) REFERENCES `lpm_unit_kerjas` (`id`) ON DELETE SET NULL
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
DROP TABLE IF EXISTS `lpm_kategori_standars`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lpm_kategori_standars` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `kode` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `urutan` int unsigned NOT NULL DEFAULT '1',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `lpm_kategori_standars_kode_unique` (`kode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `lpm_kuisioner_kelompok`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lpm_kuisioner_kelompok` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tahun_akademik_id` bigint unsigned DEFAULT NULL,
  `nama_kelompok` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `kategori` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'EDOM',
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
DROP TABLE IF EXISTS `lpm_riwayat_peningkatans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lpm_riwayat_peningkatans` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `standar_id` bigint unsigned NOT NULL,
  `versi_lama` int unsigned NOT NULL,
  `versi_baru` int unsigned NOT NULL,
  `ringkasan_perubahan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `dasar_peningkatan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'mis. Hasil AMI, Hasil Monev, Tinjauan Manajemen',
  `tanggal` date NOT NULL,
  `disetujui_oleh_person_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lpm_riwayat_peningkatans_disetujui_oleh_person_id_foreign` (`disetujui_oleh_person_id`),
  KEY `lpm_riwayat_peningkatans_standar_id_tanggal_index` (`standar_id`,`tanggal`),
  CONSTRAINT `lpm_riwayat_peningkatans_disetujui_oleh_person_id_foreign` FOREIGN KEY (`disetujui_oleh_person_id`) REFERENCES `ref_person` (`id`) ON DELETE SET NULL,
  CONSTRAINT `lpm_riwayat_peningkatans_standar_id_foreign` FOREIGN KEY (`standar_id`) REFERENCES `lpm_standars` (`id`) ON DELETE CASCADE
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
  `kategori_standar_id` bigint unsigned DEFAULT NULL,
  `pernyataan_standar` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `target_pencapaian` int NOT NULL DEFAULT '100',
  `satuan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '%',
  `versi` int NOT NULL DEFAULT '1',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `lpm_standars_kode_versi_unique` (`kode_standar`,`versi`),
  KEY `lpm_standars_kategori_standar_id_foreign` (`kategori_standar_id`),
  CONSTRAINT `lpm_standars_kategori_standar_id_foreign` FOREIGN KEY (`kategori_standar_id`) REFERENCES `lpm_kategori_standars` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `lpm_survey_analisis`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lpm_survey_analisis` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `kelompok_id` bigint unsigned NOT NULL,
  `tahun_akademik_id` bigint unsigned DEFAULT NULL,
  `unit_kerja_id` bigint unsigned DEFAULT NULL,
  `rata_rata_skor` decimal(5,2) DEFAULT NULL,
  `kesimpulan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `rencana_perbaikan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `disusun_oleh_person_id` bigint unsigned DEFAULT NULL,
  `tanggal` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lpm_survey_analisis_kelompok_id_foreign` (`kelompok_id`),
  KEY `lpm_survey_analisis_tahun_akademik_id_foreign` (`tahun_akademik_id`),
  KEY `lpm_survey_analisis_unit_kerja_id_foreign` (`unit_kerja_id`),
  KEY `lpm_survey_analisis_disusun_oleh_person_id_foreign` (`disusun_oleh_person_id`),
  CONSTRAINT `lpm_survey_analisis_disusun_oleh_person_id_foreign` FOREIGN KEY (`disusun_oleh_person_id`) REFERENCES `ref_person` (`id`) ON DELETE SET NULL,
  CONSTRAINT `lpm_survey_analisis_kelompok_id_foreign` FOREIGN KEY (`kelompok_id`) REFERENCES `lpm_kuisioner_kelompok` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lpm_survey_analisis_tahun_akademik_id_foreign` FOREIGN KEY (`tahun_akademik_id`) REFERENCES `ref_tahun_akademik` (`id`) ON DELETE SET NULL,
  CONSTRAINT `lpm_survey_analisis_unit_kerja_id_foreign` FOREIGN KEY (`unit_kerja_id`) REFERENCES `lpm_unit_kerjas` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `lpm_survey_jawaban`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lpm_survey_jawaban` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `mahasiswa_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `pertanyaan_id` bigint unsigned NOT NULL,
  `tahun_akademik_id` bigint unsigned NOT NULL,
  `jawaban_nilai` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_survey_mhs_ta` (`mahasiswa_id`,`pertanyaan_id`,`tahun_akademik_id`),
  KEY `lpm_survey_jawaban_pertanyaan_id_foreign` (`pertanyaan_id`),
  KEY `lpm_survey_jawaban_tahun_akademik_id_foreign` (`tahun_akademik_id`),
  CONSTRAINT `lpm_survey_jawaban_mahasiswa_id_foreign` FOREIGN KEY (`mahasiswa_id`) REFERENCES `mahasiswas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lpm_survey_jawaban_pertanyaan_id_foreign` FOREIGN KEY (`pertanyaan_id`) REFERENCES `lpm_kuisioner_pertanyaan` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lpm_survey_jawaban_tahun_akademik_id_foreign` FOREIGN KEY (`tahun_akademik_id`) REFERENCES `ref_tahun_akademik` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `lpm_survey_jawaban_pihak`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lpm_survey_jawaban_pihak` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `jenis_responden` enum('DOSEN','TENDIK','ALUMNI','PENGGUNA_LULUSAN') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `person_id` bigint unsigned DEFAULT NULL,
  `nama_eksternal` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `instansi_eksternal` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pertanyaan_id` bigint unsigned NOT NULL,
  `tahun_akademik_id` bigint unsigned NOT NULL,
  `jawaban_nilai` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_survey_pihak_person_ta` (`person_id`,`pertanyaan_id`,`tahun_akademik_id`),
  KEY `lpm_survey_jawaban_pihak_pertanyaan_id_foreign` (`pertanyaan_id`),
  KEY `lpm_survey_jawaban_pihak_tahun_akademik_id_foreign` (`tahun_akademik_id`),
  KEY `lpm_survey_jawaban_pihak_jenis_responden_tahun_akademik_id_index` (`jenis_responden`,`tahun_akademik_id`),
  CONSTRAINT `lpm_survey_jawaban_pihak_person_id_foreign` FOREIGN KEY (`person_id`) REFERENCES `ref_person` (`id`) ON DELETE SET NULL,
  CONSTRAINT `lpm_survey_jawaban_pihak_pertanyaan_id_foreign` FOREIGN KEY (`pertanyaan_id`) REFERENCES `lpm_kuisioner_pertanyaan` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lpm_survey_jawaban_pihak_tahun_akademik_id_foreign` FOREIGN KEY (`tahun_akademik_id`) REFERENCES `ref_tahun_akademik` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `lpm_unit_kerjas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lpm_unit_kerjas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` bigint unsigned DEFAULT NULL,
  `jenis_unit` enum('UNIVERSITAS','FAKULTAS','PRODI','LEMBAGA','BIRO','UPT') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `kode_unit` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_unit` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `fakultas_id` bigint unsigned DEFAULT NULL,
  `prodi_id` bigint unsigned DEFAULT NULL,
  `kepala_unit_person_id` bigint unsigned DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `lpm_unit_kerjas_kode_unit_unique` (`kode_unit`),
  KEY `lpm_unit_kerjas_parent_id_foreign` (`parent_id`),
  KEY `lpm_unit_kerjas_fakultas_id_foreign` (`fakultas_id`),
  KEY `lpm_unit_kerjas_prodi_id_foreign` (`prodi_id`),
  KEY `lpm_unit_kerjas_kepala_unit_person_id_foreign` (`kepala_unit_person_id`),
  CONSTRAINT `lpm_unit_kerjas_fakultas_id_foreign` FOREIGN KEY (`fakultas_id`) REFERENCES `ref_fakultas` (`id`) ON DELETE SET NULL,
  CONSTRAINT `lpm_unit_kerjas_kepala_unit_person_id_foreign` FOREIGN KEY (`kepala_unit_person_id`) REFERENCES `ref_person` (`id`) ON DELETE SET NULL,
  CONSTRAINT `lpm_unit_kerjas_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `lpm_unit_kerjas` (`id`) ON DELETE SET NULL,
  CONSTRAINT `lpm_unit_kerjas_prodi_id_foreign` FOREIGN KEY (`prodi_id`) REFERENCES `ref_prodi` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `lpm_unit_pics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lpm_unit_pics` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `unit_kerja_id` bigint unsigned NOT NULL,
  `person_id` bigint unsigned NOT NULL,
  `peran` enum('KETUA','SEKRETARIS','GKM','AUDITOR','ANGGOTA') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tanggal_mulai` date NOT NULL,
  `tanggal_selesai` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lpm_unit_pics_person_id_foreign` (`person_id`),
  KEY `lpm_unit_pics_unit_kerja_id_peran_index` (`unit_kerja_id`,`peran`),
  CONSTRAINT `lpm_unit_pics_person_id_foreign` FOREIGN KEY (`person_id`) REFERENCES `ref_person` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lpm_unit_pics_unit_kerja_id_foreign` FOREIGN KEY (`unit_kerja_id`) REFERENCES `lpm_unit_kerjas` (`id`) ON DELETE CASCADE
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
  KEY `lppm_luarans_dosen_id_foreign` (`dosen_id`),
  KEY `lppm_luarans_jenis_luaran_id_foreign` (`jenis_luaran_id`),
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
DROP TABLE IF EXISTS `mahasiswa_biodata`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mahasiswa_biodata` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `mahasiswa_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `alamat_ktp` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `alamat_domisili` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `kode_pos` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nama_ayah` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nik_ayah` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pendidikan_ayah` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pekerjaan_ayah` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `penghasilan_ayah` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nama_ibu` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nik_ibu` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pendidikan_ibu` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pekerjaan_ibu` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `penghasilan_ibu` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nama_wali` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hubungan_wali` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pekerjaan_wali` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_hp_wali` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `agama` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status_pernikahan` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `anak_ke` int unsigned DEFAULT NULL,
  `jumlah_saudara` int unsigned DEFAULT NULL,
  `no_kip` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mahasiswa_biodata_mahasiswa_id_unique` (`mahasiswa_id`),
  CONSTRAINT `mahasiswa_biodata_mahasiswa_id_foreign` FOREIGN KEY (`mahasiswa_id`) REFERENCES `mahasiswas` (`id`) ON DELETE CASCADE
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
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`dbuser`@`%`*/ /*!50003 TRIGGER `trg_mahasiswa_kelas_biu` BEFORE INSERT ON `mahasiswa_kelas` FOR EACH ROW BEGIN
                IF NEW.tanggal_keluar IS NULL AND EXISTS (
                    SELECT 1 FROM mahasiswa_kelas
                    WHERE mahasiswa_id = NEW.mahasiswa_id AND tanggal_keluar IS NULL
                ) THEN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Mahasiswa ini masih memiliki kelas aktif lain. Isi tanggal_keluar pada baris sebelumnya terlebih dahulu.';
                END IF;
            END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`dbuser`@`%`*/ /*!50003 TRIGGER `trg_mahasiswa_kelas_bu` BEFORE UPDATE ON `mahasiswa_kelas` FOR EACH ROW BEGIN
                IF NEW.tanggal_keluar IS NULL AND EXISTS (
                    SELECT 1 FROM mahasiswa_kelas
                    WHERE mahasiswa_id = NEW.mahasiswa_id AND tanggal_keluar IS NULL AND id <> NEW.id
                ) THEN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Mahasiswa ini masih memiliki kelas aktif lain. Isi tanggal_keluar pada baris sebelumnya terlebih dahulu.';
                END IF;
            END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
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
  `id_pd_feeder` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_synced_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mahasiswas_nim_unique` (`nim`),
  KEY `mahasiswas_person_id_foreign` (`person_id`),
  KEY `idx_mhs_nim` (`nim`),
  KEY `mahasiswas_angkatan_id_foreign` (`angkatan_id`),
  KEY `mahasiswas_prodi_id_foreign` (`prodi_id`),
  KEY `mahasiswas_program_id_foreign` (`program_id`),
  KEY `mahasiswas_kurikulum_id_foreign` (`kurikulum_id`),
  KEY `mahasiswas_id_pd_feeder_index` (`id_pd_feeder`),
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
  `mode_krs` enum('PAKET','BEBAS') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'PAKET' COMMENT 'PAKET: MK ditentukan kurikulum via kelas, GATE SKS berbasis IPS di-skip. BEBAS: mahasiswa pilih sendiri, tunduk GATE SKS Maksimal berbasis IPS.',
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
DROP TABLE IF EXISTS `midtrans_gateway_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `midtrans_gateway_logs` (
  `id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `order_id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `transaction_status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payload` json NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `midtrans_gateway_logs_order_id_index` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `midtrans_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `midtrans_transactions` (
  `id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `order_id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tagihan_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tagihan_type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `mahasiswa_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nominal` decimal(19,2) NOT NULL,
  `snap_token` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `midtrans_transactions_order_id_unique` (`order_id`),
  KEY `midtrans_transactions_tagihan_type_tagihan_id_index` (`tagihan_type`,`tagihan_id`),
  KEY `midtrans_transactions_mahasiswa_id_index` (`mahasiswa_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `migration_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migration_batches` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `source` enum('EXCEL','CSV','NEO_DATABASE','NEO_API') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('PROCESSING','COMPLETED','FAILED') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'PROCESSING',
  `file_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `parameter_snapshot` json NOT NULL,
  `summary_snapshot` json DEFAULT NULL,
  `total_rows` int unsigned NOT NULL DEFAULT '0',
  `total_berhasil` int unsigned NOT NULL DEFAULT '0',
  `total_gagal` int unsigned NOT NULL DEFAULT '0',
  `total_dilewati` int unsigned NOT NULL DEFAULT '0',
  `error_message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `started_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_by` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `migration_batches_created_by_foreign` (`created_by`),
  KEY `migration_batches_status_created_at_index` (`status`,`created_at`),
  KEY `migration_batches_source_status_index` (`source`,`status`),
  CONSTRAINT `migration_batches_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `migration_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migration_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `migration_batch_id` bigint unsigned NOT NULL,
  `row_number` int unsigned NOT NULL,
  `nim` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mahasiswa_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `krs_detail_id` bigint unsigned DEFAULT NULL,
  `status` enum('BERHASIL','GAGAL','DILEWATI') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `pesan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `row_data` json NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `migration_logs_krs_detail_id_foreign` (`krs_detail_id`),
  KEY `migration_logs_migration_batch_id_status_index` (`migration_batch_id`,`status`),
  KEY `migration_logs_mahasiswa_id_created_at_index` (`mahasiswa_id`,`created_at`),
  KEY `migration_logs_status_index` (`status`),
  CONSTRAINT `migration_logs_krs_detail_id_foreign` FOREIGN KEY (`krs_detail_id`) REFERENCES `krs_detail` (`id`) ON DELETE SET NULL,
  CONSTRAINT `migration_logs_mahasiswa_id_foreign` FOREIGN KEY (`mahasiswa_id`) REFERENCES `mahasiswas` (`id`) ON DELETE SET NULL,
  CONSTRAINT `migration_logs_migration_batch_id_foreign` FOREIGN KEY (`migration_batch_id`) REFERENCES `migration_batches` (`id`) ON DELETE CASCADE
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
DROP TABLE IF EXISTS `pdf_documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pdf_documents` (
  `id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `document_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `classification` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `documentable_type` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `documentable_id` varchar(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nomor_dokumen` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_disk` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'local',
  `file_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_hash` char(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `version` int unsigned NOT NULL DEFAULT '1',
  `is_current` tinyint(1) NOT NULL DEFAULT '1',
  `status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `metadata` json DEFAULT NULL,
  `generated_by` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `generated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pdf_documents_nomor_dokumen_unique` (`nomor_dokumen`),
  KEY `idx_pdfdoc_lookup` (`document_type`,`documentable_type`,`documentable_id`,`is_current`),
  KEY `pdf_documents_generated_by_foreign` (`generated_by`),
  CONSTRAINT `pdf_documents_generated_by_foreign` FOREIGN KEY (`generated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pdf_number_sequences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pdf_number_sequences` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `document_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `kode_unit` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `periode_tahun` smallint unsigned NOT NULL,
  `last_sequence` int unsigned NOT NULL DEFAULT '0',
  `format_template` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_pdf_number_sequence` (`document_type`,`kode_unit`,`periode_tahun`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pdf_signature_authorities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pdf_signature_authorities` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `document_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `jabatan_id` bigint unsigned NOT NULL,
  `scope` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'NONE',
  `urutan` tinyint unsigned NOT NULL DEFAULT '1',
  `label` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pdf_signature_authorities_jabatan_id_foreign` (`jabatan_id`),
  KEY `idx_signature_authority_lookup` (`document_type`,`is_active`,`urutan`),
  CONSTRAINT `pdf_signature_authorities_jabatan_id_foreign` FOREIGN KEY (`jabatan_id`) REFERENCES `ref_jabatan` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pdf_signatures`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pdf_signatures` (
  `id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `pdf_document_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `signature_authority_id` bigint unsigned NOT NULL,
  `person_id` bigint unsigned NOT NULL,
  `nama_penandatangan_snapshot` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `jabatan_snapshot` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `urutan` tinyint unsigned NOT NULL DEFAULT '1',
  `document_hash_at_signing` char(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `signed_at` timestamp NOT NULL,
  `status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'signed',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pdf_signatures_signature_authority_id_foreign` (`signature_authority_id`),
  KEY `pdf_signatures_person_id_foreign` (`person_id`),
  KEY `idx_pdf_signatures_document` (`pdf_document_id`,`urutan`),
  CONSTRAINT `pdf_signatures_pdf_document_id_foreign` FOREIGN KEY (`pdf_document_id`) REFERENCES `pdf_documents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pdf_signatures_person_id_foreign` FOREIGN KEY (`person_id`) REFERENCES `ref_person` (`id`),
  CONSTRAINT `pdf_signatures_signature_authority_id_foreign` FOREIGN KEY (`signature_authority_id`) REFERENCES `pdf_signature_authorities` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pdf_verifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pdf_verifications` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `pdf_document_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nomor_dokumen_diminta` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ditemukan` tinyint(1) NOT NULL DEFAULT '0',
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pdf_verifications_pdf_document_id_foreign` (`pdf_document_id`),
  KEY `pdf_verifications_nomor_dokumen_diminta_index` (`nomor_dokumen_diminta`),
  CONSTRAINT `pdf_verifications_pdf_document_id_foreign` FOREIGN KEY (`pdf_document_id`) REFERENCES `pdf_documents` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pembayaran_mahasiswas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pembayaran_mahasiswas` (
  `id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `idempotency_key` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tagihan_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tagihan_type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
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
  KEY `pembayaran_mahasiswas_status_verifikasi_id_index` (`status_verifikasi_id`),
  KEY `pembayaran_mahasiswas_verified_by_foreign` (`verified_by`),
  KEY `pembayaran_mahasiswas_tagihan_type_id_index` (`tagihan_type`,`tagihan_id`),
  CONSTRAINT `pembayaran_mahasiswas_status_verifikasi_id_foreign` FOREIGN KEY (`status_verifikasi_id`) REFERENCES `ref_status_verifikasi_pembayaran` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `pembayaran_mahasiswas_verified_by_foreign` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pembimbing_akademik`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pembimbing_akademik` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `kelas_id` bigint unsigned DEFAULT NULL,
  `mahasiswa_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dosen_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `jenis` enum('DOSEN_WALI','PEMBIMBING_PKL','PEMBIMBING_MBKM','PEMBIMBING_SKRIPSI','PEMBIMBING_TESIS','PEMBIMBING_DISERTASI','PENGUJI_SKRIPSI') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'DOSEN_WALI',
  `is_primary` tinyint(1) NOT NULL DEFAULT '1',
  `semester_mulai_id` bigint unsigned NOT NULL,
  `semester_selesai_id` bigint unsigned DEFAULT NULL,
  `tanggal_mulai` date NOT NULL,
  `tanggal_selesai` date DEFAULT NULL,
  `nomor_sk` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tanggal_sk` date DEFAULT NULL,
  `alasan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `keterangan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_by` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_by` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_by` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('AKTIF','SELESAI','DIBATALKAN') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'AKTIF',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pembimbing_akademik_semester_selesai_id_foreign` (`semester_selesai_id`),
  KEY `pembimbing_akademik_created_by_foreign` (`created_by`),
  KEY `pembimbing_akademik_updated_by_foreign` (`updated_by`),
  KEY `pembimbing_akademik_deleted_by_foreign` (`deleted_by`),
  KEY `pembimbing_akademik_kelas_id_index` (`kelas_id`),
  KEY `pembimbing_akademik_mahasiswa_id_index` (`mahasiswa_id`),
  KEY `pembimbing_akademik_dosen_id_index` (`dosen_id`),
  KEY `pembimbing_akademik_jenis_index` (`jenis`),
  KEY `pembimbing_akademik_status_index` (`status`),
  KEY `pembimbing_akademik_semester_mulai_id_semester_selesai_id_index` (`semester_mulai_id`,`semester_selesai_id`),
  KEY `pembimbing_akademik_mahasiswa_id_jenis_status_index` (`mahasiswa_id`,`jenis`,`status`),
  KEY `pembimbing_akademik_kelas_id_jenis_status_index` (`kelas_id`,`jenis`,`status`),
  KEY `pembimbing_akademik_dosen_id_status_index` (`dosen_id`,`status`),
  CONSTRAINT `pembimbing_akademik_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `pembimbing_akademik_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `pembimbing_akademik_dosen_id_foreign` FOREIGN KEY (`dosen_id`) REFERENCES `trx_dosen` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pembimbing_akademik_kelas_id_foreign` FOREIGN KEY (`kelas_id`) REFERENCES `kelas` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `pembimbing_akademik_mahasiswa_id_foreign` FOREIGN KEY (`mahasiswa_id`) REFERENCES `mahasiswas` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `pembimbing_akademik_semester_mulai_id_foreign` FOREIGN KEY (`semester_mulai_id`) REFERENCES `ref_tahun_akademik` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `pembimbing_akademik_semester_selesai_id_foreign` FOREIGN KEY (`semester_selesai_id`) REFERENCES `ref_tahun_akademik` (`id`) ON DELETE SET NULL,
  CONSTRAINT `pembimbing_akademik_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `chk_pembimbing_scope` CHECK ((((`jenis` = _utf8mb4'DOSEN_WALI') and (((`kelas_id` is not null) and (`mahasiswa_id` is null)) or ((`kelas_id` is null) and (`mahasiswa_id` is not null)))) or ((`jenis` <> _utf8mb4'DOSEN_WALI') and (`kelas_id` is null) and (`mahasiswa_id` is not null))))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`dbuser`@`%`*/ /*!50003 TRIGGER `trg_pembimbing_akademik_biu` BEFORE INSERT ON `pembimbing_akademik` FOR EACH ROW BEGIN
                IF NEW.jenis = 'DOSEN_WALI' AND NEW.is_primary = 1 AND NEW.status = 'AKTIF' THEN
                    IF NEW.kelas_id IS NOT NULL AND EXISTS (
                        SELECT 1 FROM pembimbing_akademik
                        WHERE kelas_id = NEW.kelas_id AND jenis = 'DOSEN_WALI' AND is_primary = 1 AND status = 'AKTIF'
                    ) THEN
                        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Kelas ini sudah memiliki dosen wali utama yang aktif.';
                    END IF;
                    IF NEW.mahasiswa_id IS NOT NULL AND EXISTS (
                        SELECT 1 FROM pembimbing_akademik
                        WHERE mahasiswa_id = NEW.mahasiswa_id AND jenis = 'DOSEN_WALI' AND is_primary = 1 AND status = 'AKTIF'
                    ) THEN
                        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Mahasiswa ini sudah memiliki dosen wali utama yang aktif.';
                    END IF;
                END IF;
            END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`dbuser`@`%`*/ /*!50003 TRIGGER `trg_pembimbing_akademik_bu` BEFORE UPDATE ON `pembimbing_akademik` FOR EACH ROW BEGIN
                IF NEW.jenis = 'DOSEN_WALI' AND NEW.is_primary = 1 AND NEW.status = 'AKTIF' THEN
                    IF NEW.kelas_id IS NOT NULL AND EXISTS (
                        SELECT 1 FROM pembimbing_akademik
                        WHERE kelas_id = NEW.kelas_id AND jenis = 'DOSEN_WALI' AND is_primary = 1 AND status = 'AKTIF' AND id <> NEW.id
                    ) THEN
                        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Kelas ini sudah memiliki dosen wali utama yang aktif.';
                    END IF;
                    IF NEW.mahasiswa_id IS NOT NULL AND EXISTS (
                        SELECT 1 FROM pembimbing_akademik
                        WHERE mahasiswa_id = NEW.mahasiswa_id AND jenis = 'DOSEN_WALI' AND is_primary = 1 AND status = 'AKTIF' AND id <> NEW.id
                    ) THEN
                        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Mahasiswa ini sudah memiliki dosen wali utama yang aktif.';
                    END IF;
                END IF;
            END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
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
  KEY `perkuliahan_absensi_krs_detail_id_status_kehadiran_index` (`krs_detail_id`,`status_kehadiran`),
  KEY `perkuliahan_absensi_perkuliahan_sesi_id_device_fingerprint_index` (`perkuliahan_sesi_id`,`device_fingerprint`),
  KEY `perkuliahan_absensi_perkuliahan_sesi_id_ip_address_index` (`perkuliahan_sesi_id`,`ip_address`),
  KEY `perkuliahan_absensi_perkuliahan_sesi_id_foreign` (`perkuliahan_sesi_id`),
  KEY `perkuliahan_absensi_status_kehadiran_index` (`status_kehadiran`),
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
DROP TABLE IF EXISTS `profile_change_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `profile_change_requests` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `mahasiswa_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `field_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `old_value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `new_value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `attachment_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('pending','approved','rejected') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `reviewed_by` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `rejection_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `profile_change_requests_mahasiswa_id_status_index` (`mahasiswa_id`,`status`),
  KEY `profile_change_requests_reviewed_by_foreign` (`reviewed_by`),
  CONSTRAINT `profile_change_requests_mahasiswa_id_foreign` FOREIGN KEY (`mahasiswa_id`) REFERENCES `mahasiswas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `profile_change_requests_reviewed_by_foreign` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
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
  `last_nim_seq` bigint unsigned NOT NULL,
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
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
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
  `krs_dibuka_at` timestamp NULL DEFAULT NULL,
  `krs_ditutup_at` timestamp NULL DEFAULT NULL,
  `nilai_dikunci_at` timestamp NULL DEFAULT NULL,
  `nilai_dipublish_at` timestamp NULL DEFAULT NULL,
  `semester_ditutup_at` timestamp NULL DEFAULT NULL,
  `ditutup_by` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ref_tahun_akademik_kode_tahun_unique` (`kode_tahun`),
  KEY `ref_tahun_akademik_is_active_index` (`is_active`),
  KEY `ref_tahun_akademik_created_by_foreign` (`created_by`),
  KEY `ref_tahun_akademik_updated_by_foreign` (`updated_by`),
  KEY `ref_tahun_akademik_activated_by_foreign` (`activated_by`),
  KEY `ref_tahun_akademik_tgl_publish_nilai_index` (`tgl_publish_nilai`),
  KEY `ref_tahun_akademik_ditutup_by_foreign` (`ditutup_by`),
  KEY `ref_tahun_akademik_status_index` (`status`),
  CONSTRAINT `ref_tahun_akademik_activated_by_foreign` FOREIGN KEY (`activated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ref_tahun_akademik_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ref_tahun_akademik_ditutup_by_foreign` FOREIGN KEY (`ditutup_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
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
  `group` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `locked` tinyint(1) NOT NULL DEFAULT '0',
  `payload` json NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `settings_group_name_unique` (`group`,`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sinkronisasi_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sinkronisasi_batches` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tahun_akademik_id` bigint unsigned NOT NULL,
  `mode` enum('DRY_RUN','EKSEKUSI') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'EKSEKUSI',
  `status` enum('PROCESSING','COMPLETED','FAILED') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'PROCESSING',
  `parameter_snapshot` json NOT NULL,
  `summary_snapshot` json DEFAULT NULL,
  `total_mahasiswa` int unsigned NOT NULL DEFAULT '0',
  `total_ditambah` int unsigned NOT NULL DEFAULT '0',
  `total_review` int unsigned NOT NULL DEFAULT '0',
  `total_warning` int unsigned NOT NULL DEFAULT '0',
  `total_dilewati` int unsigned NOT NULL DEFAULT '0',
  `total_error` int unsigned NOT NULL DEFAULT '0',
  `error_message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `started_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_by` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sinkronisasi_batches_created_by_foreign` (`created_by`),
  KEY `sinkronisasi_batches_tahun_akademik_id_status_index` (`tahun_akademik_id`,`status`),
  KEY `sinkronisasi_batches_status_created_at_index` (`status`,`created_at`),
  CONSTRAINT `sinkronisasi_batches_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `sinkronisasi_batches_tahun_akademik_id_foreign` FOREIGN KEY (`tahun_akademik_id`) REFERENCES `ref_tahun_akademik` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sinkronisasi_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sinkronisasi_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `sinkronisasi_batch_id` bigint unsigned NOT NULL,
  `mahasiswa_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('BERHASIL','GAGAL','DILEWATI') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `jumlah_ditambah` smallint unsigned NOT NULL DEFAULT '0',
  `jumlah_review` smallint unsigned NOT NULL DEFAULT '0',
  `jumlah_warning` smallint unsigned NOT NULL DEFAULT '0',
  `pesan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `sinkronisasi_logs_sinkronisasi_batch_id_status_index` (`sinkronisasi_batch_id`,`status`),
  KEY `sinkronisasi_logs_mahasiswa_id_created_at_index` (`mahasiswa_id`,`created_at`),
  KEY `sinkronisasi_logs_status_index` (`status`),
  CONSTRAINT `sinkronisasi_logs_mahasiswa_id_foreign` FOREIGN KEY (`mahasiswa_id`) REFERENCES `mahasiswas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `sinkronisasi_logs_sinkronisasi_batch_id_foreign` FOREIGN KEY (`sinkronisasi_batch_id`) REFERENCES `sinkronisasi_batches` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sinkronisasi_review_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sinkronisasi_review_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `sinkronisasi_batch_id` bigint unsigned NOT NULL,
  `tagihan_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tagihan_detail_id` bigint unsigned NOT NULL,
  `mahasiswa_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `komponen_biaya_id` bigint unsigned NOT NULL,
  `nominal_existing` decimal(19,2) NOT NULL,
  `nominal_skema_baru` decimal(19,2) NOT NULL,
  `status` enum('PENDING','IN_PROGRESS','RESOLVED','IGNORED') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'PENDING',
  `keuangan_adjustment_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `resolved_by` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `resolved_at` timestamp NULL DEFAULT NULL,
  `catatan_resolusi` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sinkronisasi_review_items_tagihan_id_foreign` (`tagihan_id`),
  KEY `sinkronisasi_review_items_komponen_biaya_id_foreign` (`komponen_biaya_id`),
  KEY `sinkronisasi_review_items_keuangan_adjustment_id_foreign` (`keuangan_adjustment_id`),
  KEY `sinkronisasi_review_items_resolved_by_foreign` (`resolved_by`),
  KEY `sinkronisasi_review_items_tagihan_detail_id_status_index` (`tagihan_detail_id`,`status`),
  KEY `sinkronisasi_review_items_sinkronisasi_batch_id_status_index` (`sinkronisasi_batch_id`,`status`),
  KEY `sinkronisasi_review_items_mahasiswa_id_status_index` (`mahasiswa_id`,`status`),
  CONSTRAINT `sinkronisasi_review_items_keuangan_adjustment_id_foreign` FOREIGN KEY (`keuangan_adjustment_id`) REFERENCES `keuangan_adjustments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `sinkronisasi_review_items_komponen_biaya_id_foreign` FOREIGN KEY (`komponen_biaya_id`) REFERENCES `keuangan_komponen_biaya` (`id`),
  CONSTRAINT `sinkronisasi_review_items_mahasiswa_id_foreign` FOREIGN KEY (`mahasiswa_id`) REFERENCES `mahasiswas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `sinkronisasi_review_items_resolved_by_foreign` FOREIGN KEY (`resolved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `sinkronisasi_review_items_sinkronisasi_batch_id_foreign` FOREIGN KEY (`sinkronisasi_batch_id`) REFERENCES `sinkronisasi_batches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `sinkronisasi_review_items_tagihan_detail_id_foreign` FOREIGN KEY (`tagihan_detail_id`) REFERENCES `tagihan_mahasiswas_details` (`id`) ON DELETE CASCADE,
  CONSTRAINT `sinkronisasi_review_items_tagihan_id_foreign` FOREIGN KEY (`tagihan_id`) REFERENCES `tagihan_mahasiswas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `tagihan_mahasiswas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tagihan_mahasiswas` (
  `id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `mahasiswa_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tahun_akademik_id` bigint unsigned NOT NULL,
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
  UNIQUE KEY `tagihan_mahasiswas_mhs_tahun_akademik_unique` (`mahasiswa_id`,`tahun_akademik_id`),
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
DROP TABLE IF EXISTS `tagihan_non_reguler_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tagihan_non_reguler_details` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tagihan_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `komponen_biaya_id` bigint unsigned NOT NULL,
  `nama_komponen_snapshot` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nominal_dasar` decimal(19,2) NOT NULL,
  `nominal_diskon` decimal(19,2) NOT NULL DEFAULT '0.00',
  `nominal_tagihan` decimal(19,2) NOT NULL,
  `nominal_terbayar` decimal(19,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unik_tagihan_nr_komponen` (`tagihan_id`,`komponen_biaya_id`),
  KEY `tagihan_non_reguler_details_komponen_biaya_id_index` (`komponen_biaya_id`),
  CONSTRAINT `tagihan_non_reguler_details_komponen_biaya_id_foreign` FOREIGN KEY (`komponen_biaya_id`) REFERENCES `keuangan_komponen_biaya` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `tagihan_non_reguler_details_tagihan_id_foreign` FOREIGN KEY (`tagihan_id`) REFERENCES `tagihan_non_regulers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `tagihan_non_regulers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tagihan_non_regulers` (
  `id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `mahasiswa_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `kode_transaksi` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `deskripsi` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_tagihan` decimal(19,2) NOT NULL,
  `total_bayar` decimal(19,2) NOT NULL DEFAULT '0.00',
  `status_bayar` enum('BELUM','CICIL','LUNAS') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'BELUM',
  `referensi_type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `referensi_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tenggat_waktu` date DEFAULT NULL,
  `created_by` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tagihan_non_regulers_kode_transaksi_unique` (`kode_transaksi`),
  KEY `tagihan_non_regulers_created_by_foreign` (`created_by`),
  KEY `tagihan_non_regulers_mahasiswa_id_status_bayar_index` (`mahasiswa_id`,`status_bayar`),
  KEY `tagihan_non_regulers_referensi_type_referensi_id_index` (`referensi_type`,`referensi_id`),
  CONSTRAINT `tagihan_non_regulers_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tagihan_non_regulers_mahasiswa_id_foreign` FOREIGN KEY (`mahasiswa_id`) REFERENCES `mahasiswas` (`id`) ON DELETE CASCADE
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
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `trx_dosen_person_id_unique` (`person_id`),
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

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (1,'2026_07_16_002850_create_academic_history_logs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (2,'2026_07_16_002850_create_activity_log_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (3,'2026_07_16_002850_create_akademik_ekuivalensi_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (4,'2026_07_16_002850_create_akademik_grade_revision_logs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (5,'2026_07_16_002850_create_akademik_transkrip_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (6,'2026_07_16_002850_create_bank_kampuses_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (7,'2026_07_16_002850_create_cache_locks_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (8,'2026_07_16_002850_create_cache_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (9,'2026_07_16_002850_create_dispensasi_akademik_logs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (10,'2026_07_16_002850_create_dispensasi_akademiks_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (11,'2026_07_16_002850_create_dosen_biodata_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (12,'2026_07_16_002850_create_dosen_dokumen_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (13,'2026_07_16_002850_create_dosen_profile_change_requests_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (14,'2026_07_16_002850_create_dosen_riwayat_pendidikan_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (15,'2026_07_16_002850_create_exports_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (16,'2026_07_16_002850_create_failed_import_rows_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (17,'2026_07_16_002850_create_failed_jobs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (18,'2026_07_16_002850_create_imports_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (19,'2026_07_16_002850_create_jadwal_komponen_nilai_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (20,'2026_07_16_002850_create_jadwal_kuliah_dosen_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (21,'2026_07_16_002850_create_jadwal_kuliah_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (22,'2026_07_16_002850_create_jadwal_ujian_pengawas_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (23,'2026_07_16_002850_create_jadwal_ujian_pesertas_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (24,'2026_07_16_002850_create_jadwal_ujians_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (25,'2026_07_16_002850_create_job_batches_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (26,'2026_07_16_002850_create_jobs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (27,'2026_07_16_002850_create_kelas_dosen_wali_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (28,'2026_07_16_002850_create_kelas_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (29,'2026_07_16_002850_create_keuangan_adjustments_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (30,'2026_07_16_002850_create_keuangan_beasiswa_details_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (31,'2026_07_16_002850_create_keuangan_detail_tarif_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (32,'2026_07_16_002850_create_keuangan_general_ledgers_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (33,'2026_07_16_002850_create_keuangan_komponen_biaya_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (34,'2026_07_16_002850_create_keuangan_mahasiswa_beasiswas_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (35,'2026_07_16_002850_create_keuangan_master_beasiswas_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (36,'2026_07_16_002850_create_keuangan_saldo_transactions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (37,'2026_07_16_002850_create_keuangan_saldos_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (38,'2026_07_16_002850_create_keuangan_skema_tarif_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (39,'2026_07_16_002850_create_krs_detail_nilai_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (40,'2026_07_16_002850_create_krs_detail_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (41,'2026_07_16_002850_create_krs_status_logs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (42,'2026_07_16_002850_create_krs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (43,'2026_07_16_002850_create_kurikulum_komponen_nilai_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (44,'2026_07_16_002850_create_kurikulum_mata_kuliah_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (45,'2026_07_16_002850_create_kurikulum_mk_prasyarat_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (46,'2026_07_16_002850_create_lpm_ami_discussions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (47,'2026_07_16_002850_create_lpm_ami_findings_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (48,'2026_07_16_002850_create_lpm_ami_periodes_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (49,'2026_07_16_002850_create_lpm_dokumens_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (50,'2026_07_16_002850_create_lpm_edom_jawaban_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (51,'2026_07_16_002850_create_lpm_edom_progress_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (52,'2026_07_16_002850_create_lpm_edom_saran_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (53,'2026_07_16_002850_create_lpm_iku_targets_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (54,'2026_07_16_002850_create_lpm_indikators_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (55,'2026_07_16_002850_create_lpm_kuisioner_kelompok_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (56,'2026_07_16_002850_create_lpm_kuisioner_pertanyaan_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (57,'2026_07_16_002850_create_lpm_standars_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (58,'2026_07_16_002850_create_lpm_survey_jawaban_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (59,'2026_07_16_002850_create_lppm_luarans_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (60,'2026_07_16_002850_create_lppm_ref_jenis_luarans_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (61,'2026_07_16_002850_create_lppm_ref_jenis_skemas_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (62,'2026_07_16_002850_create_lppm_skemas_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (63,'2026_07_16_002850_create_lppm_usulan_anggotas_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (64,'2026_07_16_002850_create_lppm_usulans_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (65,'2026_07_16_002850_create_mahasiswa_biodata_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (66,'2026_07_16_002850_create_mahasiswa_kelas_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (67,'2026_07_16_002850_create_mahasiswas_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (68,'2026_07_16_002850_create_master_kurikulums_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (69,'2026_07_16_002850_create_master_mata_kuliahs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (70,'2026_07_16_002850_create_model_has_permissions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (71,'2026_07_16_002850_create_model_has_roles_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (72,'2026_07_16_002850_create_notifications_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (73,'2026_07_16_002850_create_password_reset_tokens_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (74,'2026_07_16_002850_create_payment_policies_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (75,'2026_07_16_002850_create_payment_policy_details_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (76,'2026_07_16_002850_create_pembayaran_mahasiswas_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (77,'2026_07_16_002850_create_perkuliahan_absensi_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (78,'2026_07_16_002850_create_perkuliahan_sesi_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (79,'2026_07_16_002850_create_permissions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (80,'2026_07_16_002850_create_personal_access_tokens_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (81,'2026_07_16_002850_create_pmb_camaba_staging_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (82,'2026_07_16_002850_create_profile_change_requests_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (83,'2026_07_16_002850_create_ref_angkatan_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (84,'2026_07_16_002850_create_ref_aturan_sks_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (85,'2026_07_16_002850_create_ref_dokumen_dosen_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (86,'2026_07_16_002850_create_ref_fakultas_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (87,'2026_07_16_002850_create_ref_gelar_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (88,'2026_07_16_002850_create_ref_jabatan_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (89,'2026_07_16_002850_create_ref_komponen_nilai_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (90,'2026_07_16_002850_create_ref_person_role_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (91,'2026_07_16_002850_create_ref_person_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (92,'2026_07_16_002850_create_ref_prodi_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (93,'2026_07_16_002850_create_ref_program_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (94,'2026_07_16_002850_create_ref_ruang_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (95,'2026_07_16_002850_create_ref_skala_nilai_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (96,'2026_07_16_002850_create_ref_status_verifikasi_pembayaran_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (97,'2026_07_16_002850_create_ref_tahun_akademik_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (98,'2026_07_16_002850_create_riwayat_prodi_mahasiswas_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (99,'2026_07_16_002850_create_riwayat_status_mahasiswas_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (100,'2026_07_16_002850_create_role_has_permissions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (101,'2026_07_16_002850_create_roles_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (102,'2026_07_16_002850_create_sessions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (103,'2026_07_16_002850_create_settings_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (104,'2026_07_16_002850_create_tagihan_mahasiswas_details_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (105,'2026_07_16_002850_create_tagihan_mahasiswas_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (106,'2026_07_16_002850_create_trx_dosen_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (107,'2026_07_16_002850_create_trx_pegawai_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (108,'2026_07_16_002850_create_trx_person_gelar_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (109,'2026_07_16_002850_create_trx_person_jabatan_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (110,'2026_07_16_002850_create_trx_person_role_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (111,'2026_07_16_002850_create_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (112,'2026_07_16_002853_add_foreign_keys_to_academic_history_logs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (113,'2026_07_16_002853_add_foreign_keys_to_akademik_ekuivalensi_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (114,'2026_07_16_002853_add_foreign_keys_to_akademik_grade_revision_logs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (115,'2026_07_16_002853_add_foreign_keys_to_akademik_transkrip_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (116,'2026_07_16_002853_add_foreign_keys_to_dispensasi_akademik_logs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (117,'2026_07_16_002853_add_foreign_keys_to_dispensasi_akademiks_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (118,'2026_07_16_002853_add_foreign_keys_to_dosen_biodata_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (119,'2026_07_16_002853_add_foreign_keys_to_dosen_dokumen_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (120,'2026_07_16_002853_add_foreign_keys_to_dosen_profile_change_requests_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (121,'2026_07_16_002853_add_foreign_keys_to_dosen_riwayat_pendidikan_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (122,'2026_07_16_002853_add_foreign_keys_to_exports_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (123,'2026_07_16_002853_add_foreign_keys_to_failed_import_rows_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (124,'2026_07_16_002853_add_foreign_keys_to_imports_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (125,'2026_07_16_002853_add_foreign_keys_to_jadwal_komponen_nilai_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (126,'2026_07_16_002853_add_foreign_keys_to_jadwal_kuliah_dosen_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (127,'2026_07_16_002853_add_foreign_keys_to_jadwal_kuliah_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (128,'2026_07_16_002853_add_foreign_keys_to_jadwal_ujian_pengawas_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (129,'2026_07_16_002853_add_foreign_keys_to_jadwal_ujian_pesertas_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (130,'2026_07_16_002853_add_foreign_keys_to_jadwal_ujians_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (131,'2026_07_16_002853_add_foreign_keys_to_kelas_dosen_wali_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (132,'2026_07_16_002853_add_foreign_keys_to_kelas_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (133,'2026_07_16_002853_add_foreign_keys_to_keuangan_adjustments_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (134,'2026_07_16_002853_add_foreign_keys_to_keuangan_beasiswa_details_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (135,'2026_07_16_002853_add_foreign_keys_to_keuangan_detail_tarif_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (136,'2026_07_16_002853_add_foreign_keys_to_keuangan_general_ledgers_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (137,'2026_07_16_002853_add_foreign_keys_to_keuangan_mahasiswa_beasiswas_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (138,'2026_07_16_002853_add_foreign_keys_to_keuangan_saldo_transactions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (139,'2026_07_16_002853_add_foreign_keys_to_keuangan_saldos_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (140,'2026_07_16_002853_add_foreign_keys_to_keuangan_skema_tarif_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (141,'2026_07_16_002853_add_foreign_keys_to_krs_detail_nilai_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (142,'2026_07_16_002853_add_foreign_keys_to_krs_detail_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (143,'2026_07_16_002853_add_foreign_keys_to_krs_status_logs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (144,'2026_07_16_002853_add_foreign_keys_to_krs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (145,'2026_07_16_002853_add_foreign_keys_to_kurikulum_komponen_nilai_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (146,'2026_07_16_002853_add_foreign_keys_to_kurikulum_mata_kuliah_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (147,'2026_07_16_002853_add_foreign_keys_to_kurikulum_mk_prasyarat_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (148,'2026_07_16_002853_add_foreign_keys_to_lpm_ami_discussions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (149,'2026_07_16_002853_add_foreign_keys_to_lpm_ami_findings_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (150,'2026_07_16_002853_add_foreign_keys_to_lpm_edom_jawaban_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (151,'2026_07_16_002853_add_foreign_keys_to_lpm_edom_progress_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (152,'2026_07_16_002853_add_foreign_keys_to_lpm_edom_saran_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (153,'2026_07_16_002853_add_foreign_keys_to_lpm_iku_targets_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (154,'2026_07_16_002853_add_foreign_keys_to_lpm_indikators_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (155,'2026_07_16_002853_add_foreign_keys_to_lpm_kuisioner_kelompok_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (156,'2026_07_16_002853_add_foreign_keys_to_lpm_kuisioner_pertanyaan_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (157,'2026_07_16_002853_add_foreign_keys_to_lpm_survey_jawaban_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (158,'2026_07_16_002853_add_foreign_keys_to_lppm_luarans_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (159,'2026_07_16_002853_add_foreign_keys_to_lppm_skemas_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (160,'2026_07_16_002853_add_foreign_keys_to_lppm_usulan_anggotas_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (161,'2026_07_16_002853_add_foreign_keys_to_lppm_usulans_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (162,'2026_07_16_002853_add_foreign_keys_to_mahasiswa_biodata_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (163,'2026_07_16_002853_add_foreign_keys_to_mahasiswa_kelas_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (164,'2026_07_16_002853_add_foreign_keys_to_mahasiswas_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (165,'2026_07_16_002853_add_foreign_keys_to_master_kurikulums_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (166,'2026_07_16_002853_add_foreign_keys_to_master_mata_kuliahs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (167,'2026_07_16_002853_add_foreign_keys_to_model_has_permissions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (168,'2026_07_16_002853_add_foreign_keys_to_model_has_roles_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (169,'2026_07_16_002853_add_foreign_keys_to_payment_policies_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (170,'2026_07_16_002853_add_foreign_keys_to_payment_policy_details_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (171,'2026_07_16_002853_add_foreign_keys_to_pembayaran_mahasiswas_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (172,'2026_07_16_002853_add_foreign_keys_to_perkuliahan_absensi_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (173,'2026_07_16_002853_add_foreign_keys_to_perkuliahan_sesi_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (174,'2026_07_16_002853_add_foreign_keys_to_profile_change_requests_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (175,'2026_07_16_002853_add_foreign_keys_to_ref_prodi_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (176,'2026_07_16_002853_add_foreign_keys_to_ref_tahun_akademik_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (177,'2026_07_16_002853_add_foreign_keys_to_riwayat_prodi_mahasiswas_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (178,'2026_07_16_002853_add_foreign_keys_to_riwayat_status_mahasiswas_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (179,'2026_07_16_002853_add_foreign_keys_to_role_has_permissions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (180,'2026_07_16_002853_add_foreign_keys_to_tagihan_mahasiswas_details_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (181,'2026_07_16_002853_add_foreign_keys_to_tagihan_mahasiswas_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (182,'2026_07_16_002853_add_foreign_keys_to_trx_dosen_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (183,'2026_07_16_002853_add_foreign_keys_to_trx_pegawai_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (184,'2026_07_16_002853_add_foreign_keys_to_trx_person_gelar_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (185,'2026_07_16_002853_add_foreign_keys_to_trx_person_jabatan_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (186,'2026_07_16_002853_add_foreign_keys_to_trx_person_role_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (187,'2026_07_16_002853_add_foreign_keys_to_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (192,'2026_07_16_131621_drop_data_tambahan_from_mahasiswas_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (193,'2026_07_17_111802_add_unique_constraint_to_tagihan_mahasiswas_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (194,'2026_07_17_112416_add_jenis_tagihan_to_tagihan_mahasiswas_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (195,'2026_07_17_113112_create_tagihan_non_regulers_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (196,'2026_07_17_113118_create_tagihan_non_reguler_details_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (197,'2026_07_17_121843_add_unique_index_optional_tagihan_non_reguler_details',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (198,'2026_07_17_123857_make_pembayaran_mahasiswas_tagihan_polymorphic',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (199,'2026_07_17_171235_add_unique_index_optional_keuangan_general_ledgers',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (200,'2026_07_18_104738_drop_data_tambahan_from_trx_dosen_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (201,'2026_07_19_003108_create_sinkronisasi_batches_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (202,'2026_07_19_003136_create_sinkronisasi_review_items_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (203,'2026_07_19_003453_create_sinkronisasi_logs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (204,'2026_07_19_003529_add_sumber_to_tagihan_mahasiswas_details_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (205,'2026_07_19_204741_create_generator_batches_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (206,'2026_07_19_204828_create_generator_logs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (207,'2026_07_20_011825_add_index_status_ta_to_krs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (208,'2026_07_20_012109_add_unique_primary_dosen_wali_to_kelas_dosen_wali',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (209,'2026_07_21_000001_create_lpm_unit_kerjas_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (210,'2026_07_21_000002_create_lpm_unit_pics_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (211,'2026_07_21_000003_create_lpm_kategori_standars_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (212,'2026_07_21_000004_add_kategori_standar_id_to_lpm_standars_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (213,'2026_07_21_000005_add_unit_kerja_id_to_lpm_iku_targets_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (214,'2026_07_21_155518_create_lpm_dokumen_approvals_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (215,'2026_07_21_155549_create_lpm_dokumen_riwayats_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (216,'2026_07_21_155631_extend_lpm_dokumens_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (217,'2026_07_23_092725_create_lpm_survey_jawaban_pihak_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (218,'2026_07_23_093144_create_lpm_survey_analisis_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (219,'2026_07_23_140434_create_migration_batches_table',3);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (220,'2026_07_23_140436_create_migration_logs_table',3);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (221,'2026_07_24_121805_create_lpm_auditors_table',4);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (222,'2026_07_24_121832_create_lpm_ami_programs_table',4);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (223,'2026_07_24_121902_create_lpm_ami_program_auditors_table',4);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (224,'2026_07_24_121925_create_lpm_ami_checklists_table',4);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (225,'2026_07_24_121954_create_lpm_ami_checklist_items_table',4);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (226,'2026_07_24_122031_create_lpm_ami_checklist_jawabans_table',4);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (227,'2026_07_24_122112_create_lpm_ami_evidences_table',4);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (228,'2026_07_24_122145_extend_lpm_ami_findings_table',4);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (229,'2026_07_24_124906_create_lpm_akreditasi_lembagas_table',5);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (230,'2026_07_24_124927_create_lpm_akreditasis',5);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (231,'2026_07_24_124951_create_lpm_akreditasi_kriterias_table',5);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (232,'2026_07_24_125008_create_lpm_akreditasi_elemens',5);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (233,'2026_07_24_125026_create_lpm_akreditasi_indikators',5);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (234,'2026_07_24_125045_create_lpm_akreditasi_evidences',5);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (235,'2026_07_25_222646_create_lpm_benchmark_institusis_table',6);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (236,'2026_07_25_222719_create_lpm_benchmarks_table',6);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (238,'2026_07_25_224323_create_pdf_documents_table',7);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (239,'2026_07_26_214732_create_pdf_number_sequences_table',8);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (240,'2026_07_26_214733_create_pdf_signature_authorities_table',8);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (241,'2026_07_26_214734_create_pdf_signatures_table',8);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (242,'2026_07_27_121048_create_lpm_bukti_pelaksanaans_table',9);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (243,'2026_07_27_121115_create_lpm_riwayat_peningkatans_table',9);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (244,'2026_07_27_121926_create_pdf_verifications_table',9);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (249,'2026_07_29_193029_add_scope_to_pdf_signature_authorities_table',11);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (250,'2026_07_30_005123_create_payment_gateway_transactions_table',12);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (251,'2026_07_30_005156_create_payment_gateway_webhook_logs_table',13);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (252,'2026_07_30_005733_create_midtrans_settings',14);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (253,'2026_07_30_011551_create_payment_gateway_transactions_table',15);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (254,'2026_07_30_011628_create_payment_gateway_webhook_logs_table',16);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (255,'2026_07_30_011729_create_midtrans_settings',17);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (256,'2026_07_30_203542_create_midtrans_transactions_table',18);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (257,'2026_07_30_203643_create_midtrans_gateway_logs_table',18);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (258,'2026_08_03_123636_create_konfigurasi_pembimbing_akademik_table',19);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (259,'2026_08_03_123708_create_pembimbing_akademik_table',19);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (260,'2026_08_03_195654_add_integrity_constraints_to_pembimbing_akademik_table',20);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (261,'2026_08_03_195655_add_active_enrollment_guard_to_mahasiswa_kelas_table',20);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (262,'2026_07_16_002854_create_kampus_settings',21);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (263,'2026_07_16_002855_add_reset_nim_tahunan_to_kampus_settings',21);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (264,'2026_07_16_002856_add_neo_feeder_to_kampus_settings',21);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (265,'2026_07_16_002857_add_pro_settings_to_kampus_settings',21);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (266,'2026_08_03_204039_drop_kelas_dosen_wali_table',21);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (267,'2026_08_06_095815_add_status_state_machine_to_ref_tahun_akademik',21);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (268,'2026_08_13_125914_add_unique_person_id_to_trx_dosen_table',22);
