#!/bin/bash

###################################################
# Centreon                      Octobre 2015
#
# This script must be run from centreon-backup
#
###################################################

# Save or not data_bin and logs tables (save by default)
OPT_PARTIAL=0
BACKUP_DIR="/var/cache/centreon/backup"
DEBUG=0

while getopts "pb:l:Dd:" option
do
    case $option in
        p)
            OPT_PARTIAL=1 ;;
        b)
            BACKUP_DIR="$OPTARG"
            ;;
        D)
            DEBUG=1
            ;;
        d)
            today="$OPTARG"
            ;;
        :)
            echo "L'option $OPTARG requiert un argument"
            exit 1
            ;;
        \?)
            echo "$OPTARG : option invalide"
            exit 1
            ;;
    esac
done
shift $((OPTIND-1))

###########################################
# SANITY CHECK
###########################################

# minimum Go
VG_FREESIZE_NEEDED=1
STOP_TIMEOUT=60
SNAPSHOT_MOUNT="/mnt/snap-backup"
SAVE_LAST_DIR="/var/lib/centreon-backup"
SAVE_LAST_FILE="backup.last"
DO_ARCHIVE=1
INIT_SCRIPT="" # try to find it
PARTITION_NAME="centreon_storage/data_bin centreon_storage/logs"

###
# Check MySQL launch
###
process=$(ps -o args --no-headers -C mysqld)
started=0

#####
# Functions
#####
output_log() {
	error="$2"
	no_cr=""
	if [ -n "$3" ] && [ "$3" -eq "1" ] ; then
		no_cr="-n"
	fi
    if [ $DEBUG -eq '1' ] ; then
        status='DEBUG'
    else
        status='ERROR'
    fi
	if [[ ( -n "$error" && "$error" -eq "1") || $DEBUG -eq '1' ]] ; then
		echo $no_cr "[$status] [centreon-backup-mysql.sh]" $1
	fi
}

###
# Find datadir
###
if [ -n "$process" ] ; then
	datadir=$(echo "$process" | awk '{ for (i = 1; i < NF; i++) { if (match($i, "--datadir")) { print $i } } }' | awk -F\= '{ print $2 }')
	started=1
fi
if [ -z "$datadir" ] ; then
	output_log "ERROR: Can't find MySQL datadir." 1
	exit 1
fi
### Avoid datadir is a symlink (get the absolute path)
datadir=$(cd "$datadir"; pwd -P)
output_log "MySQL datadir finded: $datadir"

# Get init script
if [ -e "/etc/init.d/mysql" ] ; then
        INIT_SCRIPT="/etc/init.d/mysql"
fi
if [ -e "/etc/init.d/mysqld" ] ; then
        INIT_SCRIPT="/etc/init.d/mysqld"
fi
if [ -z "$INIT_SCRIPT" ] ; then
        output_log "ERROR: Can't find init MySQL script." 1
        exit 1
fi

###
# Get mount
###
mount_device=$(df -P "$datadir" | tail -1 | awk '{ print $1 }')
mount_point=$(df -P "$datadir" | tail -1 | awk '{ print $6 }')
if [ -z "$mount_device" ] ; then
	output_log "ERROR: Can't get mount device for datadir." 1
	exit 1
fi
if [ -z "$mount_point" ] ; then
	output_log "ERROR: Can't get mount point for datadir." 1
	exit 1
fi
output_log "Mount device finded: $mount_device"
output_log "Mount point finded: $mount_point"

###
# Get Volume group Name
###
vg_name=$(lvdisplay -c "$mount_device" | cut -d : -f 2)
lv_name=$(lvdisplay -c "$mount_device" | cut -d : -f 1)
if [ -z "$vg_name" ] ; then
	output_log "ERROR: Can't get VolumeGroup name for datadir." 1
	exit 1
fi
if [ -z "$lv_name" ] ; then
	output_log "ERROR: Can't get LogicalVolume name for datadir." 1
	exit 1
fi
output_log "VolumeGroup finded: $vg_name"

###
# Get free Space
###
free_pe=$(vgdisplay -c "$vg_name" | cut -d : -f 16)
size_pe=$(vgdisplay -c "$vg_name" | cut -d : -f 13)
if [ -z "$free_pe" ] ; then
	output_log "ERROR: Can't get free PE value for the VolumeGroup." 1
	exit 1
fi
if [ -z "$size_pe" ] ; then
	output_log "ERROR: Can't get size PE value for the VolumeGroup." 1
	exit 1
fi

free_total_pe=$(echo $free_pe " " $size_pe | awk '{ print ($1 * $2) / 1024 / 1024 }')
output_log "Free total size in VolumeGroup (Go): $free_total_pe"

echo "$free_total_pe $VG_FREESIZE_NEEDED" | awk '{ if ($2 > $1) { exit(1) } else { exit(0) } }'
if [ "$?" -eq 1 ] ; then
	output_log "ERROR: Not enough free space in the VolumeGroup." 1
	exit 1
fi

###
# Create BACKUP DIR
###
if [ "$DO_ARCHIVE" -eq "0" ] ; then
	BACKUP_DIR_TOTAL="$BACKUP_DIR/$today-mysql"
else
	BACKUP_DIR_TOTAL="$BACKUP_DIR"
fi
mkdir -p "$BACKUP_DIR_TOTAL"
if [ ! -d "$BACKUP_DIR_TOTAL" ] ; then
	output_log "ERROR: Directory '$BACKUP_DIR_TOTAL' doesn't exist." 1
	exit 1
fi

###
# Check Last DIR
###
mkdir -p "$SAVE_LAST_DIR"
if [ ! -f "$SAVE_LAST_DIR/$SAVE_LAST_FILE" ] ; then
	touch "$SAVE_LAST_DIR/$SAVE_LAST_FILE"
fi
if [ ! -w "$SAVE_LAST_DIR/$SAVE_LAST_FILE" ] ; then
	output_log "ERROR: Don't have permission on '$SAVE_LAST_DIR/$SAVE_LAST_FILE' file." 1
	exit 1
fi
#############
############# END SANITY CHECK
#############

###########################################
# Beginning
###########################################
echo "#####################"
echo "Full backup launched:"
echo "#####################"
###
# We need to stop if needed
###
if [ "$started" -eq 1 ] ; then
	i=0
	output_log "Stopping mysqld:" 0 1
	$INIT_SCRIPT stop
	while ps -o args --no-headers -C mysqld >/dev/null; do
		if [ "$i" -gt "$STOP_TIMEOUT" ] ; then
			output_log ""
			output_log "ERROR: Can't stop MySQL Server" 1
			exit 1
		fi
		output_log "." 0 1
		sleep 1
		i=$(($i + 1))
	done
	output_log "OK"
fi

save_timestamp=$(date '+%s')

###
# Do snapshot
###
output_log "Create LVM snapshot"
lvcreate -l $free_pe -s -n dbbackup $lv_name

###
# Start server
###
output_log "Start mysqld:"
$INIT_SCRIPT start

###
# Mount snapshot
###
output_log "Mount LVM snapshot"
mkdir -p "$SNAPSHOT_MOUNT"
mount /dev/$vg_name/dbbackup "$SNAPSHOT_MOUNT"
if [ $? -eq 0 ]; then
    output_log "Device mounted successfully"
else
    output_log "Unable to mount device, backup aborted"
    lvremove -f /dev/$vg_name/dbbackup
    exit 1;
fi

concat_datadir=$(echo "$datadir" | sed "s#^${mount_point}##")

###
# Do DB save
###
ar_exclude_file=""
last_save_time=$(cat "$SAVE_LAST_DIR/$SAVE_LAST_FILE")

if [ $OPT_PARTIAL -eq 1 ] ; then
    for table in $PARTITION_NAME ; do
    	tmp_dir=$(dirname "$table")
    	tmp_name=$(basename "$table")
    	tmp_path=$(echo "$SNAPSHOT_MOUNT/$concat_datadir/$tmp_dir" | sed "s#/\+#/#g")
    	for tmp_file in $(find "$tmp_path" -name "$tmp_name*" -type f); do
    		ar_exclude_file="$ar_exclude_file \"$tmp_file\""
    	done
    done
fi
save_files=""
tmp_path=$(echo "$SNAPSHOT_MOUNT/$concat_datadir" | sed "s#/\+#/#g")
for tmp_file in $(find "$tmp_path" -type f); do
	tmp_result=$(echo $tmp_file | awk -v excludefiles="$ar_exclude_file" '{ if (match(excludefiles, "\"" $0 "\"")) {  print "OK"; exit(0) } } { print "NOK"; exit (0) }')
	if [ "$tmp_result" = "NOK" ] ; then
		tmp_file=$(echo "$tmp_file" | sed "s#^$SNAPSHOT_MOUNT/##")
		save_files="$save_files \"$tmp_file\""
	fi
done

output_log "Save files"
cd $SNAPSHOT_MOUNT
if [ "$DO_ARCHIVE" -eq "0" ] ; then
	eval cp --parent -pf $save_files \"$BACKUP_DIR_TOTAL/\"
else
    if [ $OPT_PARTIAL -eq 1 ] ; then
	eval tar czvf \"$BACKUP_DIR_TOTAL/$today-mysql-partial.tar.gz\" $save_files
    else
        eval tar czvf \"$BACKUP_DIR_TOTAL/$today-mysql-full.tar.gz\" $save_files
    fi
fi
cd -

###
# Suppression du snapshot
###
output_log "Umount and Delete LVM snapshot"
umount "$SNAPSHOT_MOUNT"
lvremove -f /dev/$vg_name/dbbackup

echo "$save_timestamp" > "$SAVE_LAST_DIR/$SAVE_LAST_FILE"

exit 0
