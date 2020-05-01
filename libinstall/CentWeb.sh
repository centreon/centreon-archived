#!/usr/bin/env bash
#----
## @Synopsis    Install script for Centreon Web Front (CentWeb)
## @Copyright   Copyright 2008, Guillaume Watteeux
## @Copyright	Copyright 2008-2020, Centreon
## @license GPL : http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
## Install script for Centreon Web Front (CentWeb)
#----
#----
## Centreon is developed with GPL Licence 2.0
##
## GPL License: http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
##
## Developed by : Julien Mathis - Romain Le Merlus
## Contributors : Guillaume Watteeux - Maximilien Bersoult
##
## This program is free software; you can redistribute it and/or
## modify it under the terms of the GNU General Public License
## as published by the Free Software Foundation; either version 2
## of the License, or (at your option) any later version.
##
## This program is distributed in the hope that it will be useful,
## but WITHOUT ANY WARRANTY; without even the implied warranty of
## MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
## GNU General Public License for more details.
##
##    For information : infos@centreon.com

# debug ?
#set -x

echo -e "\n$line"
echo -e "\t$(gettext "Gorgone module Installation")"
echo -e "$line"

# locate gorgone
locate_gorgone_varlib
locate_gorgone_config
check_gorgone_user
check_gorgone_group

echo -e "\n$line"
echo -e "\t$(gettext "Start CentWeb Installation")"
echo -e "$line"

###### check space of tmp dir
check_tmp_disk_space
if [ "$?" -eq 1 ] ; then
  if [ "$silent_install" -eq 1 ] ; then
    purge_centreon_tmp_dir "silent"
  else
    purge_centreon_tmp_dir
  fi
fi

###### Mandatory step
## Create install_dir_centreon
locate_centreon_installdir
# Create an examples directory to save all important templates and config
[ ! -d $INSTALL_DIR_CENTREON/examples ] && \
    mkdir -p $INSTALL_DIR_CENTREON/examples

## locate or create Centreon log dir
locate_centreon_logdir
locate_centreon_etcdir
locate_centreon_bindir
locate_centreon_generationdir
locate_centreon_varlib

## Config pre-require
# define all necessary variables.
locate_rrdtool
locate_mail
locate_cron_d
locate_logrotate_d
locate_php_bin
locate_pear
locate_perl

## Check PHP version
check_php_version
if [ "$?" -eq 1 ] ; then
    echo_info "\n\t$(gettext "Your php version does not meet the requirements")"

    echo -e "\t$(gettext "Please read the documentation available here") : documentation.centreon.com"
    echo -e "\n\t$(gettext "Installation aborted")"

    purge_centreon_tmp_dir
    exit 1
fi

## Check composer dependencies (if vendor directory exists)
check_composer_dependencies
if [ "$?" -eq 1 ] ; then
    echo_info "\n\t$(gettext "You must first install the composer's dependencies")"

    echo -e "\n\t$(gettext "composer install --no-dev --optimize-autoloader")"
    echo -e "\t$(gettext "Please read the documentation available here") : documentation.centreon.com"

    echo -e "\n\t$(gettext "Installation aborted")"
    purge_centreon_tmp_dir
    exit 1
fi

## Check frontend application (if www/static directory exists)
check_frontend_application
if [ "$?" -eq 1 ] ; then
    echo_info "\n\t$(gettext "You must first build the frontend application")"

    echo -e "\n\t$(gettext "Using npm install and then npm build")"
    echo -e "\t$(gettext "Please read the documentation available here") : documentation.centreon.com"

    echo -e "\n\t$(gettext "Installation aborted")"
    purge_centreon_tmp_dir
    exit 1
fi

## Config apache
check_httpd_directory
check_user_apache
check_group_apache

## Config PHP FPM
check_php_fpm_directory

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
locate_centreon_plugins

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
add_group "$CENTREON_USER" "$WEB_GROUP"

## Config Sudo
# I think this process move on CentCore install...
configureSUDO "$INSTALL_DIR_CENTREON/examples"

## Config Apache
configureApache "$INSTALL_DIR_CENTREON/examples"

## Ask for fpm-php service
configure_php_fpm "$INSTALL_DIR_CENTREON/examples"

## Create temps folder and copy all src into
copyInTempFile 2>>$LOG_FILE

## InstallCentreon

# change rights centreon_log directory
log "INFO" "$(gettext "Modify rights on") $CENTREON_LOG"
$INSTALL_DIR/cinstall $cinstall_opts \
    -u "$CENTREON_USER" -g "$CENTREON_GROUP" -d 775 \
    "$CENTREON_LOG" >> "$LOG_FILE" 2>&1
check_result $? "$(gettext "Modify rights on") $CENTREON_LOG"

# change rights on successful installations files
log "INFO" "$(gettext "Modify rights on") $CENTREON_VARLIB/installs"
$INSTALL_DIR/cinstall $cinstall_opts \
    -u "$CENTREON_USER" -g "$CENTREON_GROUP" -d 775 \
    "$CENTREON_VARLIB/installs" >> "$LOG_FILE" 2>&1
chmod -R g+rwxs $CENTREON_VARLIB/installs
check_result $? "$(gettext "Modify rights on") $CENTREON_VARLIB/installs"

# change rights on centreon etc
log "INFO" "$(gettext "Modify rights on") $CENTREON_ETC"
$INSTALL_DIR/cinstall $cinstall_opts \
    -u "$CENTREON_USER" -g "$CENTREON_GROUP" -d 775 \
    "$CENTREON_ETC" >> "$LOG_FILE" 2>&1
check_result $? "$(gettext "Modify rights on") $CENTREON_ETC"

# change rights on centreon cache folder
log "INFO" "$(gettext "Modify rights on") $CENTREON_CACHEDIR"
$INSTALL_DIR/cinstall $cinstall_opts \
    -u "$CENTREON_USER" -g "$CENTREON_GROUP" -d 775 \
    "$CENTREON_CACHEDIR" >> "$LOG_FILE" 2>&1
check_result $? "$(gettext "Modify rights on") $CENTREON_CACHEDIR"

## Copy Web Front and Backend Sources in final folder
log "INFO" "$(gettext "Copy CentWeb and GPL_LIB in temporary final directory")"
cp -Rf $TMP_DIR/src/api $TMP_DIR/final
cp -Rf $TMP_DIR/src/www $TMP_DIR/final
cp -Rf $TMP_DIR/src/GPL_LIB $TMP_DIR/final
cp -Rf $TMP_DIR/src/config $TMP_DIR/final
mv $TMP_DIR/src/config/centreon.config.php.template $TMP_DIR/src/config/centreon.config.php
cp -f $TMP_DIR/src/container.php $TMP_DIR/final
cp -f $TMP_DIR/src/bootstrap.php $TMP_DIR/final
cp -f $TMP_DIR/src/composer.json $TMP_DIR/final
cp -f $TMP_DIR/src/package.json $TMP_DIR/final
cp -f $TMP_DIR/src/package-lock.json $TMP_DIR/final
cp -f $TMP_DIR/src/.env $TMP_DIR/final
cp -f $TMP_DIR/src/.env.local.php $TMP_DIR/final
cp -Rf $TMP_DIR/src/src $TMP_DIR/final

## Prepare and copy composer module
OLDPATH=$(pwd)
cd $TMP_DIR/src/
log "INFO" "$(gettext "Copying composer dependencies...")"
cp -Rf vendor $TMP_DIR/final/
cd "${OLDPATH}"

## Build frontend app
OLDPATH=$(pwd)
cd $TMP_DIR/src/
log "INFO" "$(gettext "Copying frontend application...")"
cp -Rf www/index.html www/static $TMP_DIR/final/www/
cd "${OLDPATH}"

## Create temporary directory
mkdir -p $TMP_DIR/work/bin >> $LOG_FILE 2>&1
mkdir -p $TMP_DIR/work/www/install >> "$LOG_FILE" 2>&1
mkdir -p $TMP_DIR/work/cron/reporting >> "$LOG_FILE" 2>&1
mkdir -p $TMP_DIR/work/data >> "$LOG_FILE" 2>&1
mkdir -p $TMP_DIR/final/bin >> $LOG_FILE 2>&1
mkdir -p $TMP_DIR/final/cron/reporting >> "$LOG_FILE" 2>&1
mkdir -p $TMP_DIR/final/libinstall >> "$LOG_FILE" 2>&1
mkdir -p $TMP_DIR/final/data >> "$LOG_FILE" 2>&1

## Ticket #372 : add functions/cinstall fonctionnality
cp -Rf $TMP_DIR/src/libinstall/{functions,cinstall,gettext} \
  $TMP_DIR/final/libinstall/ >> "$LOG_FILE" 2>&1

## Prepare insertBaseConf.sql
## Change Macro on sql file
log "INFO" "$(gettext "Change macros for insertBaseConf.sql")"
${SED} -e 's|@INSTALL_DIR_CENTREON@|'"$INSTALL_DIR_CENTREON"'|g' \
    -e 's|@BIN_MAIL@|'"$BIN_MAIL"'|g' \
    -e 's|@CENTREON_ETC@|'"$CENTREON_ETC"'|g' \
    -e 's|@CENTREON_LOG@|'"$CENTREON_LOG"'|g' \
    -e 's|@CENTREON_VARLIB@|'"$CENTREON_VARLIB"'|g' \
    -e 's|@BIN_RRDTOOL@|'"$BIN_RRDTOOL"'|g' \
    $TMP_DIR/src/www/install/insertBaseConf.sql > \
    $TMP_DIR/work/www/install/insertBaseConf.sql
check_result $? "$(gettext "Change macros for insertBaseConf.sql")"

## Copy in final dir
log "INFO" "$( gettext "Copying www/install/insertBaseConf.sql in final directory")"
cp $TMP_DIR/work/www/install/insertBaseConf.sql \
    $TMP_DIR/final/www/install/insertBaseConf.sql >> "$LOG_FILE" 2>&1

### Change Macro for sql update file
macros="@CENTREON_ETC@,@CENTREON_CACHEDIR@,@CENTPLUGINSTRAPS_BINDIR@,@CENTREON_LOG@,@CENTREON_VARLIB@,@CENTREON_ENGINE_CONNECTORS@"
find_macros_in_dir "$macros" "$TMP_DIR/src/" "www" "Update*.sql" "file_sql_temp"

log "INFO" "$(gettext "Apply macros")"

flg_error=0
${CAT} "$file_sql_temp" | while read file ; do
    log "MACRO" "$(gettext "Change macro for") : $file"
    [ ! -d $(dirname $TMP_DIR/work/$file) ] && \
        mkdir -p  $(dirname $TMP_DIR/work/$file) >> $LOG_FILE 2>&1
    ${SED} -e 's|@CENTREON_ETC@|'"$CENTREON_ETC"'|g' \
        -e 's|@CENTREON_CACHEDIR@|'"$CENTREON_CACHEDIR"'|g' \
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

### Step 2.0: Modify rights on Centreon WebFront and replace macros

## create a random APP_SECRET key
HEX_KEY=($(dd if=/dev/urandom bs=32 count=1 status=none | $PHP_BIN -r "echo bin2hex(fread(STDIN, 32));"))
log "INFO" "$(gettext "Generated a random key") : $HEX_KEY"

## use this step to change macros on php file...
macros="@CENTREON_ETC@,@CENTREON_CACHEDIR@,@CENTPLUGINSTRAPS_BINDIR@,@CENTREON_LOG@,@CENTREON_VARLIB@,@CENTREONTRAPD_BINDIR@,@PHP_BIN@"
find_macros_in_dir "$macros" "$TMP_DIR/src/" "www" "*.php" "file_php_temp"
find_macros_in_dir "$macros" "$TMP_DIR/src/" "bin" "*" "file_bin_temp"
log "INFO" "$(gettext "Apply macros on php files")"

flg_error=0
${CAT} "$file_php_temp" "$file_bin_temp" | while read file ; do
    log "MACRO" "$(gettext "Change macro for") : $file"
    [ ! -d $(dirname $TMP_DIR/work/$file) ] && \
        mkdir -p  $(dirname $TMP_DIR/work/$file) >> $LOG_FILE 2>&1
    ${SED} -e 's|@CENTREON_ETC@|'"$CENTREON_ETC"'|g' \
        -e 's|@CENTREON_CACHEDIR@|'"$CENTREON_CACHEDIR"'|g' \
        -e 's|@CENTPLUGINSTRAPS_BINDIR@|'"$CENTPLUGINSTRAPS_BINDIR"'|g' \
        -e 's|@CENTREONTRAPD_BINDIR@|'"$CENTREON_BINDIR"'|g' \
        -e 's|@CENTREON_VARLIB@|'"$CENTREON_VARLIB"'|g' \
        -e 's|@CENTREON_LOG@|'"$CENTREON_LOG"'|g' \
        -e 's|@PHP_BIN@|'"$PHP_BIN"'|g' \
        $TMP_DIR/src/$file > $TMP_DIR/work/$file
        [ $? -ne 0 ] && flg_error=1
    log "MACRO" "$(gettext "Copy in final dir") : $file"
    cp -f $TMP_DIR/work/$file $TMP_DIR/final/$file >> $LOG_FILE 2>&1
done
check_result $flg_error "$(gettext "Change macros for php files")"

macros="@CENTREON_ETC@,@CENTREON_CACHEDIR@,@CENTPLUGINSTRAPS_BINDIR@,@CENTREON_LOG@,@CENTREON_VARLIB@,@CENTREONTRAPD_BINDIR@,%APP_SECRET%"
find_macros_in_dir "$macros" "$TMP_DIR/src" "config" "*.php*" "file_php_config_temp"
find_macros_in_dir "$macros" "$TMP_DIR/src/" "." ".env*" "file_env_temp"
log "INFO" "$(gettext "Apply macros on env and config files")"

flg_error=0
${CAT} "$file_php_config_temp" "$file_env_temp" | while read file ; do
        log "MACRO" "$(gettext "Change macro for") : $file"
        [ ! -d $(dirname $TMP_DIR/work/$file) ] && \
                mkdir -p  $(dirname $TMP_DIR/work/$file) >> $LOG_FILE 2>&1
        ${SED} -e 's|@CENTREON_ETC@|'"$CENTREON_ETC"'|g' \
                -e 's|@CENTREON_CACHEDIR@|'"$CENTREON_CACHEDIR"'|g' \
                -e 's|@CENTPLUGINSTRAPS_BINDIR@|'"$CENTPLUGINSTRAPS_BINDIR"'|g' \
                -e 's|@CENTREONTRAPD_BINDIR@|'"$CENTREON_BINDIR"'|g' \
                -e 's|@CENTREON_VARLIB@|'"$CENTREON_VARLIB"'|g' \
                -e 's|@CENTREON_LOG@|'"$CENTREON_LOG"'|g' \
                -e 's|%APP_SECRET%|'"$HEX_KEY"'|g' \
                $TMP_DIR/src/$file > $TMP_DIR/work/$file
                [ $? -ne 0 ] && flg_error=1
        log "MACRO" "$(gettext "Copy in final dir") : $file"
        cp -f $TMP_DIR/work/$file $TMP_DIR/final/$file >> $LOG_FILE 2>&1
done
check_result $flg_error "$(gettext "Change macros for php env and config file")"

### Step 2.1 : replace macro for perl binary

## use this step to change macros on perl file...
macros="@CENTREON_ETC@,@CENTREON_CACHEDIR@,@CENTPLUGINSTRAPS_BINDIR@,@CENTREON_LOG@,@CENTREON_VARLIB@,@CENTREONTRAPD_BINDIR@"
find_macros_in_dir "$macros" "$TMP_DIR/src" "bin/" "*" "file_perl_temp"

log "INFO" "$(gettext "Apply macros")"

flg_error=0
${CAT} "$file_perl_temp" | while read file ; do
        log "MACRO" "$(gettext "Change macro for") : $file"
        [ ! -d $(dirname $TMP_DIR/work/$file) ] && \
                mkdir -p  $(dirname $TMP_DIR/work/$file) >> $LOG_FILE 2>&1
        ${SED} -e 's|@CENTREON_ETC@|'"$CENTREON_ETC"'|g' \
                -e 's|@CENTREON_CACHEDIR@|'"$CENTREON_CACHEDIR"'|g' \
                -e 's|@CENTPLUGINSTRAPS_BINDIR@|'"$CENTPLUGINSTRAPS_BINDIR"'|g' \
                -e 's|@CENTREONTRAPD_BINDIR@|'"$CENTREON_BINDIR"'|g' \
                -e 's|@CENTREON_VARLIB@|'"$CENTREON_VARLIB"'|g' \
                -e 's|@CENTREON_LOG@|'"$CENTREON_LOG"'|g' \
                $TMP_DIR/src/$file > $TMP_DIR/work/$file
                [ $? -ne 0 ] && flg_error=1
        log "MACRO" "$(gettext "Copy in final dir") : $file"
        cp -f $TMP_DIR/work/$file $TMP_DIR/final/$file >> $LOG_FILE 2>&1
done
check_result $flg_error "$(gettext "Change macros for perl binary")"

### Step 3: Modify rights on monitoring engine /etc/centreon folder
log "INFO" "$(gettext "Modify rights on") $MONITORINGENGINE_ETC"
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
check_result $flg_error "$(gettext "Modify rights on") $MONITORINGENGINE_ETC"

### Modify rights to broker /etc/centreon-broker folder
log "INFO" "$(gettext "Modify rights on ") $BROKER_ETC"
flg_error=0
if [ -z "$BROKER_USER" ]; then
    BROKER_USER=$MONITORINGENGINE_USER
    get_primary_group "$BROKER_USER" "BROKER_GROUP"
else
    get_primary_group "$BROKER_USER" "BROKER_GROUP"
    add_group "$WEB_USER" "$BROKER_GROUP"
    add_group "$MONITORINGENGINE_USER" "$BROKER_GROUP"
    add_group "$BROKER_USER" "$CENTREON_GROUP"
fi

## Configure Gorgone user and group
add_group "$CENTREON_USER" "$GORGONE_GROUP"
add_group "$WEB_USER" "$GORGONE_GROUP"
add_group "$GORGONE_USER" "$CENTREON_GROUP"
add_group "$GORGONE_USER" "$BROKER_GROUP"
add_group "$GORGONE_USER" "$MONITORINGENGINE_GROUP"
add_group "$GORGONE_USER" "$WEB_GROUP"

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
    check_result $flg_error "$(gettext "Modify rights on") $BROKER_ETC"
fi

if [ "$upgrade" = "1" ]; then
    echo_info "$(gettext "Disconnect users from WebUI")"
    php $INSTALL_DIR/clean_session.php "$CENTREON_ETC" >> "$LOG_FILE" 2>&1
    check_result $? "$(gettext "All users are disconnected")"
fi

### Step 4: Copy final stuff in system folder
echo_info "$(gettext "Copy CentWeb in system directory")"
$INSTALL_DIR/cinstall $cinstall \
    -u "$CENTREON_USER" -g "$CENTREON_GROUP" -d 775 \
    $INSTALL_DIR_CENTREON/www >> "$LOG_FILE" 2>&1

$INSTALL_DIR/cinstall $cinstall_opts \
    -u "$CENTREON_USER" -g "$CENTREON_GROUP" -d 755 -m 644 \
    -p $TMP_DIR/final/www \
    $TMP_DIR/final/www/* $INSTALL_DIR_CENTREON/www/ >> "$LOG_FILE" 2>&1
check_result $? "$(gettext "Install CentWeb (web front of centreon)")"

cp -Rf $TMP_DIR/final/src $INSTALL_DIR_CENTREON/ >> "$LOG_FILE" 2>&1
$CHOWN -R $WEB_USER:$WEB_GROUP $INSTALL_DIR_CENTREON/src

cp -Rf $TMP_DIR/final/api $INSTALL_DIR_CENTREON/ >> "$LOG_FILE" 2>&1
$CHOWN -R $WEB_USER:$WEB_GROUP $INSTALL_DIR_CENTREON/api

log "INFO" "$(gettext "Modify rights for install directory")"
$CHOWN -R $WEB_USER:$WEB_GROUP $INSTALL_DIR_CENTREON/www/install/
check_result $? "$(gettext "Modify rights for install directory")"

[ ! -d "$INSTALL_DIR_CENTREON/www/modules" ] && \
    $INSTALL_DIR/cinstall $cinstall_opts \
        -u "$CENTREON_USER" -g "$CENTREON_GROUP" -d 755 \
        $INSTALL_DIR_CENTREON/www/modules >> "$LOG_FILE" 2>&1

[ ! -d "$INSTALL_DIR_CENTREON/www/img/media" ] && \
    $INSTALL_DIR/cinstall $cinstall_opts \
        -u "$CENTREON_USER" -g "$CENTREON_GROUP" -d 775 \
        $INSTALL_DIR_CENTREON/www/img/media >> "$LOG_FILE" 2>&1

cp -f $TMP_DIR/final/bootstrap.php $INSTALL_DIR_CENTREON/bootstrap.php >> "$LOG_FILE" 2>&1
$CHOWN $WEB_USER:$WEB_GROUP $INSTALL_DIR_CENTREON/bootstrap.php

cp -f $TMP_DIR/final/.env $INSTALL_DIR_CENTREON/.env >> "$LOG_FILE" 2>&1
$CHOWN $WEB_USER:$WEB_GROUP $INSTALL_DIR_CENTREON/.env

cp -f $TMP_DIR/final/.env.local.php $INSTALL_DIR_CENTREON/.env.local.php >> "$LOG_FILE" 2>&1
$CHOWN $WEB_USER:$WEB_GROUP $INSTALL_DIR_CENTREON/.env.local.php

cp -f $TMP_DIR/final/container.php $INSTALL_DIR_CENTREON/container.php >> "$LOG_FILE" 2>&1
$CHOWN $WEB_USER:$WEB_GROUP $INSTALL_DIR_CENTREON/container.php

cp -Rf $TMP_DIR/final/vendor $INSTALL_DIR_CENTREON/ >> "$LOG_FILE" 2>&1
$CHOWN -R $WEB_USER:$WEB_GROUP $INSTALL_DIR_CENTREON/vendor

cp -f $TMP_DIR/final/composer.json $INSTALL_DIR_CENTREON/composer.json >> "$LOG_FILE" 2>&1
$CHOWN $WEB_USER:$WEB_GROUP $INSTALL_DIR_CENTREON/composer.json

cp -f $TMP_DIR/final/package.json $INSTALL_DIR_CENTREON/package.json >> "$LOG_FILE" 2>&1
$CHOWN $WEB_USER:$WEB_GROUP $INSTALL_DIR_CENTREON/package.json

cp -f $TMP_DIR/final/package-lock.json $INSTALL_DIR_CENTREON/package-lock.json >> "$LOG_FILE" 2>&1
$CHOWN $WEB_USER:$WEB_GROUP $INSTALL_DIR_CENTREON/package-lock.json

$INSTALL_DIR/cinstall $cinstall \
        -u "$CENTREON_USER" -g "$CENTREON_GROUP" -d 775 \
        $INSTALL_DIR_CENTREON/config >> "$LOG_FILE" 2>&1
cp -Rf $TMP_DIR/final/config/* $INSTALL_DIR_CENTREON/config/ >> "$LOG_FILE" 2>&1
$CHOWN -R $WEB_USER:$WEB_GROUP $INSTALL_DIR_CENTREON/config

$INSTALL_DIR/cinstall $cinstall_opts \
    -u "$CENTREON_USER" -g "$CENTREON_GROUP" -d 775 \
    $CENTREON_CACHEDIR/config >> "$LOG_FILE" 2>&1
$INSTALL_DIR/cinstall $cinstall_opts \
    -u "$CENTREON_USER" -g "$CENTREON_GROUP" -d 775 \
    $CENTREON_CACHEDIR/config/engine >> "$LOG_FILE" 2>&1
$INSTALL_DIR/cinstall $cinstall_opts \
    -u "$CENTREON_USER" -g "$CENTREON_GROUP" -d 775 \
    $CENTREON_CACHEDIR/config/broker >> "$LOG_FILE" 2>&1
$INSTALL_DIR/cinstall $cinstall_opts \
    -u "$CENTREON_USER" -g "$CENTREON_GROUP" -d 775 \
    $CENTREON_CACHEDIR/config/export >> "$LOG_FILE" 2>&1
$INSTALL_DIR/cinstall $cinstall_opts \
    -u "$CENTREON_USER" -g "$CENTREON_GROUP" -d 775 \
    $CENTREON_CACHEDIR/symfony >> "$LOG_FILE" 2>&1

log "INFO" "$(gettext "Copying GPL_LIB")"
$INSTALL_DIR/cinstall $cinstall_opts \
    -u "$CENTREON_USER" -g "$CENTREON_GROUP" -d 755 -m 644 \
    $TMP_DIR/final/GPL_LIB/* $INSTALL_DIR_CENTREON/GPL_LIB/ >> "$LOG_FILE" 2>&1
check_result $? "$(gettext "Install libraries")"

log "INFO" "$(gettext "Add rights for Smarty cache and compile")"
$CHMOD -R g+w $INSTALL_DIR_CENTREON/GPL_LIB/SmartyCache
check_result $? "$(gettext "Write rights to Smarty Cache")"

## Cron stuff
## need to add stuff for Unix system... (freeBSD...)
log "INFO" "$(gettext "Change macros for centreon.cron")"
${SED} -e 's|@PHP_BIN@|'"$PHP_BIN"'|g' \
    -e 's|@PERL_BIN@|'"$BIN_PERL"'|g' \
    -e 's|@CENTREON_ETC@|'"$CENTREON_ETC"'|g' \
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
    -e 's|@PHP_BIN@|'"$PHP_BIN"'|g' \
    $TMP_DIR/src/cron/centAcl.php > $TMP_DIR/work/cron/centAcl.php
check_result $? "$(gettext "Change macros for centAcl.php")"

cp -f $TMP_DIR/work/cron/centAcl.php \
    $TMP_DIR/final/cron/centAcl.php >> "$LOG_FILE" 2>&1

log "INFO" "$(gettext "Change macros for downtimeManager.php")"
${SED} -e 's|@CENTREON_ETC@|'"$CENTREON_ETC"'|g' \
    -e 's|@CENTREON_VARLIB@|'"$CENTREON_VARLIB"'|g' \
    -e 's|@PHP_BIN@|'"$PHP_BIN"'|g' \
    $TMP_DIR/src/cron/downtimeManager.php > $TMP_DIR/work/cron/downtimeManager.php
check_result $? "$(gettext "Change macros for downtimeManager.php")"

cp -f $TMP_DIR/work/cron/downtimeManager.php \
    $TMP_DIR/final/cron/downtimeManager.php >> "$LOG_FILE" 2>&1

log "INFO" "$(gettext "Change macros for centreon-backup.pl")"
${SED} -e 's|@CENTREON_ETC@|'"$CENTREON_ETC"'|g' \
    -e 's|@PHP_BIN@|'"$PHP_BIN"'|g' \
    $TMP_DIR/src/cron/centreon-backup.pl > $TMP_DIR/work/cron/centreon-backup.pl
check_result $? "$(gettext "Change macros for centreon-backup.pl")"

cp -f $TMP_DIR/work/cron/centreon-backup.pl \
    $TMP_DIR/final/cron/centreon-backup.pl >> "$LOG_FILE" 2>&1

log "INFO" "$(gettext "Install cron directory")"
$INSTALL_DIR/cinstall $cinstall_opts \
    -u "$CENTREON_USER" -g "$CENTREON_GROUP" -d 755 -m 644 \
    $TMP_DIR/final/cron/* $INSTALL_DIR_CENTREON/cron/ >> "$LOG_FILE" 2>&1
check_result $? "$(gettext "Install cron directory")"

log "INFO" "$(gettext "Modify rights for eventReportBuilder")"
${CHMOD} 755 $INSTALL_DIR_CENTREON/cron/eventReportBuilder >> "$LOG_FILE" 2>&1
check_result $? "$(gettext "Modify rights for eventReportBuilder")"

log "INFO" "$(gettext "Modify rights for dashboardBuilder")"
${CHMOD} 755 $INSTALL_DIR_CENTREON/cron/dashboardBuilder >> "$LOG_FILE" 2>&1
check_result $? "$(gettext "Modify rights for dashboardBuilder")"

log "INFO" "$(gettext "Modify rights for centreon-backup.pl")"
${CHMOD} 755 $INSTALL_DIR_CENTREON/cron/centreon-backup.pl >> "$LOG_FILE" 2>&1
check_result $? "$(gettext "Modify rights for centreon-backup.pl")"

log "INFO" "$(gettext "Modify rights for centreon-backup-mysql.sh")"
${CHMOD} 755 $INSTALL_DIR_CENTREON/cron/centreon-backup-mysql.sh >> "$LOG_FILE" 2>&1
check_result $? "$(gettext "Modify rights for centreon-backup-mysql.sh")"

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

## Install traps insert binary
log "INFO" "$(gettext "Prepare centFillTrapDB")"
cp $TMP_DIR/src/bin/centFillTrapDB \
    $TMP_DIR/final/bin/centFillTrapDB >> "$LOG_FILE" 2>&1
check_result $? "$(gettext "Prepare centFillTrapDB")"

log "INFO" "$(gettext "Install centFillTrapDB")"
$INSTALL_DIR/cinstall $cinstall_opts \
    -m 755 \
    $TMP_DIR/final/bin/centFillTrapDB \
    $CENTREON_BINDIR/centFillTrapDB >> $LOG_FILE 2>&1
check_result $? "$(gettext "Install centFillTrapDB")"

## Install centreon_trap_send
log "INFO" "$(gettext "Prepare centreon_trap_send")"
cp $TMP_DIR/src/bin/centreon_trap_send \
    $TMP_DIR/final/bin/centreon_trap_send >> "$LOG_FILE" 2>&1
check_result $? "$(gettext "Prepare centreon_trap_send")"

log "INFO" "$(gettext "Install centreon_trap_send")"
$INSTALL_DIR/cinstall $cinstall_opts \
    -m 755 \
    $TMP_DIR/final/bin/centreon_trap_send \
    $CENTREON_BINDIR/centreon_trap_send >> $LOG_FILE 2>&1
check_result $? "$(gettext "Install centreon_trap_send")"

## Install centreon_check_perfdata
log "INFO" "$(gettext "Prepare centreon_check_perfdata")"
cp $TMP_DIR/src/bin/centreon_check_perfdata \
    $TMP_DIR/final/bin/centreon_check_perfdata >> "$LOG_FILE" 2>&1
check_result $? "$(gettext "Prepare centreon_check_perfdata")"

log "INFO" "$(gettext "Install centreon_check_perfdata")"
$INSTALL_DIR/cinstall $cinstall_opts \
    -m 755 \
    $TMP_DIR/final/bin/centreon_check_perfdata \
    $CENTREON_BINDIR/centreon_check_perfdata >> $LOG_FILE 2>&1
check_result $? "$(gettext "Install centreon_check_perfdata")"

## Install centreonSyncPlugins
log "INFO" "$(gettext "Prepare centreonSyncPlugins")"
cp $TMP_DIR/src/bin/centreonSyncPlugins \
    $TMP_DIR/final/bin/centreonSyncPlugins >> "$LOG_FILE" 2>&1
check_result $? "$(gettext "Prepare centreonSyncPlugins")"

log "INFO" "$(gettext "Install centreonSyncPlugins")"
$INSTALL_DIR/cinstall $cinstall_opts \
    -m 755 \
    $TMP_DIR/final/bin/centreonSyncPlugins \
    $CENTREON_BINDIR/centreonSyncPlugins >> $LOG_FILE 2>&1
check_result $? "$(gettext "Install centreonSyncPlugins")"

## Install centreonSyncArchives
log "INFO" "$(gettext "Prepare centreonSyncArchives")"
cp $TMP_DIR/src/bin/centreonSyncArchives \
    $TMP_DIR/final/bin/centreonSyncArchives >> "$LOG_FILE" 2>&1
check_result $? "$(gettext "Prepare centreonSyncArchives")"

log "INFO" "$(gettext "Install centreonSyncArchives")"
$INSTALL_DIR/cinstall $cinstall_opts \
    -m 755 \
    $TMP_DIR/final/bin/centreonSyncArchives \
    $CENTREON_BINDIR/centreonSyncArchives >> $LOG_FILE 2>&1
check_result $? "$(gettext "Install centreonSyncArchives")"

## Install generateSqlLite
log "INFO" "$(gettext "Install generateSqlLite")"
$INSTALL_DIR/cinstall $cinstall_opts \
    -m 755 \
    $TMP_DIR/final/bin/generateSqlLite \
    $CENTREON_BINDIR/generateSqlLite >> $LOG_FILE 2>&1
check_result $? "$(gettext "Install generateSqlLite")"

## Install changeRrdDsName
log "INFO" "$(gettext "Install changeRrdDsName.pl")"
$INSTALL_DIR/cinstall $cinstall_opts \
        -m 755 \
        $TMP_DIR/final/bin/changeRrdDsName.pl \
        $CENTREON_BINDIR/changeRrdDsName.pl >> $LOG_FILE 2>&1
check_result $? "$(gettext "Install changeRrdDsName.pl")"

## Install binaries for check indexes
log "INFO" "$(gettext "Install export-mysql-indexes")"
$INSTALL_DIR/cinstall $cinstall_opts \
    -m 755 \
    $TMP_DIR/final/bin/export-mysql-indexes \
    $CENTREON_BINDIR/export-mysql-indexes >> $LOG_FILE 2>&1
check_result $? "$(gettext "Install export-mysql-indexes")"

log "INFO" "$(gettext "Install import-mysql-indexes")"
$INSTALL_DIR/cinstall $cinstall_opts \
    -m 755 \
    $TMP_DIR/final/bin/import-mysql-indexes \
    $CENTREON_BINDIR/import-mysql-indexes >> $LOG_FILE 2>&1
check_result $? "$(gettext "Install import-mysql-indexes")"

# Install Centreon CLAPI command line
log "INFO" "$(gettext "Install clapi binary")"
$INSTALL_DIR/cinstall $cinstall_opts \
    -m 755 \
    $TMP_DIR/final/bin/centreon \
    $CENTREON_BINDIR/centreon >> $LOG_FILE 2>&1
check_result $? "$(gettext "Install clapi binary")"

# Install centreon perl lib
    $INSTALL_DIR/cinstall $cinstall_opts -m 755 \
        $TMP_DIR/src/lib/perl/centreon/common/ \
        $PERL_LIB_DIR/centreon/common/ >> $LOG_FILE 2>&1
    $INSTALL_DIR/cinstall $cinstall_opts -m 755 \
        $TMP_DIR/src/lib/perl/centreon/script.pm \
        $PERL_LIB_DIR/centreon/script.pm >> $LOG_FILE 2>&1
    $INSTALL_DIR/cinstall $cinstall_opts -m 755 \
        $TMP_DIR/src/lib/perl/centreon/reporting/ \
        $PERL_LIB_DIR/centreon/reporting/ >> $LOG_FILE 2>&1
    $INSTALL_DIR/cinstall $cinstall_opts -m 755 \
        $TMP_DIR/src/lib/perl/centreon/script/dashboardBuilder.pm \
        $PERL_LIB_DIR/centreon/script/dashboardBuilder.pm >> $LOG_FILE 2>&1
    $INSTALL_DIR/cinstall $cinstall_opts -m 755 \
        $TMP_DIR/src/lib/perl/centreon/script/eventReportBuilder.pm \
        $PERL_LIB_DIR/centreon/script/eventReportBuilder.pm >> $LOG_FILE 2>&1
    $INSTALL_DIR/cinstall $cinstall_opts -m 755 \
        $TMP_DIR/src/lib/perl/centreon/script/logAnalyser.pm \
        $PERL_LIB_DIR/centreon/script/logAnalyser.pm >> $LOG_FILE 2>&1
    $INSTALL_DIR/cinstall $cinstall_opts -m 755 \
        $TMP_DIR/src/lib/perl/centreon/script/logAnalyserBroker.pm \
        $PERL_LIB_DIR/centreon/script/logAnalyserBroker.pm >> $LOG_FILE 2>&1
    echo_success "$(gettext "Centreon Web Perl lib installed")" "$ok"
    log "INFO" "$(gettext "Centreon Web Perl lib installed")"
# End

# Install libraries for Centreon CLAPI
$INSTALL_DIR/cinstall $cinstall_opts -m 755 \
    $TMP_DIR/src/lib/Centreon/ \
    $INSTALL_DIR_CENTREON/lib/Centreon/ >> $LOG_FILE 2>&1

## Prepare to install all pear modules needed.
# use check_pear.php script
echo -e "\n$line"
echo -e "\t$(gettext "Pear Modules")"
echo -e "$line"
pear_module="0"
first=1
while [ "$pear_module" -eq 0 ] ; do
    check_pear_module "$INSTALL_VARS_DIR/$PEAR_MODULES_LIST"
    if [ "$?" -ne 0 ] ; then
            if [ "${PEAR_AUTOINST:-0}" -eq 0 ]; then
                if [ "$first" -eq 0 ] ; then
                    echo_info "$(gettext "Unable to upgrade PEAR modules. You seem to have a connection problem.")"
                fi
                yes_no_default "$(gettext "Do you want to install/upgrade your PEAR modules")" "$yes"
                [ "$?" -eq 0 ] && PEAR_AUTOINST=1
            fi
        if [ "${PEAR_AUTOINST:-0}" -eq 1 ] ; then
            upgrade_pear_module "$INSTALL_VARS_DIR/$PEAR_MODULES_LIST"
                install_pear_module "$INSTALL_VARS_DIR/$PEAR_MODULES_LIST"
                PEAR_AUTOINST=0
                first=0
            else
            pear_module="1"
            fi
    else
        echo_success "$(gettext "All PEAR modules")" "$ok"
        pear_module="1"
    fi
done

#----
## Gorgone specific tasks
#----
echo "$line"
echo -e "\t$(gettext "Achieve gorgone's module integration")"
echo "$line"
## Copy pollers SSH keys (in case of upgrade) to the new "user" gorgone
if [ "$upgrade" = "1" ]; then

    copy_ssh_keys_to_gorgone
fi
## Create gorgone's configuration structure
create_gorgone_configuration_structure

echo "$line"
echo -e "\t$(gettext "Create configuration and installation files")"
echo "$line"
## Create configfile for web install
createConfFile

## Write install config file
createCentreonInstallConf

## wait sql inject script....
