#!/bin/bash

SOURCE_DB=centreon
DEST_DB=centreon_storage
TMP_DIR=/tmp
DROP_DB=0
VERBOSE=0
SCRIPT_RENAME="$(dirname $(dirname $0))/sql/renameTables.sql"

usage() {
  echo -e "Usage: $1 [-s source] [-d dest] [-u dbuser] [-p dbpass] [-H dbhost] [-t tmp_dir] [-v] [-D]"
  echo -e "\t-s\tsource\tThe database source (Default : centreon)"
  echo -e "\t-d\tdest\tThe database destination (Default : centreon_storage)"
  echo -e "\t-t\ttmp_dir\tThe temporary directory (Default : /tmp)"
  echo -e "\t-u\tdbuser\tThe database user"
  echo -e "\t-p\tdbpass\tThe database password"
  echo -e "\t-H\tdbhost\tThe database host"
  echo -e "\t-D\t\tDrop source database"
  echo -e "\t-v\t\tverbose mode"
  exit $2
}

clean_exit() {
  if [ -w "$1" ]; then
    rm "$1"
  fi
  exit $2
}

log() {
  if [ ${VERBOSE} -eq 1 ]; then
    echo $*
  fi
}

# Get script options
while getopts "s:d:t:hv" o; do
  case "${o}" in
    s)
      SOURCE_DB="${OPTARG}"
      ;;
    d)
      DEST_DB="${OPTARG}"
      ;;
    t)
      TMP_DIR="${OPTARG}"
      if [ ! -d "${TMP_DIR}" -o ! -w "${TMP_DIR}" ]; then
        echo "The temporary directory does not exist or is not writable" >&2
        usage "$(basename $0)" 1
      fi
      ;;
    u)
      DB_USER="${OPTARG}"
      ;;
    p)
      DB_PASS="${OPTARG}"
      ;;
    H)
      DB_HOST="${OPTARG}"
      ;;
    D)
      DROP_DB=1
      ;;
    v)
      VERBOSE=1
      ;;
    h|*)
      usage "$(basename $0)" 0
      ;;
  esac
done

# Test if the rename script file exists
if [ ! -f "${SCRIPT_RENAME}" ]; then
  echo "The script file for rename table does not exists." >&2
  exit 1
fi

# Build mysql command arguments
MYSQL_ARGS=""
if [ -n "${DB_HOST}" ]; then
  MYSQL_ARGS="${MYSQL_ARGS} -h ${DB_HOST}"
fi
if [ -n "${DB_USER}" ]; then
  MYSQL_ARGS="${MYSQL_ARGS} -u ${DB_USER}"
fi
if [ -n "${DB_PASS}" ]; then
  MYSQL_ARGS="${MYSQL_ARGS} -p${DB_PASS}"
fi

# Temporary file for dump
TMP_DUMP=$(mktemp --tmpdir="${TMP_DIR}")

log "Get data from source database (${SOURCE_DB})."
mysqldump ${MYSQL_ARGS} "${SOURCE_DB}" > "${TMP_DUMP}" 2>/dev/null
if [ $? -ne 0 ]; then
  echo "Error in dump of source database" >&2
  clean_exit "${TMP_DUMP}" 1
fi

log "Insert source database (${SOURCE_DB}) into destination database (${DEST_DB})."
mysql ${MYSQL_ARGS} "${DEST_DB}" < "${TMP_DUMP}" &>/dev/null
if [ $? -ne 0 ]; then
  echo "Error in import ${SOURCE_DB} in ${DEST_DB}" >&2
  clean_exit "${TMP_DUMP}" 1
fi

log "Rename tables in destination database : ${DEST_DB}"
mysql ${MYSQL_ARGS} "${DEST_DB}" < "${SCRIPT_RENAME}"
if [ $? -ne 0 ]; then
  echo "Error in renaming tables" >&2
  clean_exit "${TMP_DUMP}" 1
fi

if [ "${DROP_DB}" -eq 1 ]; then
  log "Drop the source database : ${SOURCE_DB}."
  mysql ${MYSQL_ARGS} -e "DROP DATABASE ${SOURCE_DB}" &>/dev/null
  if [ $? -ne 0 ]; then
    echo "Error to drop the source database" >&2
    clean_exit "${TMP_DUMP}" 1
  fi
fi

clean_exit "${TMP_DUMP}" 0
