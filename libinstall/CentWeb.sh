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
locate_centpluginstraps_bindir

## Config pre-require
# define all necessary variables.
locate_rrd_perldir
locate_rrdtool
locate_mail
#locate_nagios_p1_file $NAGIOS_ETC
locate_cron_d
locate_logrotate_d
locate_init_d
locate_php_bin
locate_perl

## Config apache
check_httpd_directory
check_user_apache
check_group_apache
## Ask for centreon user
check_centreon_group
check_centreon_user
## Ask for monitoring engine user
check_engine_user
## Ask for monitoring broker user
check_broker_user
## Ask for plugins directory
locate_monitoringengine_log
locate_plugindir

## Add default value for centreon engine connector
if [ -z "$CENTREON_ENGINE_CONNECTORS" ]; then
	if [ "$(uname -i)" = "x86_64" ]; then
		CENTREON_ENGINE_CONNECTORS="/usr/lib64/centreon-connector"
	else
		CENTREON_ENGINE_CONNECTORS="/usr/lib/centreon-connector"
	fi
fi

add_group "$WEB_USER" "$CENTREON_GROUP"
add_group "$MONITORINGENGINE_USER" "$CENTREON_GROUP"
get_primary_group "$MONITORINGENGINE_USER" "MONITORINGENGINE_GROUP"
add_group "$WEB_USER" "$MONITORINGENGINE_GROUP"
add_group "$CENTREON_USER" "$MONITORINGENGINE_GROUP"

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
	-u "$CENTREON_USER" -g "$CENTREON_GROUP" -d 775 \
	"$CENTREON_LOG" >> "$LOG_FILE" 2>&1
check_result $? "$(gettext "Change right on") $CENTREON_LOG"

# change right on centreon etc
log "INFO" "$(gettext "Change right on") $CENTREON_ETC"
$INSTALL_DIR/cinstall $cinstall_opts \
	-u "$CENTREON_USER" -g "$CENTREON_GROUP" -d 775 \
	"$CENTREON_ETC" >> "$LOG_FILE" 2>&1
check_result $? "$(gettext "Change right on") $CENTREON_ETC"

# change right on nagios images/logos
#if [ -z "${NAGIOS_IMG}" ]; then
#	log "INFO" "$(gettext "Change right on") $NAGIOS_IMG"
#	$INSTALL_DIR/cinstall $cinstall_opts \
#		-u "$WEB_USER" -d 755 \
#		"$NAGIOS_IMG" >> "$LOG_FILE" 2>&1
#	check_result $? "$(gettext "Change right on") $NAGIOS_IMG"
#fi

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
#$INSTALL_DIR/cinstall $cinstall_opts \
#	-g $CENTREON_GROUP -d 755 -m 644 \
#	$TMP_DIR/src/doc $INSTALL_DIR_CENTREON/doc >> $LOG_FILE 2>&1
#check_result $? "$(gettext "Install nagios documentation")"

## Ticket #372 : add functions/cinstall fonctionnality
cp -Rf $TMP_DIR/src/libinstall/{functions,cinstall,gettext} \
  $TMP_DIR/final/libinstall/ >> "$LOG_FILE" 2>&1

## Prepare insertBaseConf.sql
#echo -e "$(gettext "In process")"
### Step 1:
## Change Macro on sql file
log "INFO" "$(gettext "Change macros for insertBaseConf.sql")"
${SED} -e 's|@RRDTOOL_PERL_LIB@|'"$RRD_PERL"'|g' \
	-e 's|@INSTALL_DIR_CENTREON@|'"$INSTALL_DIR_CENTREON"'|g' \
	-e 's|@BIN_RRDTOOL@|'"$BIN_RRDTOOL"'|g' \
	-e 's|@BIN_MAIL@|'"$BIN_MAIL"'|g' \
	-e 's|@CENTREON_ETC@|'"$CENTREON_ETC"'|g' \
	-e 's|@CENTREON_LOG@|'"$CENTREON_LOG"'|g' \
	-e 's|@CENTREON_VARLIB@|'"$CENTREON_VARLIB"'|g' \
	$TMP_DIR/src/www/install/insertBaseConf.sql > \
	$TMP_DIR/work/www/install/insertBaseConf.sql
check_result $? "$(gettext "Change macros for insertBaseConf.sql")"

## Copy in final dir
log "INFO" "$( gettext "Copying www/install/insertBaseConf.sql in final directory")"
cp $TMP_DIR/work/www/install/insertBaseConf.sql \
	$TMP_DIR/final/www/install/insertBaseConf.sql >> "$LOG_FILE" 2>&1
	
### Chagne Macro for sql update file
macros="@CENTREON_ETC@,@CENTREON_GENDIR@,@CENTPLUGINSTRAPS_BINDIR@,@CENTREON_LOG@,@CENTREON_VARLIB@,@CENTREON_ENGINE_CONNECTORS@"
find_macros_in_dir "$macros" "$TMP_DIR/src/" "www" "Update*.sql" "file_sql_temp"

log "INFO" "$(gettext "Apply macros")"

flg_error=0
${CAT} "$file_sql_temp" | while read file ; do
	log "MACRO" "$(gettext "Change macro for") : $file"
	[ ! -d $(dirname $TMP_DIR/work/$file) ] && \
		mkdir -p  $(dirname $TMP_DIR/work/$file) >> $LOG_FILE 2>&1
	${SED} -e 's|@CENTREON_ETC@|'"$CENTREON_ETC"'|g' \
		-e 's|@CENTREON_GENDIR@|'"$CENTREON_GENDIR"'|g' \
		-e 's|@CENTPLUGINSTRAPS_BINDIR@|'"$CENTPLUGINSTRAPS_BINDIR"'|g' \
		-e 's|@CENTREON_VARLIB@|'"$CENTREON_VARLIB"'|g' \
		-e 's|@CENTREON_LOG@|'"$CENTREON_LOG"'|g' \
		-e 's|@CENTREON_ENGINE_CONNECTORS@|'"$CENTREON_ENGINE_CONNECTORS"'|g' \
		$TMP_DIR/src/$file > $TMP_DIR/work/$file
		[ $? -ne 0 ] && flg_error=1
	log "MACRO" "$(gettext "Copy in final dir") : $file"
	cp -f $TMP_DIR/work/$file $TMP_DIR/final/$file >> $LOG_FILE 2>&1 
done
check_result $flg_error "$(gettext "Change macros for sql update files")"

### Step 2: Change right on Centreon WebFront

## use this step to change macros on php file...
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

### Step 3: Change right on monitoringengine_etcdir
log "INFO" "$(gettext "Change right on") $MONITORINGENGINE_ETC" 
flg_error=0
$INSTALL_DIR/cinstall $cinstall_opts \
	-g "$MONITORINGENGINE_GROUP" -d 775 \
	"$MONITORINGENGINE_ETC" >> "$LOG_FILE" 2>&1
[ $? -ne 0 ] && flg_error=1

find "$MONITORINGENGINE_ETC" -type f -print | \
	xargs -I '{}' ${CHMOD}  775 '{}' >> "$LOG_FILE" 2>&1
[ $? -ne 0 ] && flg_error=1
find "$MONITORINGENGINE_ETC" -type f -print | \
	xargs -I '{}' ${CHOWN} "$MONITORINGENGINE_USER":"$MONITORINGENGINE_GROUP" '{}' >> "$LOG_FILE" 2>&1
[ $? -ne 0 ] && flg_error=1
check_result $flg_error "$(gettext "Change right on") $MONITORINGENGINE_ETC" 

### Change right to broker_etcdir
log "INFO" "$(gettext "Change right on ") $BROKER_ETC"
flg_error=0
if [ -z "$BROKER_USER" ]; then
	BROKER_USER=$MONITORINGENGINE_USER
	get_primary_group "$BROKER_USER" "BROKER_GROUP"
else
	get_primary_group "$BROKER_USER" "BROKER_GROUP"
	add_group "$WEB_USER" "$BROKER_GROUP"
	add_group "$BROKER_USER" "$CENTREON_GROUP"
fi
if [ "$MONITORINGENGINE_ETC" != "$BROKER_ETC" ]; then
	$INSTALL_DIR/cinstall $cinstall_opts \
		-g "$BROKER_GROUP" -d 775 \
		"$BROKER_ETC" >> "$LOG_FILE" 2>&1
	[ $? -ne 0 ] && flg_error=1
	find "$BROKER_ETC" -type f -print | \
		xargs -I '{}' ${CHMOD}  775 '{}' >> "$LOG_FILE" 2>&1
	[ $? -ne 0 ] && flg_error=1
	find "$BROKER_ETC" -type f -print | \
		xargs -I '{}' ${CHOWN} "$BROKER_USER":"$BROKER_GROUP" '{}' >> "$LOG_FILE" 2>&1
	[ $? -ne 0 ] && flg_error=1
	check_result $flg_error "$(gettext "Change right on") $BROKER_ETC" 
fi

if [ "$upgrade" = "1" ]; then
	echo_info "$(gettext "Disconnect users from WebUI")"
	php $INSTALL_DIR/clean_session.php "$CENTREON_ETC" >> "$LOG_FILE" 2>&1
	check_result $? "$(gettext "All users are disconnected")"
fi

### Step 4: Copy final stuff in system directoy
echo_info "$(gettext "Copy CentWeb in system directory")"
$INSTALL_DIR/cinstall $cinstall \
	-u "$CENTREON_USER" -g "$CENTREON_GROUP" -d 775 \
	$INSTALL_DIR_CENTREON/www >> "$LOG_FILE" 2>&1

$INSTALL_DIR/cinstall $cinstall_opts \
	-u "$CENTREON_USER" -g "$CENTREON_GROUP" -d 755 -m 644 \
	-p $TMP_DIR/final/www \
	$TMP_DIR/final/www/* $INSTALL_DIR_CENTREON/www/ >> "$LOG_FILE" 2>&1
check_result $? "$(gettext "Install CentWeb (web front of centreon)")"

echo_info "$(gettext "Change right for install directory")"
$CHOWN -R $WEB_USER:$WEB_GROUP $INSTALL_DIR_CENTREON/www/install/
check_result $? "$(gettext "Change right for install directory")"

[ ! -d "$INSTALL_DIR_CENTREON/www/modules" ] && \
	$INSTALL_DIR/cinstall $cinstall_opts \
		-u "$CENTREON_USER" -g "$CENTREON_GROUP" -d 755 \
		$INSTALL_DIR_CENTREON/www/modules >> "$LOG_FILE" 2>&1

[ ! -d "$INSTALL_DIR_CENTREON/www/img/media" ] && \
	$INSTALL_DIR/cinstall $cinstall_opts \
		-u "$CENTREON_USER" -g "$CENTREON_GROUP" -d 775 \
		$INSTALL_DIR_CENTREON/www/img/media >> "$LOG_FILE" 2>&1

$INSTALL_DIR/cinstall $cinstall_opts \
	-u "$CENTREON_USER" -g "$CENTREON_GROUP" -d 775 \
	$CENTREON_GENDIR/filesGeneration/nagiosCFG >> "$LOG_FILE" 2>&1
$INSTALL_DIR/cinstall $cinstall_opts \
	-u "$CENTREON_USER" -g "$CENTREON_GROUP" -d 775 \
	$CENTREON_GENDIR/filesGeneration/broker >> "$LOG_FILE" 2>&1	
# By default, CentWeb use a filesGeneration directory in install dir.
# I create a symlink to continue in a same process
[ ! -h $INSTALL_DIR_CENTREON/filesGeneration -a ! -d $INSTALL_DIR_CENTREON/filesGeneration ] && \
	ln -s $CENTREON_GENDIR/filesGeneration $INSTALL_DIR_CENTREON >> $LOG_FILE 2>&1

$INSTALL_DIR/cinstall $cinstall_opts \
	-u "$CENTREON_USER" -g "$CENTREON_GROUP" -d 775 -v \
	$CENTREON_GENDIR/filesUpload/nagiosCFG >> "$LOG_FILE" 2>&1
# By default, CentWeb use a filesGeneration directory in install dir.
# I create a symlink to continue in a same process
[ ! -h $INSTALL_DIR_CENTREON/filesUpload -a ! -d $INSTALL_DIR_CENTREON/filesUpload ] && \
	ln -s $CENTREON_GENDIR/filesUpload $INSTALL_DIR_CENTREON >> $LOG_FILE 2>&1

$INSTALL_DIR/cinstall $cinstall_opts \
	-u "$CENTREON_USER" -g "$CENTREON_GROUP" -d 775 -v \
	$CENTREON_GENDIR/filesUpload/images >> "$LOG_FILE" 2>&1
# By default, CentWeb use a filesGeneration directory in install dir.
# I create a symlink to continue in a same process
[ ! -h $INSTALL_DIR_CENTREON/filesUpload -a ! -d $INSTALL_DIR_CENTREON/filesUpload ] && \
	ln -s $CENTREON_GENDIR/filesUpload $INSTALL_DIR_CENTREON >> $LOG_FILE 2>&1

log "INFO" "$(gettext "Copying GPL_LIB")"
$INSTALL_DIR/cinstall $cinstall_opts \
	-u "$CENTREON_USER" -g "$CENTREON_GROUP" -d 755 -m 644 \
	$TMP_DIR/final/GPL_LIB $INSTALL_DIR_CENTREON/GPL_LIB >> "$LOG_FILE" 2>&1
check_result $? "$(gettext "Install libraries")"

log "INFO" "$(gettext "Add right for Smarty cache and compile")"
$CHMOD -R g+w $INSTALL_DIR_CENTREON/GPL_LIB/SmartyCache
check_result $? "$(gettext "Write right to Smarty Cache")"

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
	-e 's|@CENTREON_USER@|'"$CENTREON_USER"'|g' \
	-e 's|@WEB_USER@|'"$WEB_USER"'|g' \
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

log "INFO" "$(gettext "Change macros for centAcl.php")"
${SED} -e 's|@CENTREON_ETC@|'"$CENTREON_ETC"'|g' \
	$TMP_DIR/src/cron/centAcl.php > $TMP_DIR/work/cron/centAcl.php
check_result $? "$(gettext "Change macros for centAcl.php")"

cp -f $TMP_DIR/work/cron/centAcl.php \
	$TMP_DIR/final/cron/centAcl.php >> "$LOG_FILE" 2>&1

log "INFO" "$(gettext "Change macros for downtimeManager.php")"
${SED} -e 's|@CENTREON_ETC@|'"$CENTREON_ETC"'|g' \
	-e 's|@CENTREON_VARLIB@|'"$CENTREON_VARLIB"'|g' \
	$TMP_DIR/src/cron/downtimeManager.php > $TMP_DIR/work/cron/downtimeManager.php
check_result $? "$(gettext "Change macros for downtimeManager.php")"

cp -f $TMP_DIR/work/cron/downtimeManager.php \
	$TMP_DIR/final/cron/downtimeManager.php >> "$LOG_FILE" 2>&1

log "INFO" "$(gettext "Change macros for eventReportBuilder.pl")"
${SED} -e 's|@CENTREON_ETC@|'"$CENTREON_ETC"'|g' \
	-e 's|@INSTALL_DIR_CENTREON@|'"$INSTALL_DIR_CENTREON"'|g' \
	-e 's|@CENTREON_VARLIB@|'"$CENTREON_VARLIB"'|g' \
	$TMP_DIR/src/cron/eventReportBuilder.pl > $TMP_DIR/work/cron/eventReportBuilder.pl
check_result $? "$(gettext "Change macros for eventReportBuilder.pl")"

cp -f $TMP_DIR/work/cron/eventReportBuilder.pl \
	$TMP_DIR/final/cron/eventReportBuilder.pl >> "$LOG_FILE" 2>&1

log "INFO" "$(gettext "Change macros for dashboardBuilder.pl")"
${SED} -e 's|@CENTREON_ETC@|'"$CENTREON_ETC"'|g' \
	-e 's|@INSTALL_DIR_CENTREON@|'"$INSTALL_DIR_CENTREON"'|g' \
	-e 's|@CENTREON_VARLIB@|'"$CENTREON_VARLIB"'|g' \
	$TMP_DIR/src/cron/dashboardBuilder.pl > $TMP_DIR/work/cron/dashboardBuilder.pl
check_result $? "$(gettext "Change macros for dashboardBuilder.pl")"

cp -f $TMP_DIR/work/cron/dashboardBuilder.pl \
	$TMP_DIR/final/cron/dashboardBuilder.pl >> "$LOG_FILE" 2>&1

log "INFO" "$(gettext "Install cron directory")"
$INSTALL_DIR/cinstall $cinstall_opts \
	-u "$CENTREON_USER" -g "$CENTREON_GROUP" -d 755 -m 644 \
	$TMP_DIR/final/cron $INSTALL_DIR_CENTREON/cron >> "$LOG_FILE" 2>&1
check_result $? "$(gettext "Install cron directory")"

log "INFO" "$(gettext "Change right for eventReportBuilder.pl")"
${CHMOD} 755 $INSTALL_DIR_CENTREON/cron/eventReportBuilder.pl >> "$LOG_FILE" 2>&1
check_result $? "$(gettext "Change right for eventReportBuilder.pl")"

log "INFO" "$(gettext "Change right for dashboardBuilder.pl")"
${CHMOD} 755 $INSTALL_DIR_CENTREON/cron/dashboardBuilder.pl >> "$LOG_FILE" 2>&1
check_result $? "$(gettext "Change right for dashboardBuilder.pl")"

## Logrotate
log "INFO" "$(gettext "Change macros for centreon.logrotate")"
${SED} -e 's|@CENTREON_LOG@|'"$CENTREON_LOG"'|g' \
	$TMP_DIR/src/logrotate/centreon > $TMP_DIR/work/centreon.logrotate
check_result $? "$(gettext "Change macros for centreon.logrotate")"
cp $TMP_DIR/work/centreon.logrotate $TMP_DIR/final/centreon.logrotate >> "$LOG_FILE" 2>&1

log "INFO" "$(gettext "Install centreon.logrotate")"
$INSTALL_DIR/cinstall $cinstall_opts \
	-m 644 \
	$TMP_DIR/final/centreon.logrotate $LOGROTATE_D/centreon >> "$LOG_FILE" 2>&1
check_result $? "$(gettext "Install Centreon logrotate.d file")"

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
  	if [ "${PEAR_AUTOINST:-0}" -eq 1 ] ; then
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

