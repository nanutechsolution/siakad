# OIDC SSO Siakad → E-Perpustakaan UNMARIS

Dokumen integrasi untuk **Siakad sebagai OpenID Connect Provider (OP/IdP)** dan E-Perpustakaan sebagai client/Relying Party.

> Tidak ada endpoint yang menerima atau meneruskan password Siakad. Login tetap terjadi pada Siakad. E-Perpustakaan hanya menerima authorization code/token dan klaim identitas yang diizinkan.

## Status dan prasyarat

- Laravel Passport mengimplementasikan OAuth 2.0 authorization-code + PKCE.
- Lapisan OIDC menambahkan discovery, ID token RS256, JWKS, userinfo, nonce, end-session, revocation, dan introspection.
- Production harus HTTPS dengan reverse-proxy headers terpercaya dan nilai `OIDC_ISSUER` identik dengan URL publik.
- Kunci RSA Passport di `storage/oauth-private.key` harus dijaga sebagai secret deployment. Jangan commit atau mengirimkannya. `storage/oauth-public.key` boleh dipublikasikan melalui JWKS.
- Migration Passport telah disesuaikan agar kolom pemilik token memakai UUID Siakad (`users.id` adalah char(36)). Jalankan migrasi normal setelah backup dan persetujuan deployment; jangan gunakan `migrate:fresh`.

## Konfigurasi Siakad

Atur pada secret manager / environment server, bukan dokumentasi atau repository:

```dotenv
OIDC_ISSUER=https://siakad.unmarissumba.ac.id
OIDC_ID_TOKEN_LIFETIME=600
OIDC_ACCESS_TOKEN_LIFETIME=600
OIDC_AUTH_CODE_LIFETIME=120
OIDC_ALLOWED_CLOCK_SKEW=30
OIDC_ENABLE_HTTP_LOCAL=false
OIDC_KEY_ID=siakad-rsa-2026-01
```

Generate kunci dengan `php artisan passport:keys` pada environment target. Jangan memakai `APP_KEY` untuk penandatanganan ID token. Untuk multi-node, distribusikan key pair secara aman dan konsisten; private key hanya pada server provider.

Issuer dan discovery:

```
https://siakad.unmarissumba.ac.id/.well-known/openid-configuration
```

Discovery mencantumkan endpoint dan metadata aktual. Endpoint tetap:

| Fungsi | URL |
|---|---|
| Authorization | `https://siakad.unmarissumba.ac.id/oauth/authorize` |
| Token | `https://siakad.unmarissumba.ac.id/oauth/token` |
| UserInfo | `https://siakad.unmarissumba.ac.id/oauth/userinfo` |
| JWKS | `https://siakad.unmarissumba.ac.id/oauth/jwks` |
| Revocation (RFC 7009) | `https://siakad.unmarissumba.ac.id/oauth/revoke` |
| Introspection (RFC 7662) | `https://siakad.unmarissumba.ac.id/oauth/introspect` |
| End session | `https://siakad.unmarissumba.ac.id/oauth/end-session` |

Gunakan metadata discovery sebagai sumber kebenaran; path dapat dikonfigurasi di `config/oidc.php`.

## Scope dan klaim

| Scope | Klaim |
|---|---|
| `openid` | `sub` = UUID `users.id` (ID stabil), `iss`, `aud`, `exp`, `iat`, `auth_time`, `nonce`, `at_hash` pada ID token |
| `profile` | `name` dari `ref_person.nama_lengkap`, fallback ke `users.name` |
| `email` | `email`, `email_verified` |
| `siakad_identity` | `nim`, `nidn`, `nip` bila tersedia, dan `preferred_username` |
| `account_status` | `active` (boolean) |

`active` mensyaratkan `users.is_active`. Untuk mahasiswa, status pada tahun akademik aktif harus `A`; tanpa riwayat pada tahun aktif status akun tidak lolos. Untuk dosen/pegawai, record terkait juga harus aktif. User admin tanpa record akademik/kepegawaian mengikuti `users.is_active`.

NIK, password, password hash, remember token, dan kredensial Siakad lain tidak pernah dikeluarkan.

## Buat client E-Perpustakaan

Pengguna admin dengan permission `OidcClient` membuka **/admin → Integrasi Sistem → Client SSO (OIDC)**:

1. Buat client dengan nama `E-Perpustakaan UNMARIS`.
2. Masukkan redirect URI callback E-Perpustakaan **lengkap dan persis** (HTTPS, tanpa wildcard), misalnya `https://perpustakaan.unmarissumba.ac.id/auth/callback`.
3. Pilih scope minimum yang dibutuhkan; untuk integrasi penuh: `openid profile email siakad_identity account_status`.
4. Tambahkan post-logout URI bila diperlukan.
5. Salin client secret yang muncul sekali dan simpan langsung ke secret manager E-Perpustakaan. Secret disimpan ter-hash di Siakad dan tidak dapat dibaca kembali.
6. Uji pada staging, lalu gunakan client terpisah untuk production.

Untuk rotasi, siapkan perubahan di E-Perpustakaan, pilih **Rotasi Secret**, simpan secret baru segera, lalu deploy. Rotasi langsung membatalkan secret lama.

## Contoh konfigurasi Laravel E-Perpustakaan

Contoh memakai `socialiteproviders/openid-connect` sebagai relying-party/client adapter. Install versi yang kompatibel dengan versi Laravel pada aplikasi eksternal. Callback/secret hanya placeholder environment — jangan commit nilai nyata.

```dotenv
OIDC_ISSUER=https://siakad.unmarissumba.ac.id
OIDC_CLIENT_ID=isi-dari-admin-siakad
OIDC_CLIENT_SECRET=isi-di-secret-manager-jangan-di-commit
OIDC_REDIRECT_URI=https://perpustakaan.unmarissumba.ac.id/auth/callback
OIDC_SCOPES=openid profile email siakad_identity account_status
```

Contoh konfigurasi konsep di `config/services.php` eksternal:

```php
'openid_connect' => [
    'base_url' => env('OIDC_ISSUER'),
    'client_id' => env('OIDC_CLIENT_ID'),
    'client_secret' => env('OIDC_CLIENT_SECRET'),
    'redirect' => env('OIDC_REDIRECT_URI'),
    'scopes' => preg_split('/\s+/', trim(env('OIDC_SCOPES', 'openid profile email'))),
],
```

Gunakan library OIDC RP yang membaca discovery document; jangan menyalin token ke log/session URL. Library tersebut hanya dipasang di aplikasi perpustakaan, bukan di Siakad. Pastikan RP memvalidasi tanda tangan ID token dengan JWKS, `iss`, `aud`, `exp`, `nonce`, dan `state`, serta menggunakan PKCE `S256`.

## Sandbox aman

- Gunakan deployment non-production dengan data uji saja dan client khusus.
- Redirect loopback lokal dibatasi ke `http://localhost` / `http://127.0.0.1` yang dicatat tepat pada konfigurasi client. Siakad production tetap menolak HTTP (`OIDC_ENABLE_HTTP_LOCAL=false`).
- Gunakan scope minimum dan lifetime pendek. Simpan secret di environment lokal/secret manager, bukan `.env.example`, chat, tiket, atau repo.
- Jangan pernah memakai secret production untuk tes. Setelah tes, nonaktifkan/hapus client sandbox dan revoke token-nya.

## Logout, pencabutan, dan rotasi kunci

- End-session membersihkan session Siakad dan hanya redirect ke URI post-logout yang terdaftar untuk client tersebut. End-session **tidak** mencabut semua token otomatis.
- Admin dapat memilih **Cabut Semua Token** pada client untuk mencabut access dan refresh tokens. Menonaktifkan client juga menolak permintaan baru; gunakan revoke token saat perlu memutus sesi/token yang telah terbit.
- RP dapat memanggil `/oauth/revoke` dengan client ID/secret dan token. Token tidak dikenal tetap mendapat respons sukses sesuai RFC 7009.
- `/oauth/introspect` hanya menerima client confidential terdaftar dengan secret yang valid.
- Rotasi key: terbitkan pasangan kunci RSA baru dan key ID baru; publikasikan public key baru di JWKS. Pertahankan public key lama sampai seluruh ID token bertanda tangan lama kedaluwarsa, lalu hapus. Rencana rotasi terkoordinasi diperlukan karena Passport access token juga menggunakan kunci RSA yang sama.

## Tes dan deployment

Migrasi menambahkan tabel OAuth Passport dan menyesuaikan `user_id` menjadi UUID. Sesudah backup dan migrasi resmi pada staging:

```bash
php artisan migrate --force
php artisan route:list | grep -E 'oauth|oidc|well-known'
php artisan test --filter=Oidc
vendor/bin/pint --test app/Http/Controllers/Oidc app/Services/Oidc app/Providers/OidcServiceProvider.php app/Filament/Resources/OidcClients tests/Feature/Oidc
```

`phpunit.xml` memakai MySQL database bernama `siakad`, jadi tes OIDC membuat baris dengan suffix acak dan membersihkan baris yang dibuatnya sendiri di `tearDown()`. Jangan aktifkan `RefreshDatabase` dan jangan jalankan `migrate:fresh`.

## Nilai operasional yang harus dikonfirmasi admin

- Host issuer publik dan TLS/reverse-proxy forwarding.
- Populasi yang diizinkan (mahasiswa/dosen/pegawai/admin) dan scope yang boleh dipakai E-Perpustakaan.
- Definisi aktif bagi mahasiswa/dosen/pegawai, termasuk perilaku bila data akademik tahun aktif tidak tersedia.
- Callback production, post-logout URI, dan callback sandbox lokal yang tepat.
- Lifetime token, siapa yang boleh membuat/menonaktifkan/rotate client, serta jadwal rotasi private key.
- Apakah logout harus hanya mengakhiri sesi perpustakaan atau juga mencabut token.
