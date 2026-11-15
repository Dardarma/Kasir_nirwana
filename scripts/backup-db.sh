#!/usr/bin/env bash
set -Eeuo pipefail
umask 077

fail() {
    printf 'Backup gagal: %s\n' "$*" >&2
    exit 1
}

[[ $# -eq 3 ]] || fail "Penggunaan: $0 /path/client.cnf nama_database /mount/backup"
client_config=$1
database=$2
backup_dir=$3

[[ $client_config == /* && -f $client_config && -r $client_config ]] ||
    fail 'File konfigurasi client harus berupa path absolut dan bisa dibaca.'
[[ $database =~ ^[a-zA-Z0-9_]+$ ]] || fail 'Nama database harus berisi huruf, angka, atau underscore.'
[[ $backup_dir == /* ]] || fail 'Path tujuan backup harus absolut.'
mountpoint -q -- "$backup_dir" || fail "Penyimpanan backup belum terpasang di $backup_dir."

case ${DUMP_CLIENT:-mysqldump} in
    mysqldump) dump_command=(mysqldump --no-tablespaces --set-gtid-purged=OFF) ;;
    mariadb-dump) dump_command=(mariadb-dump) ;;
    *) fail 'DUMP_CLIENT harus mysqldump atau mariadb-dump.' ;;
esac

exec 9>"$backup_dir/.${database}.lock"
flock -n 9 || fail "Backup $database sedang berjalan."

archive="$backup_dir/${database}-$(date -u +%Y%m%dT%H%M%S%NZ).sql.gz"
temp_file=$(mktemp -- "$backup_dir/.${database}.XXXXXXXX.sql.gz.part")
trap 'rm -f -- "$temp_file"' EXIT
trap 'exit 1' HUP INT TERM

if ! "${dump_command[0]}" --defaults-file="$client_config" "${dump_command[@]:1}" \
    --single-transaction --quick --no-create-db --skip-add-drop-table "$database" |
    gzip -c > "$temp_file"; then
    fail "Dump atau kompresi database $database gagal."
fi

gzip -t -- "$temp_file" || fail 'Pemeriksaan arsip gzip gagal.'
mv -- "$temp_file" "$archive"
printf 'Backup berhasil: %s\n' "$archive"
