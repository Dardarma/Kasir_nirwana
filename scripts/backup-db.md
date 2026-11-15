# Backup database Kasir Nirwana

Skrip ini membuat dump MySQL atau MariaDB yang dikompresi dengan gzip. Jalankan
dari LXC Linux dengan Bash, klien dump yang sesuai dengan server database,
`gzip`, dan util-linux (`mountpoint`, `flock`). Untuk tabel InnoDB,
`--single-transaction` mengambil snapshot yang konsisten. Jadwalkan backup
di luar waktu migrasi skema.

## Pasang di LXC

1. Siapkan storage di luar host Proxmox dan pastikan
   `/mnt/kasir-backup` terdeteksi sebagai titik mount **di dalam LXC**.
   Beri akun yang menjalankan cron akses tulis ke lokasi tersebut. Skrip
   menyimpan arsip langsung di mount agar backup gagal jika storage lepas.
2. Buat `/etc/kasir-nirwana/backup-client.cnf` dengan pemilik akun cron
   dan permission `0600`. Isi dengan akun backup database. Untuk MySQL,
   gunakan `SELECT` pada database aplikasi dan tambahkan `SHOW VIEW` atau
   `TRIGGER` sesuai objek yang ada. Sesuaikan host dan port dengan server.

   ```ini
   [client]
   host=127.0.0.1
   port=3306
   user=kasir_backup
   password="GANTI_PASSWORD_BACKUP"
   ```

3. Jalankan sebagai akun yang akan dipakai cron. Ganti `kasir_nirwana`
   dengan nilai `DB_DATABASE` di server.

   ```bash
   /bin/bash /opt/kasir-nirwana/scripts/backup-db.sh \
     /etc/kasir-nirwana/backup-client.cnf kasir_nirwana /mnt/kasir-backup
   ```

   Untuk MariaDB, awali perintah dengan `DUMP_CLIENT=mariadb-dump`.
4. Pastikan zona waktu LXC `Asia/Jakarta` sebelum memasang jadwal
   pukul 22.00 WIB melalui `crontab -e` milik akun tersebut.
   Untuk MariaDB, tambahkan baris `DUMP_CLIENT=mariadb-dump` di crontab.

   ```cron
   0 22 * * * /bin/bash /opt/kasir-nirwana/scripts/backup-db.sh /etc/kasir-nirwana/backup-client.cnf kasir_nirwana /mnt/kasir-backup >> "$HOME/kasir-backup.log" 2>&1
   ```

   Nama arsip memakai waktu UTC. Siapkan retensi pada penyimpanan backup
   karena setiap eksekusi sukses menambah satu arsip.

## Skenario pengujian

| Skenario | Cara uji | Hasil yang diharapkan |
|---|---|---|
| Backup manual | Di database uji, buat transaksi yang mengubah stok. Jalankan skrip dengan akun cron dan catat path yang muncul. | Exit code 0, satu arsip `.sql.gz` baru, dan `gzip -t` pada arsip lolos. |
| Restore | Impor arsip ke database kosong bernama `kasir_restore_test` di server uji seperti contoh di bawah. | Impor selesai tanpa error. Transaksi uji, detail, dan stok hasil transaksi sesuai dengan sumber pada waktu backup. |
| Kredensial salah | Jalankan skrip dengan salinan file konfigurasi uji yang berisi password salah. | Exit code nonzero, muncul pesan gagal, dan tidak ada arsip final maupun file `.part` baru. |
| Storage tidak terpasang | Gunakan direktori kosong yang bukan titik mount sebagai argumen terakhir. | Exit code nonzero dan direktori tetap kosong. |
| Backup tumpang tindih | Di lingkungan uji, tahan lock dengan `flock /mnt/kasir-backup/.kasir_nirwana.lock sleep 30` lalu jalankan skrip pada terminal lain. | Proses kedua gagal dengan pesan backup sedang berjalan dan tidak membuat arsip. |
| Jadwal cron | Untuk pengujian awal, jadwalkan satu eksekusi pada menit terdekat. Periksa log dan timestamp arsip lalu kembalikan jadwal ke 22.00 WIB. | Entri sukses dari akun cron dan satu arsip baru di storage. |

Contoh restore MySQL dengan Bash. Buat database kosong `kasir_restore_test`
terlebih dahulu. Gunakan file konfigurasi yang menunjuk ke server uji dan
akun dengan akses hanya ke database restore tersebut. Untuk MariaDB, ganti
`mysql` dengan `mariadb`.

```bash
set -o pipefail
archive=/mnt/kasir-backup/NAMA_ARSIP.sql.gz
gzip -t -- "$archive" &&
  gzip -dc -- "$archive" |
    mysql --defaults-file=/path/restore-test.cnf kasir_restore_test
```
