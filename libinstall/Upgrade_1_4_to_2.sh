#!/bin/bash
#----
## @Synopsis	Upgrade script version 1.4 to 2.0 for centreon
## @Copyright	Copyright 2008, Guillaume Watteeux
## @licence	GPL : http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
## TODO
## * Backup CENTREON INSTALL DIR (www, cron, ...)
## * Find cron (file or user) & backup
## * Quid RRD base
## * SQL changes
## * Clean unused files ... 
## * Remove ODS
## * ...

#----
# Centreon Upgrade script
#################################
# SVN: $Id$

echo "$line"
echo -e "\t$(gettext "Start Centreon Prepare Upgrade 1.4.x to 2.x")"
echo "$line"


# I'm writing all upgrade functon on this script. And when all people will stop use centreon1.4. We just delete or unuse this script.

#---
## Define directory where I move old centreon install.
## @Globals	CENTREON_BACKUPDIR, DEFAULT_CENTREON_BACKUPDIR
#---
function locate_centreon_backupdir() {
	if [ -z "$CENTREON_BACKUPDIR" ] ; then 
		answer_with_createdir "$(gettext "Where do you want to backup your old centreon ?")" "$DEFAULT_CENTREON_BACKUPDIR" "CENTREON_BACKUPDIR"
		echo_success "$(gettext "Path") : $CENTREON_BACKUPDIR"
	elif [ ! -d "$CENTREON_BACKUPDIR" -a "$silent_install" -eq 1 ] ; then
		mkdir -p "$CENTREON_BACKUPDIR"
		log "INFO" "$(gettext "Create") $CENTREON_BACKUPDIR"
	fi
	CENTREON_BACKUPDIR=${CENTREON_BACKUPDIR%/}
	export CENTREON_BACKUPDIR
	log "INFO" "CENTREON_BACKUPDIR: $CENTREON_BACKUPDIR"
}

#----
## search in system who cron was define. 
## @Globals	CRON_FILE
## need to valid there are not a user crontab (nagios)
## if /etc/cron.d/oreon or centreon exist, move and add by new install.
#----
function find_cron() {
	local cron_type=""
	local cron_type_choice="file user"
	echo -e "$(gettext "Please select a type of cron method")"
	select_in_array	"cron_type" "${cron_type_choice[@]}"
	if [ "$cron_type" = "user" ] ; then 
		is_cron_user
	elif [ "$cron_type" = "file" ] ; then
		is_cron_file
	else
		return 1
	fi

	return 0
}

## find in nagios user if crontab centreon exist and WARNING

#----
## Find in cron file if centreon's cron exist
#----
function is_cron_file() {
	
}

#----
## Find in cron user if centreon's cron exist
#----
function is_cron_user() {

}
