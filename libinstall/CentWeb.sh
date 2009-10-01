#!/bin/bash
#----
## @Synopsis	Install script for Centreon Web Front (CentWeb)
## @Copyright	Copyright 2008, Guillaume Watteeux
## @license	GPL : http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
## Install script for Centreon Web Front (CentWeb)
#----
# Install script for Centreon Web Front
#################################
# SVN: $Id$

# debug ?
#set -x 

echo -e "\n$line"
echo -e "\t$(gettext "Start CentWeb Installation")"
echo -e "$line"

###### check space ton tmp dir
check_tmp_disk_space
[ "$?" -eq 1 ] && purge_centreon_tmp_dir

###### Require
#################################
## Create install_dir_centreon
locate_centreon_installdir
# Create an examples directory to save all important templates and config
[ ! -d $INSTALL_DIR_CENTREON/examples ] && \
	mkdir -p $INSTALL_DIR_CENTREON/examples

## locate or create Centreon log dir
locate_centreon_logdir
locate_centreon_etcdir
locate_centreon_generationdir
locate_centreon_varlib
locate_centreon_logdir
locate_centpluginstraps_bindir

## Config pre-require
# define all necessary variables.
locate_rrd_perldir
locate_rrdtool
locate_mail
locate_pear
locate_nagios_installdir
locate_nagios_etcdir
locate_nagios_vardir
locate_nagios_plugindir
locate_nagios_binary
locate_nagios_imgdir
locate_nagiostats_binary
locate_nagios_plugindir
locate_nagios_p1_file $NAGIOS_ETC
locate_cron_d
locate_init_d
locate_php_bin
locate_perl

## Config apache
check_httpd_directory
check_group_apache
check_user_apache
## Check Nagios config
check_user_nagios
check_group_nagios

## NDO binary
## IS NOT POSSIBLE TO USE CENTREON WITHOUT NDO !! CAUTION
if [ "${FORCE_NOT_USE_NDO:-0}" -eq 1 ] ; then 
	NDOMOD_BINARY="CAUTION_NOT_POSSIBLE_TO_USE_CENTREON_WITHOUT_NDO"
else 
	locate_ndomod_binary
fi
## For a moment, Centreon2 does not support when NDO not use.
## by default, I prefert force NDO usage. But you can use 
## FORCE_NOT_USE_NDO
#yes_no_default "$(gettext "Do you want to use NDO ?")" "$no"
#if [ "$?" -eq 0 ] ; then
#	log "INFO" "$(gettext "NDO use...")"
#	locate_ndomod_binary
#fi


## Config Sudo
# I think this process move on CentCore install...
configureSUDO "$INSTALL_DIR_CENTREON/examples"

## Config Apache
configureApache "$INSTALL_DIR_CENTREON/examples"

## Create temps folder and copy all src into
copyInTempFile 2>>$LOG_FILE 

## InstallCentreon

#echo "$line"
#echo -e "\t$(gettext "Start Centreon Web Front Installation")"
#echo -e "$line\n\n"

# change right centreon_log directory
log "INFO" "$(gettext "Change right on") $CENTREON_LOG"
$INSTALL_DIR/cinstall $cinstall_opts \
	-u "$WEB_USER" -g "$NAGIOS_GROUP" -d 775 \
	"$CENTREON_LOG" >> "$LOG_FILE" 2>&1
check_result $? "$(gettext "Change right on") $CENTREON_LOG"

# change right on centreon etc
log "INFO" "$(gettext "Change right on") $CENTREON_ETC"
$INSTALL_DIR/cinstall $cinstall_opts \
	-u "$WEB_USER" -d 755 \
	"$CENTREON_ETC" >> "$LOG_FILE" 2>&1
check_result $? "$(gettext "Change right on") $CENTREON_ETC"

# change right on nagios images/logos
log "INFO" "$(gettext "Change right on") $NAGIOS_IMG"
$INSTALL_DIR/cinstall $cinstall_opts \
	-u "$WEB_USER" -d 755 \
	"$NAGIOS_IMG" >> "$LOG_FILE" 2>&1
check_result $? "$(gettext "Change right on") $NAGIOS_IMG"

## Copy Web Front Source in final
log "INFO" "$(gettext "Copy CentWeb and GPL_LIB in temporary final directory")"
cp -Rf $TMP_DIR/src/www $TMP_DIR/final
cp -Rf $TMP_DIR/src/GPL_LIB $TMP_DIR/final

## Create temporary directory
mkdir -p $TMP_DIR/work/www/install >> "$LOG_FILE" 2>&1
mkdir -p $TMP_DIR/work/cron/reporting >> "$LOG_FILE" 2>&1
mkdir -p $TMP_DIR/final/cron/reporting >> "$LOG_FILE" 2>&1
mkdir -p $TMP_DIR/final/libinstall >> "$LOG_FILE" 2>&1

## Install Centreon doc (nagios doc)
$INSTALL_DIR/cinstall $cinstall_opts \
	-g $WEB_GROUP -d 755 -m 644 \
	$TMP_DIR/src/doc $INSTALL_DIR_CENTREON/doc >> $LOG_FILE 2>&1
check_result $? "$(gettext "Install nagios documentation")"

## Ticket #372 : add functions/cinstall fonctionnality
cp -Rf $TMP_DIR/src/libinstall/{functions,cinstall,gettext} \
  $TMP_DIR/final/libinstall/ >> "$LOG_FILE" 2>&1

## Prepare insertBaseConf.sql
#echo -e "$(gettext "In process")"
### Step 1:
## Change Macro on sql file
log "INFO" "$(gettext "Change macros for insertBaseConf.sql")"
${SED} -e 's|@NAGIOS_VAR@|'"$NAGIOS_VAR"'|g' \
	-e 's|@NAGIOS_BINARY@|'"$NAGIOS_BINARY"'|g' \
	-e 's|@NAGIOSTATS_BINARY@|'"$NAGIOSTATS_BINARY"'|g' \
	-e 's|@NAGIOS_IMG@|'"$NAGIOS_IMG"'|g' \
	-e 's|@INSTALL_DIR_NAGIOS@|'"$INSTALL_DIR_NAGIOS"'|g' \
	-e 's|@NAGIOS_USER@|'"$NAGIOS_USER"'|g' \
	-e 's|@NAGIOS_GROUP@|'"$NAGIOS_GROUP"'|g' \
	-e 's|@NAGIOS_ETC@|'"$NAGIOS_ETC"'|g' \
	-e 's|@NAGIOS_PLUGIN@|'"$NAGIOS_PLUGIN"'|g' \
	-e 's|@NAGIOS_BIN@|'"$NAGIOS_BIN"'|g' \
	-e 's|@NAGIOS_INIT_SCRIPT@|'"$NAGIOS_INIT_SCRIPT"'|g' \
	-e 's|@RRDTOOL_PERL_LIB@|'"$RRD_PERL"'|g' \
	-e 's|@INSTALL_DIR_CENTREON@|'"$INSTALL_DIR_CENTREON"'|g' \
	-e 's|@BIN_RRDTOOL@|'"$BIN_RRDTOOL"'|g' \
	-e 's|@BIN_MAIL@|'"$BIN_MAIL"'|g' \
	-e 's|@INIT_D@|'"$INIT_D"'|g' \
	-e 's|@NDOMOD_BINARY@|'"$NDOMOD_BINARY"'|g' \
	-e 's|@P1_PL@|'"$NAGIOS_P1_FILE"'|g' \
	$TMP_DIR/src/www/install/insertBaseConf.sql > \
	$TMP_DIR/work/www/install/insertBaseConf.sql
check_result $? "$(gettext "Change macros for insertBaseConf.sql")"

## Copy in final dir
log "INFO" "$( gettext "Copying www/install/insertBaseConf.sql in final directory")"
cp $TMP_DIR/work/www/install/insertBaseConf.sql \
	$TMP_DIR/final/www/install/insertBaseConf.sql >> "$LOG_FILE" 2>&1

### Step 2: Change right on Centreon WebFront

## use this step to change macros on php file...
echo_info "$(gettext "Change macros for php files")"
macros="@CENTREON_ETC@,@CENTREON_GENDIR@,@CENTPLUGINSTRAPS_BINDIR@,@CENTREON_LOG@,@CENTREON_VARLIB@"
find_macros_in_dir "$macros" "$TMP_DIR/src/" "www" "*.php" "file_php_temp"

log "INFO" "$(gettext "Apply macros")"

flg_error=0
${CAT} "$file_php_temp" | while read file ; do
	log "MACRO" "$(gettext "Change macro for") : $file"
	[ ! -d $(dirname $TMP_DIR/work/$file) ] && \
		mkdir -p  $(dirname $TMP_DIR/work/$file) >> $LOG_FILE 2>&1
	${SED} -e 's|@CENTREON_ETC@|'"$CENTREON_ETC"'|g' \
		-e 's|@CENTREON_GENDIR@|'"$CENTREON_GENDIR"'|g' \
		-e 's|@CENTPLUGINSTRAPS_BINDIR@|'"$CENTPLUGINSTRAPS_BINDIR"'|g' \
		-e 's|@CENTREON_VARLIB@|'"$CENTREON_VARLIB"'|g' \
		-e 's|@CENTREON_LOG@|'"$CENTREON_LOG"'|g' \
		$TMP_DIR/src/$file > $TMP_DIR/work/$file
		[ $? -ne 0 ] && flg_error=1
	log "MACRO" "$(gettext "Copy in final dir") : $file"
	cp -f $TMP_DIR/work/$file $TMP_DIR/final/$file >> $LOG_FILE 2>&1 
done
check_result $flg_error "$(gettext "Change macros for php files")"

### Step 3: Change right on nagios_etcdir
log "INFO" "$(gettext "Change right on") $NAGIOS_ETC" 
flg_error=0
$INSTALL_DIR/cinstall $cinstall_opts \
	-g "$WEB_GROUP" -d 775 \
	"$NAGIOS_ETC" >> "$LOG_FILE" 2>&1
[ $? -ne 0 ] && flg_error=1

find "$NAGIOS_ETC" -type f -print | \
	xargs -I '{}' ${CHMOD}  775 '{}' >> "$LOG_FILE" 2>&1
[ $? -ne 0 ] && flg_error=1
find "$NAGIOS_ETC" -type f -print | \
	xargs -I '{}' ${CHOWN} "$WEB_USER":"$WEB_GROUP" '{}' >> "$LOG_FILE" 2>&1
[ $? -ne 0 ] && flg_error=1
check_result $flg_error "$(gettext "Change right on") $NAGIOS_ETC" 

### Step 4: Copy final stuff in system directoy
echo_info "$(gettext "Copy CentWeb in system directory")"
$INSTALL_DIR/cinstall $cinstall \
	-u "$WEB_USER" -g "$WEB_GROUP" -d 755 \
	$INSTALL_DIR_CENTREON/www >> "$LOG_FILE" 2>&1

$INSTALL_DIR/cinstall $cinstall_opts \
	-u "$WEB_USER" -g "$WEB_GROUP" -d 755 -m 644 \
	-p $TMP_DIR/final/www \
	$TMP_DIR/final/www/* $INSTALL_DIR_CENTREON/www/ >> "$LOG_FILE" 2>&1
check_result $? "$(gettext "Install CentWeb (web front of centreon)")"

[ ! -d "$INSTALL_DIR_CENTREON/www/modules" ] && \
	$INSTALL_DIR/cinstall $cinstall_opts \
		-u "$WEB_USER" -g "$WEB_GROUP" -d 755 \
		$INSTALL_DIR_CENTREON/www/modules >> "$LOG_FILE" 2>&1

[ ! -d "$INSTALL_DIR_CENTREON/www/img/media" ] && \
	$INSTALL_DIR/cinstall $cinstall_opts \
		-u "$WEB_USER" -d 755 \
		$INSTALL_DIR_CENTREON/www/img/media >> "$LOG_FILE" 2>&1

$INSTALL_DIR/cinstall $cinstall_opts \
	-u "$WEB_USER" -g "$WEB_GROUP" -d 755 \
	$CENTREON_GENDIR/filesGeneration/nagiosCFG >> "$LOG_FILE" 2>&1
# By default, CentWeb use a filesGeneration directory in install dir.
# I create a symlink to continue in a same process
[ ! -h $INSTALL_DIR_CENTREON/filesGeneration -a ! -d $INSTALL_DIR_CENTREON/filesGeneration ] && \
	ln -s $CENTREON_GENDIR/filesGeneration $INSTALL_DIR_CENTREON >> $LOG_FILE 2>&1

$INSTALL_DIR/cinstall $cinstall_opts \
	-u "$WEB_USER" -g "$WEB_GROUP" -d 755 -v \
	$CENTREON_GENDIR/filesUpload/nagiosCFG >> "$LOG_FILE" 2>&1
# By default, CentWeb use a filesGeneration directory in install dir.
# I create a symlink to continue in a same process
[ ! -h $INSTALL_DIR_CENTREON/filesUpload -a ! -d $INSTALL_DIR_CENTREON/filesUpload ] && \
	ln -s $CENTREON_GENDIR/filesUpload $INSTALL_DIR_CENTREON >> $LOG_FILE 2>&1

$INSTALL_DIR/cinstall $cinstall_opts \
	-u "$WEB_USER" -g "$WEB_GROUP" -d 755 -v \
	$CENTREON_GENDIR/filesUpload/images >> "$LOG_FILE" 2>&1
# By default, CentWeb use a filesGeneration directory in install dir.
# I create a symlink to continue in a same process
[ ! -h $INSTALL_DIR_CENTREON/filesUpload -a ! -d $INSTALL_DIR_CENTREON/filesUpload ] && \
	ln -s $CENTREON_GENDIR/filesUpload $INSTALL_DIR_CENTREON >> $LOG_FILE 2>&1

log "INFO" "$(gettext "Copying GPL_LIB")"
$INSTALL_DIR/cinstall $cinstall_opts \
	-u "$WEB_USER" -g "$WEB_GROUP" -d 755 -m 644 \
	$TMP_DIR/final/GPL_LIB $INSTALL_DIR_CENTREON/GPL_LIB >> "$LOG_FILE" 2>&1
check_result $? "$(gettext "Install libraries")"

log "INFO" "$(gettext "Copying libinstall")"
$INSTALL_DIR/cinstall $cinstall_opts \
  -d 755 -m 755 \
  $TMP_DIR/final/libinstall $INSTALL_DIR_CENTREON/libinstall >> "$LOG_FILE" 2>&1
check_result $? "$(gettext "Copying libinstall")"

## Cron stuff
## need to add stuff for Unix system... (freeBSD...)
log "INFO" "$(gettext "Change macros for centreon.cron")"
${SED} -e 's|@PHP_BIN@|'"$PHP_BIN"'|g' \
	-e 's|@PERL_BIN@|'"$BIN_PERL"'|g' \
	-e 's|@INSTALL_DIR_CENTREON@|'"$INSTALL_DIR_CENTREON"'|g' \
	-e 's|@CENTREON_LOG@|'"$CENTREON_LOG"'|g' \
	-e 's|@CRONUSER@|'"$NAGIOS_USER"'|g' \
	$BASE_DIR/tmpl/install/centreon.cron > $TMP_DIR/work/centreon.cron
check_result $? "$(gettext "Change macros for centreon.cron")"
cp $TMP_DIR/work/centreon.cron $TMP_DIR/final/centreon.cron >> "$LOG_FILE" 2>&1

log "INFO" "$(gettext "Install centreon.cron")"
$INSTALL_DIR/cinstall $cinstall_opts \
	-m 644 \
	$TMP_DIR/final/centreon.cron $CRON_D/centreon >> "$LOG_FILE" 2>&1
check_result $? "$(gettext "Install Centreon cron.d file")"

## cron binary
cp -R $TMP_DIR/src/cron/ $TMP_DIR/final/
log "INFO" "$(gettext "Change macros for archiveDayLog")"
${SED}  -e 's|@CENTREON_ETC@|'"$CENTREON_ETC"'|g' \
		-e 's|@CENTREON_VARLIB@|'"$CENTREON_VARLIB"'|g' \
	$TMP_DIR/src/cron/archiveDayLog > \
	$TMP_DIR/work/cron/archiveDayLog
check_result $? "$(gettext "Change macros for archiveDayLog")"

cp -f $TMP_DIR/work/cron/archiveDayLog \
	$TMP_DIR/final/cron/archiveDayLog

log "INFO" "$(gettext "Change macros for centAcl.php")"
${SED} -e 's|@CENTREON_ETC@|'"$CENTREON_ETC"'|g' \
	$TMP_DIR/src/cron/centAcl.php > $TMP_DIR/work/cron/centAcl.php
check_result $? "$(gettext "Change macros for centAcl.php")"

cp -f $TMP_DIR/work/cron/centAcl.php \
	$TMP_DIR/final/cron/centAcl.php >> "$LOG_FILE" 2>&1

log "INFO" "$(gettext "Install cron directory")"
$INSTALL_DIR/cinstall $cinstall_opts \
	-u "$NAGIOS_USER" -g "$WEB_GROUP" -d 755 -m 644 \
	$TMP_DIR/final/cron $INSTALL_DIR_CENTREON/cron >> "$LOG_FILE" 2>&1
check_result $? "$(gettext "Install cron directory")"

## Prepare to install all pear modules needed.
# use check_pear.php script
echo -e "\n$line"
echo -e "$(gettext "Pear Modules")"
echo -e "$line"
pear_module="0"
while [ "$pear_module" -eq 0 ] ; do 
	check_pear_module "$INSTALL_VARS_DIR/$PEAR_MODULES_LIST"
	if [ "$?" -ne 0 ] ; then
    if [ "${PEAR_AUTOINST:-0}" -eq 0 ]; then
  		yes_no_default "$(gettext "Do you want me to install/upgrade your PEAR modules")" "$yes"
      [ "$?" -eq 0 ] && PEAR_AUTOINST=1
    fi
  	if [ "$PEAR_AUTOINST" -eq 1 ] ; then
  		upgrade_pear_module "$INSTALL_VARS_DIR/$PEAR_MODULES_LIST"
  		install_pear_module "$INSTALL_VARS_DIR/$PEAR_MODULES_LIST"
  	else
  			pear_module="1"
  	fi
 	else 
  	echo_success "$(gettext "All PEAR modules")" "$ok"
 		pear_module="1"
 	fi
done

## Create configfile for web install
createConfFile

## Write install config file
createCentreonInstallConf

## wait sql inject script....

