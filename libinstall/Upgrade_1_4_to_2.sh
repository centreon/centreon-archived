#!/bin/bash
# Centreon Upgrade script
#################################
# SVN: $Id$

echo "$line"
echo -e "\t$(gettext "Start Centreon Prepare Upgrade 1.4.x to 2.x")"
echo "$line"

## TODO
# * Backup CENTREON INSTALL DIR (www, cron, ...)
# * Find cron (file or user) & backup
# * Quid RRD base
# * SQL changes
# * Clean unused files ... 
# * Remove ODS
# * ...


# I'm writing all upgrade functon on this script. And when all people will stop use centreon1.4. We just delete or unuse this script.

## Function locate_centreon_backupdir
# define directory where I move old centreon install.
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

## Function find_cron
# search in system who cron was define. 
# need to valid there are not a user crontab (nagios)
# if /etc/cron.d/oreon or centreon exist, move and add by new install.
function find_cron() {

}


