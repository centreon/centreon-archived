#!/bin/bash
#----
## @Synopsis	Install script for CentPluginsTraps
## @Copyright	Copyright 2008, Guillaume Watteeux
## @license	GPL : http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
## Install script for CentPluginsTraps
#----
# install script for CentPlugins
#################################
# SVN: $Id$

echo "$line"
echo -e "\t$(gettext "Start CentPlugins Traps Installation")"
echo "$line"

###### Check disk space
check_tmp_disk_space
[ "$?" -eq 1 ] && purge_centreon_tmp_dir

## Where is nagios_pluginsdir
locate_nagios_plugindir

## Locate centreon etc_dir
locate_centreon_etcdir
locate_snmp_etcdir
locate_snmptt_bindir
locate_centpluginstraps_bindir

check_group_nagios
check_httpd_directory
check_user_apache

## Populate temporaty source directory
copyInTempFile 2>>$LOG_FILE

## Create temporary folder
log "INFO" "$(gettext "Create working directory")"
mkdir -p $TMPDIR/final/bin
mkdir -p $TMPDIR/work/bin
mkdir -p $TMPDIR/work/snmptrapd
mkdir -p $TMPDIR/final/snmptrapd
mkdir -p $TMPDIR/work/snmptt
mkdir -p $TMPDIR/final/snmptt

## Change Macro in working dir
log "INFO" "$(gettext "Change macros for ")centFillTrapDB, centGenSnmpttConfFile, centTrapHandler-2.x"
for FILE in  $TMPDIR/src/bin/centFillTrapDB \
	$TMPDIR/src/bin/centGenSnmpttConfFile \
	$TMPDIR/src/bin/centTrapHandler-2.x ; do

	sed -e 's|@CENTREON_ETC@|'"$CENTREON_ETC"'|g' \
		"$FILE" > "$TMPDIR/work/bin/`basename $FILE`"
done

## Copy in final dir
log "INFO" "$(gettext "Copying Traps binaries in final directory")"
cp -f $TMPDIR/work/bin/* $TMPDIR/final/bin >> $LOG_FILE 2>&1

## Install the plugins traps binaries
log "INFO" "$(gettext "Installing the plugins Traps binaries")"
$INSTALL_DIR/cinstall $cinstall_opts \
	-m 755 -p $TMPDIR/final/bin \
	$TMPDIR/final/bin/* $CENTPLUGINSTRAPS_BINDIR >> $LOG_FILE 2>&1
echo_success "$(gettext "Installing the plugins Trap binaries ")" "$ok"

# Create a SNMP config
## Create centreon_traps directory
$INSTALL_DIR/cinstall $cinstall_opts \
	-u $WEB_USER -g $NAGIOS_GROUP -d 775 \
	$SNMP_ETC/centreon_traps >> $LOG_FILE 2>&1

log "INFO" "$(gettext "Backup all your snmp files")"
# Backup snmptrapd.conf if exist
if [ -e "$SNMP_ETC/snmptrapd.conf" ] ; then
	log "INFO" "$(gettext "Backup") : $SNMP_ETC/snmptrapd.conf"
	mv $SNMP_ETC/snmptrapd.conf $SNMP_ETC/snmptrapd.conf.bak-centreon
fi
# Backup snmptt.ini 
if [ -e "$SNMP_ETC/centreon_traps/snmptt.ini" ] ; then
	log "INFO" "$(gettext "Backup") : $SNMP_ETC/centreon_traps/snmptt.ini"
	mv $SNMP_ETC/centreon_traps/snmptt.ini \
		$SNMP_ETC/centreon_traps/snmptt.ini.bak-centreon
fi
# Backup snmp.conf if exist
if [ -e "$SNMP_ETC/snmp.conf" ] ; then
	log "INFO" "$(gettext "Backup") : $SNMP_ETC/snmp.conf"
	mv $SNMP_ETC/snmp.conf $SNMP_ETC/snmp.conf.bak-centreon
fi

# Backup snmptt if exist
if [ -e "$SNMPTT_BINDIR/snmptt" ] ; then
	log "INFO" "$(gettext "Backup") : $SNMPTT_BINDIR/snmptt"
	mv $SNMPTT_BINDIR/snmptt $SNMPTT_BINDIR/snmptt.bak-centreon
fi

# Backup snmptt if exist
if [ -e "$SNMPTT_BINDIR/snmpttconvertmib" ] ; then
	log "INFO" "$(gettext "Backup") : $SNMPTT_BINDIR/snmpttconvertmib"
	mv $SNMPTT_BINDIR/snmpttconvertmib \
		$SNMPTT_BINDIR/snmpttconvertmib.bak-centreon
fi
echo_success "$(gettext "Backup all your snmp files")" "$ok"

log "INFO" "$(gettext "Installing snmptt")"
# Change macros on snmptrapd.conf
sed -e 's|@SNMPTT_INI_FILE@|'"$SNMP_ETC/centreon_traps/snmptt.ini"'|g' \
	-e 's|@SNMPTT_BINDIR@|'"$SNMPTT_BINDIR"'|g' \
	$TMPDIR/src/snmptrapd/snmptrapd.conf > \
	$TMPDIR/work/snmptrapd/snmptrapd.conf 2>>$LOG_FILE

# Change macros on snmptt.ini
# TODO: SNMPTT_LOG, SNMPTT_SPOOL
sed -e 's|@SNMP_ETC@|'"$SNMP_ETC"'|g' \
	$TMPDIR/src/snmptt/snmptt.ini > $TMPDIR/work/snmptt/snmptt.ini \
	2>>$LOG_FILE

## Copy in final dir
log "INFO" "$(gettext "Copying traps config in final directory")"
cp -r $TMPDIR/work/snmptrapd/snmptrapd.conf \
	$TMPDIR/final/snmptrapd/snmptrapd.conf >> $LOG_FILE 2>&1
cp $TMPDIR/work/snmptt/snmptt.ini \
	$TMPDIR/final/snmptt/snmptt.ini >> $LOG_FILE 2>&1
cp $TMPDIR/src/snmptrapd/snmp.conf \
	$TMPDIR/final/snmptrapd/snmp.conf >> $LOG_FILE 2>&1
cp $TMPDIR/src/snmptt/snmptt \
	$TMPDIR/final/snmptt/snmptt >> $LOG_FILE 2>&1
cp $TMPDIR/src/snmptt/snmpttconvertmib \
	$TMPDIR/final/snmptt/snmpttconvertmib >> $LOG_FILE 2>&1


## Install all config file
log "INFO" "$(gettext "Install") : snmptrapd.conf"
$INSTALL_DIR/cinstall $cinstall_opts -m 644 \
	$TMPDIR/final/snmptrapd/snmptrapd.conf \
	$SNMP_ETC/snmptrapd.conf >> $LOG_FILE 2>&1

log "INFO" "$(gettext "Install") : snmp.conf"
$INSTALL_DIR/cinstall $cinstall_opts -m 644 \
	$TMPDIR/final/snmptrapd/snmp.conf \
	$SNMP_ETC/snmp.conf >> $LOG_FILE 2>&1

log "INFO" "$(gettext "Install") : snmptt.ini"
$INSTALL_DIR/cinstall $cinstall_opts -u $WEB_USER -g $NAGIOS_GROUP -m 644 \
	$TMPDIR/final/snmptt/snmptt.ini \
	$SNMP_ETC/centreon_traps/snmptt.ini >> $LOG_FILE 2>&1

log "INFO" "$(gettext "Install") : snmptt"
$INSTALL_DIR/cinstall $cinstall_opts -m 755 \
	$TMPDIR/final/snmptt/snmptt \
	$SNMPTT_BINDIR/snmptt >> $LOG_FILE 2>&1

log "INFO" "$(gettext "Install") : snmpttconvertmib"
$INSTALL_DIR/cinstall $cinstall_opts -m 755 \
	$TMPDIR/final/snmptt/snmpttconvertmib \
	$SNMPTT_BINDIR/snmpttconvertmib >> $LOG_FILE 2>&1

echo_success "$(gettext "Install SNMPTT")" "$ok"
## TODO : comment ^^ , log and echo_*
#	: copy centreon.pm and centreon.conf if not exist


