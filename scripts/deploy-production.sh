#!/usr/bin/env bash
set -Eeuo pipefail
umask 022

fail() {
    printf 'Deploy gagal: %s\n' "$*" >&2
    exit 1
}

[[ $# -eq 4 ]] || fail 'Penggunaan: deploy-production.sh arsip sha database url-login'
archive=$1
revision=$2
database=$3
login_url=$4
app_dir=/opt/kasir-nirwana
releases="$app_dir/releases"
current="$app_dir/current"

[[ $revision =~ ^[a-f0-9]{40}$ && -r $archive ]] || fail 'Arsip atau SHA rilis tidak valid.'
[[ -L $current && -f $current/artisan ]] || fail "Siapkan symlink $current ke aplikasi yang aktif."
[[ -r $app_dir/.env && -w $app_dir/storage/framework ]] || fail 'Periksa akses deployment ke .env dan storage/framework.'

mkdir -p -- "$releases"
exec 9>"$releases/.deploy.lock"
flock -n 9 || fail 'Deploy lain sedang berjalan di LXC.'
[[ ! -e $app_dir/storage/framework/down ]] || fail 'Aplikasi masih dalam mode maintenance. Periksa deployment sebelumnya.'

previous=$(readlink -f -- "$current")
release=$(mktemp -d -- "$releases/$revision.XXXXXXXX")
chmod 755 -- "$release"
tar --no-same-owner -xzf "$archive" -C "$release"
[[ $(<"$release/REVISION") == "$revision" ]] || fail 'SHA di dalam paket tidak cocok.'
[[ -f $release/vendor/autoload.php && -f $release/public/build/manifest.json ]] || fail 'Paket belum lengkap.'

ln -s -- "$app_dir/.env" "$release/.env"
ln -s -- "$app_dir/storage" "$release/storage"
ln -s -- "$app_dir/storage/app/public" "$release/public/storage"

cd "$release"
php artisan package:discover
release_config=$(php -r '
    require "vendor/autoload.php";
    $app = require "bootstrap/app.php";
    $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    $connection = config("database.default");
    printf("%s:%s:%s", config("app.maintenance.driver"), config("database.connections.$connection.driver"), config("database.connections.$connection.database"));
')
[[ $release_config == "file:mariadb:$database" || $release_config == "file:mysql:$database" ]] ||
    fail 'Periksa APP_MAINTENANCE_DRIVER=file dan kesesuaian nama database MariaDB untuk backup.'

printf 'Rilis aktif: %s\nRilis baru: %s\n' "$previous" "$release"
trap 'printf "Deploy terhenti di baris %s. Periksa mode maintenance dan status migrasi sebelum memulihkan layanan.\n" "$LINENO" >&2' ERR
php "$previous/artisan" down
if ! DUMP_CLIENT=mariadb-dump bash scripts/backup-db.sh \
    /etc/kasir-nirwana/backup-client.cnf "$database" /mnt/kasir-backup; then
    php "$previous/artisan" up
    fail 'Backup gagal. Rilis sebelumnya sudah diaktifkan kembali.'
fi
php artisan migrate --force
php artisan optimize

next_link="$app_dir/.current-${release##*/}"
ln -s -- "$release" "$next_link"
mv -Tf -- "$next_link" "$current"
php artisan up

status=$(curl --silent --show-error --max-time 20 --proto '=http,https' \
    --output /dev/null --write-out '%{http_code}' -- "$login_url")
[[ $status == 200 ]] || fail "Smoke test login mendapat HTTP $status. Periksa rilis $release."
printf 'Deploy %s berhasil. Login HTTP %s.\n' "$revision" "$status"
