# -*-Shell-script-*-
# install centreon centstorage  

echo "------------------------------------------------------------------------"
echo -e "\t`gettext \"Start CentStorage Installation\"`"
echo "------------------------------------------------------------------------"

###### Require
#################################
## Where is install_dir_centreon ?
locate_centreon_installdir

## locate or create Centreon log dir
locate_centreon_logdir
locate_centreon_etcdir
locate_centreon_rundir
locate_centreon_generationdir

## Config pre-require
locate_rrd_perldir
locate_nagios_vardir
locate_init_d
locate_cron_d

locate_centstorage_bindir
locate_centstorage_rrddir
## Config Nagios
check_group_nagios
check_user_nagios

## Populate temporaty source directory
copyInTempFile >> $LOG_FILE 2>&1

## Create temporary folder
log "INFO" "`gettext \"Create working directory\"`"
mkdir -p $TMPDIR/final/www/install
mkdir -p $TMPDIR/work/www/install
mkdir -p $TMPDIR/final/bin
mkdir -p $TMPDIR/work/bin
[ ! -d $INSTALL_DIR_CENTREON/examples ] && mkdir -p $INSTALL_DIR_CENTREON/examples
cp -f $BASE_DIR/tmpl/install/centstorage.init.d $TMPDIR/src
cp -rf $TMPDIR/src/lib $TMPDIR/final

###### DB script
#################################
## Change Macro in working dir
sed -e 's|@NAGIOS_VAR@|'"$NAGIOS_VAR"'|g' \
 -e 's|@CENTREON_VAR@|'"$CENTREON_GENDIR"'|g' \
 $TMPDIR/src/www/install/createTablesODS.sql > $TMPDIR/work/www/install/createTablesODS.sql

## Copy in final dir
log "INFO" "`gettext \"Copying www/install/createTablesODS.sql in final directory\"`"
cp $TMPDIR/work/www/install/createTablesODS.sql $TMPDIR/final/www/install/createTablesODS.sql >> $LOG_FILE 2>&1

###### RRD directory
#################################
## Create CentStorage Status folder
if [ ! -d "$CENTSTORAGE_RRD/status" ] ; then
	log "INFO" "`gettext \"Create CentStorage status directory\"`"
	$INSTALL_DIR/cinstall -u $NAGIOS_USER -g $NAGIOS_GROUP -d 775 \
		$CENTSTORAGE_RRD/status >> $LOG_FILE 2>&1
	echo_success "`gettext \"Creating Centreon Directory\"` '$CENTSTORAGE_RRD/status'" "$ok"
else
	echo_passed "`gettext \"CentStorage status Directory already exists\"`" "$passed"
fi
## Create CentStorage metrics folder
if [ ! -d "$CENTSTORAGE_RRD/metrics" ] ; then
	log "INFO" "`gettext \"Create CentStorage metrics directory\"`"
	$INSTALL_DIR/cinstall -u $NAGIOS_USER -g $NAGIOS_GROUP -d 775 \
		$CENTSTORAGE_RRD/metrics >> $LOG_FILE 2>&1
	echo_success "`gettext \"Creating Centreon Directory\"` '$CENTSTORAGE_RRD/metrics'" "$ok"
else
	echo_passed "`gettext \"CentStorage metrics Directory already exists\"`" "$passed"
fi
    
    
###### CentStorage binary
#################################
## Change macros in CentStorage binary
sed -e 's|@CENTREON_PATH@|'"$INSTALL_DIR_CENTREON"'|g' \
	 -e 's|@CENTREON_LOG@|'"$CENTREON_LOG"'|g' \
	 -e 's|@CENTREON_RUNDIR@|'"$CENTREON_RUNDIR"'|g' \
	 -e 's|@RRD_PERL@|'"$RRD_PERL"'|g' \
	 $TMPDIR/src/bin/centstorage > $TMPDIR/work/bin/centstorage
	 
echo_success "`gettext \"Replace Centstorage Macro\"`" "$ok"
log "INFO" "`gettext \"Copying CentStorage binary in final directory\"`"
cp $TMPDIR/work/bin/centstorage $TMPDIR/final/bin/centstorage >> $LOG_FILE 2>&1
$INSTALL_DIR/cinstall -u $NAGIOS_USER -g $NAGIOS_GROUP -m 755 \
	$TMPDIR/final/bin/centstorage $CENTSTORAGE_BINDIR/centstorage >> $LOG_FILE 2>&1

echo_success "`gettext \"Set CentStorage properties\"`" "$ok"

## Copy lib for CentStorage
$INSTALL_DIR/cinstall -u root -g $NAGIOS_GROUP -m 766 \
	$TMPDIR/final/lib $INSTALL_DIR_CENTREON/lib >> $LOG_FILE 2>&1

## Change right on CENTREON_RUNDIR
log "INFO" "`gettext \"Change right\"` : $CENTREON_RUNDIR"
$INSTALL_DIR/cinstall -u root -g $NAGIOS_USER -d 775 \
	$CENTREON_RUNDIR >> $LOG_FILE 2>&1

###### CentStorate init
#################################
## Change macros in CentStorage init script
sed -e 's|@CENTREON_DIR@|'"$INSTALL_DIR_CENTREON"'|g' \
	-e 's|@CENTREON_ETC@|'"$CENTREON_ETC"'|g' \
	-e 's|@CENTREON_LOG@|'"$CENTREON_LOG"'|g' \
	-e 's|@CENTSTORAGE_BINDIR@|'"$CENTSTORAGE_BINDIR"'|g' \
	-e 's|@NAGIOS_USER@|'"$NAGIOS_USER"'|g' \
	$TMPDIR/src/centstorage.init.d > $TMPDIR/work/centstorage.init.d

echo_success "`gettext \"Replace Centstorage init script Macro\"`" "$ok"
cp $TMPDIR/work/centstorage.init.d $TMPDIR/final/centstorage.init.d
cp $TMPDIR/final/centstorage.init.d $INSTALL_DIR_CENTREON/examples/centstorage.init.d

yes_no_default "`gettext \"Do you want I install CentStorage init script ?\"`"
if [ $? -eq 0 ] ; then 
	log "INFO" "`gettext \"CentStorage init script installed\"`"
	$INSTALL_DIR/cinstall -u root -g root -m 755 \
		$TMPDIR/final/centstorage.init.d \
		$INIT_D/centstorage >> $LOG_FILE 2>&1
	yes_no_default "`gettext \"Do you want I install CentStorage run level ?\"`"
		if [ $? -eq 0 ] ; then
			install_init_service "centstorage"
		fi
else
	echo_passed "`gettext \"CentStorage init script not installed, please use \"`:\n $INSTALL_DIR_CENTREON/examples/centstorage.init.d" "$passed"
	log "INFO" "`gettext \"CentStorage init script not installed, please use \"`: $INSTALL_DIR_CENTREON/examples/centstorage.init.d"
fi

###### Cron
#################################
### Macro
## logAnalyser
sed -e 's|@CENTREON_ETC@|'"$CENTREON_ETC"'|g' \
	$TMPDIR/src/bin/logAnalyser > $TMPDIR/work/bin/logAnalyser

cp $TMPDIR/work/bin/logAnalyser $TMPDIR/final/bin/logAnalyser >> $LOG_FILE 2>&1
$INSTALL_DIR/cinstall -u $NAGIOS_USER -g $NAGIOS_GROUP -m 755 \
	$TMPDIR/final/bin/logAnalyser \
	$CENTSTORAGE_BINDIR/logAnalyser >> $LOG_FILE 2>&1

echo_success "`gettext \"Set logAnalyser properties\"`" "$ok"

## nagiosPerfTrace
sed -e 's|@CENTREON_ETC@|'"$CENTREON_ETC"'|g' \
	$TMPDIR/src/bin/nagiosPerfTrace > $TMPDIR/work/bin/nagiosPerfTrace

cp $TMPDIR/work/bin/nagiosPerfTrace $TMPDIR/final/bin/nagiosPerfTrace >> $LOG_FILE 2>&1
$INSTALL_DIR/cinstall -u $NAGIOS_USER -g $NAGIOS_GROUP -m 755 \
	$TMPDIR/final/bin/nagiosPerfTrace \
	$CENTSTORAGE_BINDIR/nagiosPerfTrace >> $LOG_FILE 2>&1

echo_success "`gettext \"Set nagiosPerfTrace properties\"`" "$ok"


## cron file 
sed -e 's|@PHP_BIN@|'"$PHP_BIN"'|g' \
	-e 's|@CENTSTORAGE_BINDIR@|'"$CENTSTORAGE_BINDIR"'|g' \
	-e 's|@CENTREON_LOG@|'"$CENTREON_LOG"'|g' \
	$BASE_DIR/tmpl/install/centstorage.cron > $TMPDIR/work/centstorage.cron
cp $TMPDIR/work/centstorage.cron $TMPDIR/final/centstorage.cron >> $LOG_FILE 2>&1
$INSTALL_DIR/cinstall -u root -g root -m 644 \
	$TMPDIR/final/centstorage.cron \
	$CRON_D/centstorage >> $LOG_FILE 2>&1

echo_success "`gettext \"Install CentStorage cron\"`" "$ok"


###### Post Install
#################################
createCentStorageInstallConf


## wait and see...
## sql console inject ?
