#!/bin/bash
#----
## @Synopsis    Install Script for Centreon project
## @Copyright    Copyright 2008, Guillaume Watteeux
## @Copyright    Copyright 2008-2021, Centreon
## @License    GPL : http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
## Centreon Install Script
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
#

#----
## Usage information for install.sh
## @Sdtout    Usage information
#----
usage() {
    local program=$0
    echo -e "Usage: $program"
    echo -e "  -i\tinstall Centreon with interactive interface"
    echo -e "  -s\tinstall Centreon silently"
    echo -e "  -u\tupgrade Centreon specifying the directory of instCentWeb.conf file"
    echo -e "  -e\textra variables, 'VAR=value' format (overrides input files)"
    exit 1
}

## Use TRAPs to call clean_and_exit when user press
## CRTL+C or exec kill -TERM.
trap clean_and_exit SIGINT SIGTERM

## Valid if you are root 
if [ "${FORCE_NO_ROOT:-0}" -ne 0 ]; then
    USERID=$(id -u)
    if [ "$USERID" != "0" ]; then
        echo -e "You must launch this script using a root user"
        exit 1
    fi
fi

## Define where are Centreon sources
BASE_DIR=$(dirname $0)
BASE_DIR=$( cd $BASE_DIR; pwd )
if [ -z "${BASE_DIR#/}" ] ; then
    echo -e "You cannot select the filesystem root folder"
    exit 1
fi
INSTALL_DIR="$BASE_DIR/install"

_tmp_install_opts="0"
silent_install="0"
upgrade="0"

## Get options
while getopts "isu:e:h" Options
do
    case ${Options} in
        i ) silent_install="0"
            _tmp_install_opts="1"
            ;;
        s ) silent_install="1"
            _tmp_install_opts="1"
            ;;
        u ) silent_install="1"
            UPGRADE_FILE="${OPTARG%/}"
            upgrade="1"
            _tmp_install_opts="1"
            ;;
        e ) env_opts+=("$OPTARG")
            ;;
        \?|h ) usage ; exit 0 ;;
        * ) usage ; exit 1 ;;
    esac
done
shift $((OPTIND -1))

if [ "$_tmp_install_opts" -eq 0 ] ; then
    usage
    exit 1
fi

INSTALLATION_MODE="install"
if [ ! -z "$upgrade" ] && [ "$upgrade" -eq 1 ]; then
    INSTALLATION_MODE="upgrade"
fi

## Load default input variables
source $INSTALL_DIR/inputvars.default.env
## Load all functions used in this script
source $INSTALL_DIR/functions

## Define a default log file
if [ ! -z $LOG_FILE ] ; then
    LOG_FILE="$BASE_DIR/log/install.log"
fi
LOG_DIR=$(dirname $LOG_FILE)
[ ! -d "$LOG_DIR" ] && mkdir -p "$LOG_DIR"

## Init LOG_FILE
if [ -e "$LOG_FILE" ] ; then
    mv "$LOG_FILE" "$LOG_FILE.`date +%Y%m%d-%H%M%S`"
fi
${CAT} << __EOL__ > "$LOG_FILE"
__EOL__

# Checking installation script requirements
BINARIES="rm cp mv chmod chown echo more mkdir find grep cat sed tr"
binary_fail="0"
# For the moment, I check if all binary exists in PATH.
# After, I must look a solution to use complet path by binary
for binary in $BINARIES; do
    if [ ! -e ${binary} ] ; then
        pathfind_ret "$binary" "PATH_BIN"
        if [ "$?" -ne 0 ] ; then
            echo_error "${binary}" "FAILED"
            binary_fail=1
        fi
    fi
done

## Script stop if one binary is not found
if [ "$binary_fail" -eq 1 ] ; then
    echo_info "Please check failed binary and retry"
    exit 1
else
    echo_success "Script requirements" "OK"
fi

## Search distribution and version
if [ -z "$DISTRIB" ] || [ -z "$DISTRIB_VERSION" ] ; then
    find_os
fi
echo_info "Found distribution" "$DISTRIB $DISTRIB_VERSION"

## Load specific variables based on distribution
if [ -f $INSTALL_DIR/inputvars.$DISTRIB.env ]; then
    echo_info "Loading distribution specific input variables" "install/inputvars.$DISTRIB.env"
    source $INSTALL_DIR/inputvars.$DISTRIB.env
fi

## Load specific variables based on version
if [ -f $INSTALL_DIR/inputvars.$DISTRIB.$DISTRIB_VERSION.env ]; then
    echo_info "Loading version specific input variables" "install/inputvars.$DISTRIB.$DISTRIB_VERSION.env"
    source $INSTALL_DIR/inputvars.$DISTRIB.$DISTRIB_VERSION.env
fi

## Load specific variables defined by user
if [ -f $INSTALL_DIR/../inputvars.env ]; then
    echo_info "Loading user specific input variables" "inputvars.env"
    source $INSTALL_DIR/../inputvars.env
fi

## Load previous installation input variables if upgrade
if [ "$upgrade" -eq 1 ] ; then
    test_file "$UPGRADE_FILE" "Centreon upgrade file"
    if [ "$?" -eq 0 ] ; then
        echo_info "Loading previous installation input variables" "$UPGRADE_FILE"
        source $UPGRADE_FILE
    else
        echo_error "Missing previous installation input variables" "FAILED"
        echo_info "Either specify it in command line or using UPGRADE_FILE input variable"
        exit 1
    fi
fi

## Load variables provided in command line
for env_opt in "${env_opts[@]}"; do
    if [[ "${env_opt}" =~ .+=.+ ]] ; then
        variable=$(echo $env_opt | cut -f1 -d "=")
        value=$(echo $env_opt | cut -f2 -d "=")
        if [ ! -z "$variable" ] && [ ! -z "$value" ] ; then
            echo_info "Loading command line input variables" "${variable}=${value}"
            eval ${variable}=${value}
        fi
    fi
done

## Check installation mode
if [ -z "$INSTALLATION_TYPE" ] ; then
    echo_error "Installation mode" "NOT DEFINED"
    exit 1
fi
if [[ ! "${INSTALLATION_TYPE}" =~ ^central|poller$ ]] ; then
    echo_error "Installation mode" "$INSTALLATION_TYPE"
    exit 1
fi
echo_info "Installation type" "$INSTALLATION_TYPE"
echo_info "Installation mode" "$INSTALLATION_MODE"

## Check space of tmp dir
check_tmp_disk_space
if [ "$?" -eq 1 ] ; then
    if [ "$silent_install" -eq 1 ] ; then
        purge_centreon_tmp_dir "silent"
    else
        purge_centreon_tmp_dir
    fi
fi

## Installation is interactive
if [ "$silent_install" -ne 1 ] ; then
    echo -e "\n"
    echo_info "Welcome to Centreon installation script!"
    yes_no_default "Should we start?" "$yes"
    if [ "$?" -ne 0 ] ; then
        echo_info "Exiting"
        exit 1
    fi
fi

# Start installation

ERROR_MESSAGE=""

# Centreon installation requirements
echo_title "Centreon installation requirements"
echo_line "Checking installation requirements"

if [[ "${INSTALLATION_TYPE}" =~ ^central|poller$ ]] ; then
    # System
    test_dir_from_var "SUDOERSD_ETC_DIR" "Sudoers directory"
    test_dir_from_var "LOGROTATED_ETC_DIR" "Logrotate directory"
    test_dir_from_var "CROND_ETC_DIR" "Cron directory"
    test_dir_from_var "SNMP_ETC_DIR" "SNMP configuration directory"
    test_dir_from_var "SYSTEMD_ETC_DIR" "SystemD directory"
    test_dir_from_var "SYSCONFIG_ETC_DIR" "Sysconfig directory"

    ## Perl information
    find_perl_info
    test_file_from_var "PERL_BINARY" "Perl binary"
    test_dir_from_var "PERL_LIB_DIR" "Perl libraries directory"
fi
if [[ "${INSTALLATION_TYPE}" =~ ^central$ ]] ; then
    ## Centreon
    test_dir "$BASE_DIR/vendor" "Composer dependencies"
    test_dir "$BASE_DIR/www/static" "Frontend application"

    ## System
    test_file_from_var "RRDTOOL_BINARY" "RRDTool binary"
    test_file_from_var "MAIL_BINARY" "Mail binary"

    ## Apache information
    find_apache_info
    test_user_from_var "APACHE_USER" "Apache user"
    test_group_from_var "APACHE_GROUP" "Apache group"
    test_dir_from_var "APACHE_DIR" "Apache directory"
    test_dir_from_var "APACHE_CONF_DIR" "Apache configuration directory"
    test_var "APACHE_SERVICE" "MariaDB service"

    ## MariaDB information
    if [ "$WITH_DB" -ne 0 ] ; then
        find_mariadb_info
        test_dir_from_var "MARIADB_CONF_DIR" "MariaDB configuration directory"
        test_dir_from_var "MARIADB_SERVICE_DIR" "MariaDB systemd directory"
        test_var "MARIADB_SERVICE" "MariaDB service"
    fi

    ## PHP information
    find_php_info
    find_phpfpm_info
    get_timezone
    test_var "PHP_TIMEZONE" "PHP timezone"
    test_dir_from_var "PHPFPM_LOG_DIR" "PHP FPM log directory"
    test_dir_from_var "PHPFPM_CONF_DIR" "PHP FPM configuration directory"
    test_dir_from_var "PHPFPM_SERVICE_DIR" "PHP FPM service directory"
    test_var "PHPFPM_SERVICE" "PHP FPM service"
    test_dir_from_var "PHP_ETC_DIR" "PHP configuration directory"
    test_file_from_var "PHP_BINARY" "PHP binary"
    test_php_version

    ## Engine information
    test_user_from_var "ENGINE_USER" "Engine user"
    test_group_from_var "ENGINE_GROUP" "Engine group"
    test_file_from_var "ENGINE_BINARY" "Engine binary"
    test_dir_from_var "ENGINE_ETC_DIR" "Engine configuration directory"
    test_dir_from_var "ENGINE_LOG_DIR" "Engine log directory" 
    test_dir_from_var "ENGINE_LIB_DIR" "Engine library directory"
    test_dir_from_var "ENGINE_CONNECTORS_DIR" "Engine Connectors directory"

    ## Broker information
    test_user_from_var "BROKER_USER" "Broker user"
    test_group_from_var "BROKER_GROUP" "Broker group"
    test_dir_from_var "BROKER_ETC_DIR" "Broker configuration directory"
    test_dir_from_var "BROKER_VARLIB_DIR" "Broker variable library directory"
    test_dir_from_var "BROKER_LOG_DIR" "Broker log directory"
    test_file_from_var "BROKER_MOD_BINARY" "Broker module binary"

    ## Gorgone information
    test_user_from_var "GORGONE_USER" "Gorgone user"
    test_group_from_var "GORGONE_GROUP" "Gorgone group"
    test_dir_from_var "GORGONE_ETC_DIR" "Gorgone configuration directory"
    test_dir_from_var "GORGONE_VARLIB_DIR" "Gorgone variable library directory"

    ## Plugins information
    test_dir_from_var "CENTREON_PLUGINS_DIR" "Centreon Plugins directory"
fi

if [ ! -z "$ERROR_MESSAGE" ] ; then
    echo_error_on_line "FAILED"
    echo_error "\nErrors:"
    echo_error "$ERROR_MESSAGE"
    exit 1
fi
echo_success_on_line "OK"

## Centreon information
echo_title "Centreon information"

if [[ "${INSTALLATION_TYPE}" =~ ^central|poller$ ]] ; then
    test_var_and_show "CENTREON_USER" "Centreon user"
    test_var_and_show "CENTREON_GROUP" "Centreon group"
    test_var_and_show "CENTREON_HOME" "Centreon user home directory"
    test_var_and_show "CENTREON_INSTALL_DIR" "Centreon installation directory"
    test_var_and_show "CENTREON_ETC_DIR" "Centreon configuration directory"
    test_var_and_show "CENTREON_LOG_DIR" "Centreon log directory"
    test_var_and_show "CENTREON_VARLIB_DIR" "Centreon variable library directory"
    test_var_and_show "CENTREON_PLUGINS_TMP_DIR" "Centreon Plugins temporary directory"
    test_var_and_show "CENTREON_CACHE_DIR" "Centreon cache directory"
    test_var_and_show "CENTREONTRAPD_SPOOL_DIR" "Centreontrapd spool directory"
fi
if [[ "${INSTALLATION_TYPE}" =~ ^central$ ]] ; then
    test_var_and_show "CENTREON_CENTCORE_DIR" "Centreon Centcore directory"
    test_var_and_show "CENTREON_RRD_STATUS_DIR" "Centreon RRD status directory"
    test_var_and_show "CENTREON_RRD_METRICS_DIR" "Centreon RRD metrics directory"
    test_var_and_show "USE_HTTPS" "Use HTTPS configuration"
    if [ $USE_HTTPS -eq 1 ] ; then
        test_var_and_show "HTTPS_CERTIFICATE_FILE" "Certificate file path"
        test_var_and_show "HTTPS_CERTIFICATE_KEY_FILE" "Certificate key file path"
    fi
    test_var_and_show "WITH_DB" "Install database configuration"
fi

if [ ! -z "$ERROR_MESSAGE" ] ; then
    echo_error "\nErrors:"
    echo_error "$ERROR_MESSAGE"
    exit 1
fi

if [ "$silent_install" -ne 1 ] ; then 
    yes_no_default "Everything looks good, proceed to installation?"
    if [ "$?" -ne 0 ] ; then
        purge_centreon_tmp_dir "silent"
        exit 1
    fi
fi

# Start installation

## Disconnect user if upgrade
if [ "$upgrade" = "1" ] && [[ "$INSTALLATION_TYPE" =~ central ]] ; then
    echo_info "Disconnect users from WebUI"
    $PHP_BINARY $INSTALL_DIR/clean_session.php "$CENTREON_ETC" >> "$LOG_FILE" 2>&1
    check_result $? "All users are disconnected"
fi

## Create a random APP_SECRET key
if [[ "${INSTALLATION_TYPE}" =~ ^central$ ]] ; then
    HEX_KEY=($(dd if=/dev/urandom bs=32 count=1 status=none | $PHP_BINARY -r "echo bin2hex(fread(STDIN, 32));"));
fi

## Build files
echo_title "Build files"
echo_line "Copying files to '$TMP_DIR'"

if [ -d $TMP_DIR ] ; then
    mv $TMP_DIR $TMP_DIR.`date +%Y%m%d-%k%m%S`
fi

create_dir "$TMP_DIR/source"

if [[ "${INSTALLATION_TYPE}" =~ ^central|poller$ ]] ; then
    {
        copy_dir "$BASE_DIR/bin" "$TMP_DIR/source/" &&
        copy_dir "$BASE_DIR/cron" "$TMP_DIR/source/" &&
        copy_dir "$BASE_DIR/lib" "$TMP_DIR/source/" &&
        copy_dir "$BASE_DIR/logrotate" "$TMP_DIR/source/" &&
        copy_dir "$BASE_DIR/snmptrapd" "$TMP_DIR/source/" &&
        copy_dir "$BASE_DIR/tmpl" "$TMP_DIR/source/" &&
        copy_dir "$BASE_DIR/install" "$TMP_DIR/source/"
    } || {
        echo_error_on_line "FAILED"
        if [ ! -z "$ERROR_MESSAGE" ] ; then
            echo_error "\nErrors:"
            echo_error "$ERROR_MESSAGE"
        fi
        purge_centreon_tmp_dir "silent"
        exit 1
    }
fi
if [[ "${INSTALLATION_TYPE}" =~ ^central$ ]] ; then
    {
        copy_dir "$BASE_DIR/config" "$TMP_DIR/source/" &&
        copy_dir "$BASE_DIR/GPL_LIB" "$TMP_DIR/source/" &&
        copy_dir "$BASE_DIR/src" "$TMP_DIR/source/" &&
        copy_dir "$BASE_DIR/vendor" "$TMP_DIR/source/" &&
        copy_dir "$BASE_DIR/www" "$TMP_DIR/source/" &&
        copy_dir "$BASE_DIR/api" "$TMP_DIR/source/" &&
        copy_file "$BASE_DIR/.env" "$TMP_DIR/source/" &&
        copy_file "$BASE_DIR/.env.local.php" "$TMP_DIR/source/" &&
        copy_file "$BASE_DIR/bootstrap.php" "$TMP_DIR/source/" &&
        copy_file "$BASE_DIR/container.php" "$TMP_DIR/source/" &&
        copy_file "$BASE_DIR/package.json" "$TMP_DIR/source/" &&
        copy_file "$BASE_DIR/composer.json" "$TMP_DIR/source/"
    } || {
        echo_error_on_line "FAILED"
        if [ ! -z "$ERROR_MESSAGE" ] ; then
            echo_error "\nErrors:"
            echo_error "$ERROR_MESSAGE"
        fi
        purge_centreon_tmp_dir "silent"
        exit 1
    }
fi
echo_success_on_line "OK"

echo_line "Replacing macros"
eval "echo \"$(cat "$TMP_DIR/source/install/src/instCentWeb.conf")\"" > $TMP_DIR/source/install/src/instCentWeb.conf
{
    if [[ "${INSTALLATION_TYPE}" =~ ^central|poller$ ]] ; then
        replace_macro "bin cron logrotate snmptrapd tmpl install"
    fi
    if [[ "${INSTALLATION_TYPE}" =~ ^central$ ]] ; then
        replace_macro "config www .env .env.local.php"
    fi
} || {
    echo_error_on_line "FAILED"
    if [ ! -z "$ERROR_MESSAGE" ] ; then
        echo_error "\nErrors:"
        echo_error "$ERROR_MESSAGE"
    fi
    purge_centreon_tmp_dir "silent"
    exit 1
}
echo_success_on_line "OK"

test_user "$CENTREON_USER"
if [ $? -ne 0 ]; then
    {
        # Create user and group
        create_dir "$CENTREON_HOME" &&
        create_group "$CENTREON_GROUP" &&
        create_user "$CENTREON_USER" "$CENTREON_GROUP" "$CENTREON_HOME" &&
        set_ownership "$CENTREON_HOME" "$CENTREON_USER" "$CENTREON_GROUP" &&
        set_permissions "$CENTREON_HOME" "700"
    } || {
        if [ ! -z "$ERROR_MESSAGE" ] ; then
            echo_error "\nErrors:"
            echo_error "$ERROR_MESSAGE"
        fi
        purge_centreon_tmp_dir "silent"
        exit 1
    }
fi

echo_line "Building installation tree"
BUILD_DIR="$TMP_DIR/build"
create_dir "$BUILD_DIR"

if [[ "${INSTALLATION_TYPE}" =~ ^central|poller$ ]] ; then
    {
        # Centreon configuration
        create_dir "$BUILD_DIR/$CENTREON_ETC_DIR" "$CENTREON_USER" "$CENTREON_GROUP" "775" &&
        create_dir "$BUILD_DIR/$CENTREON_ETC_DIR/config.d" "$CENTREON_USER" "$CENTREON_GROUP" "775" &&
        copy_file "$TMP_DIR/source/install/src/config.yaml" "$BUILD_DIR/$CENTREON_ETC_DIR/config.yaml" \
            "$CENTREON_USER" "$CENTREON_GROUP" "664" &&

        ### Log directory
        create_dir "$BUILD_DIR/$CENTREON_LOG_DIR" "$CENTREON_USER" "$CENTREON_GROUP" "775" &&

        ### Variable libraries directory
        create_dir "$BUILD_DIR/$CENTREON_VARLIB_DIR" "$CENTREON_USER" "$CENTREON_GROUP" "775" &&
        create_dir "$BUILD_DIR/$CENTREON_PLUGINS_TMP_DIR" "$CENTREON_USER" "$CENTREON_GROUP" "775" &&

        ### Cache directories
        create_dir "$BUILD_DIR/$CENTREON_CACHE_DIR" "$CENTREON_USER" "$CENTREON_GROUP" "775" &&
        create_dir "$BUILD_DIR/$CENTREON_CACHE_DIR/backup" "$CENTREON_USER" "$CENTREON_GROUP" "750" &&
        create_dir "$BUILD_DIR/$CENTREON_CACHE_DIR/config/engine" "$CENTREON_USER" "$CENTREON_GROUP" "775" &&
        create_dir "$BUILD_DIR/$CENTREON_CACHE_DIR/config/broker" "$CENTREON_USER" "$CENTREON_GROUP" "775" &&
        create_dir "$BUILD_DIR/$CENTREON_CACHE_DIR/config/export" "$CENTREON_USER" "$CENTREON_GROUP" "775" &&

        ### Centreon binaries
        create_dir "$BUILD_DIR/$CENTREON_INSTALL_DIR" "" "" "755" &&
        create_dir "$BUILD_DIR/$CENTREON_INSTALL_DIR/bin" "" "" "755" &&
        copy_file "$TMP_DIR/source/bin/centreontrapd" "$BUILD_DIR/$CENTREON_INSTALL_DIR/bin/centreontrapd" \
            "" "" "755" &&
        copy_file "$TMP_DIR/source/bin/centreontrapdforward" \
            "$BUILD_DIR/$CENTREON_INSTALL_DIR/bin/centreontrapdforward" "" "" "755" &&
        copy_file "$TMP_DIR/source/bin/registerServerTopology.sh" \
            "$BUILD_DIR/$CENTREON_INSTALL_DIR/bin/registerServerTopology.sh" "" "" "755" &&
        copy_file "$TMP_DIR/source/bin/registerServerTopologyTemplate" \
            "$BUILD_DIR/$CENTREON_INSTALL_DIR/bin/registerServerTopologyTemplate" "" "" "644" &&

        ### Centreontrapd
        copy_file "$TMP_DIR/source/install/src/centreontrapd-$INSTALLATION_TYPE.pm" \
            "$BUILD_DIR/$CENTREON_ETC_DIR/centreontrapd.pm" "$CENTREON_USER" "$CENTREON_GROUP" "644" &&
        create_dir "$BUILD_DIR/$CENTREONTRAPD_SPOOL_DIR" "$CENTREON_USER" "$CENTREON_GROUP" "755" &&

        # Centreon Engine and Broker directories (kind of a workaround induced by components source installs)
        create_dir "$BUILD_DIR/$ENGINE_ETC_DIR" "$ENGINE_USER" "$ENGINE_GROUP" "775" &&
        create_dir "$BUILD_DIR/$BROKER_ETC_DIR" "$BROKER_USER" "$BROKER_GROUP" "775" &&
        create_dir "$BUILD_DIR/$BROKER_LOG_DIR" "$BROKER_USER" "$BROKER_GROUP" "775" &&
        create_dir "$BUILD_DIR/$BROKER_VARLIB_DIR" "$BROKER_USER" "$BROKER_GROUP" "775"
    } || {
        echo_error_on_line "FAILED"
        if [ ! -z "$ERROR_MESSAGE" ] ; then
            echo_error "\nErrors:"
            echo_error "$ERROR_MESSAGE"
        fi
        purge_centreon_tmp_dir "silent"
        exit 1
    }
fi
if [[ "${INSTALLATION_TYPE}" =~ ^central$ ]] ; then
    {
        ### Install save file
        copy_file "$TMP_DIR/source/install/src/instCentWeb.conf" \
            "$BUILD_DIR/$CENTREON_ETC_DIR/instCentWeb.conf" \
            "$CENTREON_USER" "$CENTREON_GROUP" "644" &&

        ### Variable libraries directory
        create_dir "$BUILD_DIR/$CENTREON_VARLIB_DIR/installs" "$CENTREON_USER" "$CENTREON_GROUP" "775" &&
        create_dir "$BUILD_DIR/$CENTREON_VARLIB_DIR/log" "$CENTREON_USER" "$CENTREON_GROUP" "775" &&
        create_dir "$BUILD_DIR/$CENTREON_VARLIB_DIR/nagios-perf" "$CENTREON_USER" "$CENTREON_GROUP" "775" &&
        create_dir "$BUILD_DIR/$CENTREON_VARLIB_DIR/perfdata" "$CENTREON_USER" "$CENTREON_GROUP" "775" &&
        create_dir "$BUILD_DIR/$CENTREON_CENTCORE_DIR" "$CENTREON_USER" "$CENTREON_GROUP" "775" &&
        create_dir "$BUILD_DIR/$CENTREON_RRD_STATUS_DIR" "$CENTREON_USER" "$CENTREON_GROUP" "775" &&
        create_dir "$BUILD_DIR/$CENTREON_RRD_METRICS_DIR" "$CENTREON_USER" "$CENTREON_GROUP" "775" &&

        ### Symfony cache directory
        create_dir "$BUILD_DIR/$CENTREON_CACHE_DIR/symfony" "$APACHE_USER" "$APACHE_GROUP" "755" &&

        ### Web directory
        copy_dir "$TMP_DIR/source/www" "$BUILD_DIR/$CENTREON_INSTALL_DIR/www" \
            "$CENTREON_USER" "$CENTREON_GROUP" "775" &&
        copy_file "$TMP_DIR/source/install/src/install.conf.php" \
            "$BUILD_DIR/$CENTREON_INSTALL_DIR/www/install/install.conf.php" \
            "$CENTREON_USER" "$CENTREON_GROUP" "775" &&
        create_dir "$BUILD_DIR/$CENTREON_INSTALL_DIR/www/modules" "$CENTREON_USER" "$CENTREON_GROUP" "775" &&

        ### Sources
        copy_dir "$TMP_DIR/source/src" "$BUILD_DIR/$CENTREON_INSTALL_DIR/src" \
            "$CENTREON_USER" "$CENTREON_GROUP" "775" &&

        ### API files
        copy_dir "$TMP_DIR/source/api" "$BUILD_DIR/$CENTREON_INSTALL_DIR/api" \
            "$CENTREON_USER" "$CENTREON_GROUP" "775" &&

        ### Symfony config directories
        copy_dir "$TMP_DIR/source/vendor" "$BUILD_DIR/$CENTREON_INSTALL_DIR/vendor" "" "" "755" &&
        copy_dir "$TMP_DIR/source/config" "$BUILD_DIR/$CENTREON_INSTALL_DIR/config" "" "" "755" &&
        copy_file "$BUILD_DIR/$CENTREON_INSTALL_DIR/config/centreon.config.php.template" \
            "$BUILD_DIR/$CENTREON_INSTALL_DIR/config/centreon.config.php" "" "" "644" &&

        ### Smarty directories
        copy_dir "$TMP_DIR/source/GPL_LIB" "$BUILD_DIR/$CENTREON_INSTALL_DIR/GPL_LIB" "" "" "755" &&
        set_ownership "$BUILD_DIR/$CENTREON_INSTALL_DIR/GPL_LIB/SmartyCache" "$CENTREON_USER" "$CENTREON_GROUP" &&
        set_permissions "$BUILD_DIR/$CENTREON_INSTALL_DIR/GPL_LIB/SmartyCache" "775" &&

        ### Centreon binaries
        create_dir "$BUILD_DIR/usr/bin" "" "" "555" &&
        copy_file "$TMP_DIR/source/bin/centFillTrapDB" \
            "$BUILD_DIR/$CENTREON_INSTALL_DIR/bin/centFillTrapDB" "" "" "755" &&
        copy_file "$TMP_DIR/source/bin/centreon_health" \
            "$BUILD_DIR/$CENTREON_INSTALL_DIR/bin/centreon_health" "" "" "755" &&
        copy_file "$TMP_DIR/source/bin/centreon_trap_send" \
            "$BUILD_DIR/$CENTREON_INSTALL_DIR/bin/centreon_trap_send" "" "" "755" &&
        copy_file "$TMP_DIR/source/bin/centreonSyncPlugins" \
            "$BUILD_DIR/$CENTREON_INSTALL_DIR/bin/centreonSyncPlugins" "" "" "755" &&
        copy_file "$TMP_DIR/source/bin/centreonSyncArchives" \
            "$BUILD_DIR/$CENTREON_INSTALL_DIR/bin/centreonSyncArchives" "" "" "755" &&
        copy_file "$TMP_DIR/source/bin/generateSqlLite" \
            "$BUILD_DIR/$CENTREON_INSTALL_DIR/bin/generateSqlLite" "" "" "755" &&
        copy_file "$TMP_DIR/source/bin/changeRrdDsName.pl" \
            "$BUILD_DIR/$CENTREON_INSTALL_DIR/bin/changeRrdDsName.pl" "" "" "755" &&
        copy_file "$TMP_DIR/source/bin/migrateWikiPages.php" \
            "$BUILD_DIR/$CENTREON_INSTALL_DIR/bin/migrateWikiPages.php" "" "" "644" &&
        copy_file "$TMP_DIR/source/bin/centreon-partitioning.php" \
            "$BUILD_DIR/$CENTREON_INSTALL_DIR/bin/centreon-partitioning.php" "" "" "644" &&
        copy_file "$TMP_DIR/source/bin/logAnalyserBroker" \
            "$BUILD_DIR/$CENTREON_INSTALL_DIR/bin/logAnalyserBroker" "" "" "755" &&
        create_symlink "$CENTREON_INSTALL_DIR/bin/centFillTrapDB" \
            "$BUILD_DIR/usr/bin/centFillTrapDB" &&
        create_symlink "$CENTREON_INSTALL_DIR/bin/centreon_trap_send" \
            "$BUILD_DIR/usr/bin/centreon_trap_send" &&
        create_symlink "$CENTREON_INSTALL_DIR/bin/centreonSyncPlugins" \
            "$BUILD_DIR/usr/bin/centreonSyncPlugins" &&
        create_symlink "$CENTREON_INSTALL_DIR/bin/centreonSyncArchives" \
            "$BUILD_DIR/usr/bin/centreonSyncArchives" &&
        create_symlink "$CENTREON_INSTALL_DIR/bin/generateSqlLite" \
            "$BUILD_DIR/usr/bin/generateSqlLite" &&
        copy_file "$TMP_DIR/source/bin/import-mysql-indexes" \
            "$BUILD_DIR/$CENTREON_INSTALL_DIR/bin/import-mysql-indexes" \
            "$CENTREON_USER" "$CENTREON_GROUP" "755" &&
        copy_file "$TMP_DIR/source/bin/export-mysql-indexes" \
            "$BUILD_DIR/$CENTREON_INSTALL_DIR/bin/export-mysql-indexes" \
            "$CENTREON_USER" "$CENTREON_GROUP" "755" &&
        copy_file "$TMP_DIR/source/bin/centreon" "$BUILD_DIR/$CENTREON_INSTALL_DIR/bin/centreon" \
            "$CENTREON_USER" "$CENTREON_GROUP" "755" &&
        copy_file "$TMP_DIR/source/bin/console" "$BUILD_DIR/$CENTREON_INSTALL_DIR/bin/console" \
            "$CENTREON_USER" "$CENTREON_GROUP" "755" &&

        ### Centreon CLAPI
        create_dir "$BUILD_DIR/$CENTREON_INSTALL_DIR/lib" "" "" "755" &&
        copy_file "$TMP_DIR/source/lib/Slug.class.php" "$BUILD_DIR/$CENTREON_INSTALL_DIR/lib/Slug.class.php" \
            "" "" "644" &&
        copy_dir "$TMP_DIR/source/lib/Centreon" "$BUILD_DIR/$CENTREON_INSTALL_DIR/lib/Centreon" "" "" "755" &&

        ### Cron binary
        copy_dir "$TMP_DIR/source/cron" "$BUILD_DIR/$CENTREON_INSTALL_DIR/cron" "" "" "775" &&
        set_permissions "$BUILD_DIR/$CENTREON_INSTALL_DIR/cron/*" "775"

        ### Bases
        copy_file "$TMP_DIR/source/bootstrap.php" "$BUILD_DIR/$CENTREON_INSTALL_DIR/bootstrap.php" "" "" "644" &&
        copy_file "$TMP_DIR/source/composer.json" "$BUILD_DIR/$CENTREON_INSTALL_DIR/composer.json" "" "" "644" &&
        copy_file "$TMP_DIR/source/container.php" "$BUILD_DIR/$CENTREON_INSTALL_DIR/container.php" "" "" "644" &&
        copy_file "$TMP_DIR/source/package.json" "$BUILD_DIR/$CENTREON_INSTALL_DIR/package.json" "" "" "644"
    } || {
        echo_error_on_line "FAILED"
        if [ ! -z "$ERROR_MESSAGE" ] ; then
            echo_error "\nErrors:"
            echo_error "$ERROR_MESSAGE"
        fi
        purge_centreon_tmp_dir "silent"
        exit 1
    }
fi
echo_success_on_line "OK"

## Install files
echo_title "Install builded files"
echo_line "Copying files from '$TMP_DIR' to final directory"
copy_dir "$BUILD_DIR/*" "/"
if [ "$?" -ne 0 ] ; then
    echo_error_on_line "FAILED"
    if [ ! -z "$ERROR_MESSAGE" ] ; then
        echo_error "\nErrors:"
        echo_error "$ERROR_MESSAGE"
    fi
    purge_centreon_tmp_dir "silent"
    exit 1
fi
echo_success_on_line "OK"

## Install remaining files
echo_title "Install remaining files"

if [[ "${INSTALLATION_TYPE}" =~ ^central|poller$ ]] ; then
    ### Centreon
    copy_file "$TMP_DIR/source/install/src/centreon.systemd" "$SYSTEMD_ETC_DIR/centreon.service"
    copy_file_no_replace "$TMP_DIR/source/logrotate/centreon" "$LOGROTATED_ETC_DIR/centreon" \
        "Logrotate Centreon configuration"

    ### Centreontrapd
    create_dir "$SNMP_ETC_DIR/centreon_traps" "$CENTREON_USER" "$CENTREON_GROUP" "775"
    copy_file "$TMP_DIR/source/snmptrapd/snmptrapd.conf" "$SNMP_ETC_DIR/snmptrapd.conf"
    copy_file "$TMP_DIR/source/install/src/centreontrapd.systemd" "$SYSTEMD_ETC_DIR/centreontrapd.service"
    copy_file "$TMP_DIR/source/install/src/centreontrapd.sysconfig" "$SYSCONFIG_ETC_DIR/centreontrapd"
    copy_file_no_replace "$TMP_DIR/source/logrotate/centreontrapd" "$LOGROTATED_ETC_DIR/centreontrapd" \
        "Logrotate Centreontrapd configuration"

    ### Perl libraries
    copy_dir "$TMP_DIR/source/lib/perl/centreon" "$PERL_LIB_DIR/centreon"

    ### Sudoers configuration
    copy_file "$TMP_DIR/source/tmpl/install/sudoersCentreonEngine" "$SUDOERSD_ETC_DIR/centreon" \
        "" "" "600"

    ### Centreon Engine and Broker configurations (kind of a workaround induced by components source installs)
    delete_file "$ENGINE_ETC_DIR/"
    delete_file "$BROKER_ETC_DIR/"
fi
if [[ "${INSTALLATION_TYPE}" =~ ^central$ ]] ; then
    ### Centreon CLAPI
    create_symlink "$CENTREON_INSTALL_DIR/bin/centreon" "/usr/bin/centreon" \
        "$CENTREON_USER" "$CENTREON_GROUP"

    ### Cron configurations
    copy_file "$TMP_DIR/source/tmpl/install/centreon.cron" "$CROND_ETC_DIR/centreon"
    copy_file "$TMP_DIR/source/tmpl/install/centstorage.cron" "$CROND_ETC_DIR/centstorage"

    ### Symfony
    copy_file_no_replace "$TMP_DIR/source/.env" "$CENTREON_INSTALL_DIR/.env" "Symfony .env file"
    copy_file_no_replace "$TMP_DIR/source/.env.local.php" "$CENTREON_INSTALL_DIR/.env.local.php" "Symfony .env.local.php file"

    ### Apache
    restart_apache="0"
    if [ $USE_HTTPS -eq 1 ] ; then
        copy_file_no_replace "$TMP_DIR/source/install/src/centreon-apache-https.conf" \
            "$APACHE_CONF_DIR/10-centreon.conf" "Apache configuration" && restart_apache="1"
    else
        copy_file_no_replace "$TMP_DIR/source/install/src/centreon-apache.conf" \
            "$APACHE_CONF_DIR/10-centreon.conf" "Apache configuration" && restart_apache="1"
    fi

    ### PHP FPM
    restart_php_fpm="0"
    create_dir "$PHPFPM_VARLIB_DIR/session" "root" "$APACHE_GROUP" "770"
    delete_file "$PHPFPM_VARLIB_DIR/session/*"
    create_dir "$PHPFPM_VARLIB_DIR/wsdlcache" "root" "$APACHE_GROUP" "775"
    copy_file_no_replace "$TMP_DIR/source/install/src/php-fpm.conf" "$PHPFPM_CONF_DIR/centreon.conf" \
        "PHP FPM configuration" && restart_php_fpm="1"
    copy_file_no_replace "$TMP_DIR/source/install/src/php-fpm-systemd.conf" "$PHPFPM_SERVICE_DIR/centreon.conf" \
        "PHP FPM service configuration" && restart_php_fpm="1"
    copy_file_no_replace "$TMP_DIR/source/install/src/php.ini" "$PHP_ETC_DIR/50-centreon.ini" \
        "PHP configuration" && restart_php_fpm="1"
    #### openSUSE hack
    [[ "$DISTRIB" =~ suse ]] && copy_file_no_replace "/etc/php7/fpm/php-fpm.conf.default" \
        "/etc/php7/fpm/php-fpm.conf" "PHP default configuration" && restart_php_fpm="1"

    ### MariaDB
    restart_mariadb="0"
    if [ "$WITH_DB" -ne 0 ] ; then
        copy_file_no_replace "$TMP_DIR/source/install/src/centreon-mysql.cnf" "$MARIADB_CONF_DIR/centreon.cnf" \
            "MariaDB configuration" && restart_mariadb="1"
        copy_file_no_replace "$TMP_DIR/source/install/src/mariadb-systemd.conf" "$MARIADB_SERVICE_DIR/centreon.conf" \
            "MariaDB service configuration" && restart_mariadb="1"
    fi
fi

if [ ! -z "$ERROR_MESSAGE" ] ; then
    echo_error "\nErrors:"
    echo_error "$ERROR_MESSAGE"
    ERROR_MESSAGE=""
fi

## Update groups memberships
echo_title "Update groups memberships"
if [[ "${INSTALLATION_TYPE}" =~ ^central|poller$ ]] ; then
    add_user_to_group "$ENGINE_USER" "$CENTREON_GROUP"
    add_user_to_group "$CENTREON_USER" "$ENGINE_GROUP"
    add_user_to_group "$ENGINE_USER" "$BROKER_GROUP"
    add_user_to_group "$BROKER_USER" "$CENTREON_GROUP"
    add_user_to_group "$CENTREON_USER" "$GORGONE_GROUP"
    add_user_to_group "$GORGONE_USER" "$CENTREON_GROUP"
    add_user_to_group "$GORGONE_USER" "$BROKER_GROUP"
    add_user_to_group "$GORGONE_USER" "$ENGINE_GROUP"
fi
if [[ "${INSTALLATION_TYPE}" =~ ^central$ ]] ; then
    add_user_to_group "$APACHE_USER" "$CENTREON_GROUP"
    add_user_to_group "$APACHE_USER" "$ENGINE_GROUP"
    add_user_to_group "$APACHE_USER" "$BROKER_GROUP"
    add_user_to_group "$APACHE_USER" "$GORGONE_GROUP"
    add_user_to_group "$GORGONE_USER" "$APACHE_GROUP"
    add_user_to_group "$CENTREON_USER" "$APACHE_GROUP"
fi

if [ ! -z "$ERROR_MESSAGE" ] ; then
    echo_error "\nErrors:"
    echo_error "$ERROR_MESSAGE"
    ERROR_MESSAGE=""
fi

## Configure and restart services
echo_title "Configure and restart services"
if [[ "${INSTALLATION_TYPE}" =~ ^poller$ ]] ; then
    ### Poller hack
    echo "1;" > "$CENTREON_ETC_DIR/conf.pm"
fi
if [[ "${INSTALLATION_TYPE}" =~ ^central|poller$ ]] ; then
    ### Centreontrapd
    enable_service "centreontrapd"
fi
if [[ "${INSTALLATION_TYPE}" =~ ^central$ ]] ; then
    ### Centreon
    enable_service "centreon"

    if [ "$restart_apache" -eq 1 ] || [ "$restart_php_fpm" -eq 1 ] || [ "$restart_mariadb" -eq 1 ] ; then
        reload_daemon
    fi

    ### Apache
    enable_apache_mod "proxy"
    enable_apache_mod "proxy_fcgi"
    enable_apache_mod "setenvif"
    enable_apache_mod "headers"
    enable_apache_mod "rewrite"
    [[ "$DISTRIB" =~ suse ]] && enable_apache_mod "mod_access_compat"
    [ $USE_HTTPS -eq 1 ] && enable_apache_mod "ssl"
    if [ "$restart_apache" -eq 1 ] ; then
        [[ "$DISTRIB" =~ ^debian|ubuntu$ ]] && enable_apache_conf "10-centreon"
        enable_service "$APACHE_SERVICE"
        restart_service "$APACHE_SERVICE"
    fi

    ### PHP FPM
    if [ "$restart_php_fpm" -eq 1 ] ; then
        enable_service "$PHPFPM_SERVICE"
        restart_service "$PHPFPM_SERVICE"
    fi
    
    ### MariaDB
    if [ "$restart_mariadb" -eq 1 ] ; then
        enable_service "$MARIADB_SERVICE"
        restart_service "$MARIADB_SERVICE"
    fi
fi

## Show databse configuration if not installed locally
if [[ "${INSTALLATION_TYPE}" =~ ^central$ ]] && [ "$WITH_DB" -eq 0 ] ; then
    echo_info "Add the following configuration on your database server:"
    echo "$(<$BASE_DIR/install/src/centreon-mysql.cnf)"
fi

if [ ! -z "$ERROR_MESSAGE" ] ; then
    echo_error "\nErrors:"
    echo_error "$ERROR_MESSAGE"
    ERROR_MESSAGE=""
fi

## Purge working directories
purge_centreon_tmp_dir "silent"
server=`hostname -I | cut -d" " -f1`

# End
echo_title "You're done!"
echo_info "You can now connect to the following URL to finalize installation:"
echo_info "\thttp://$server/centreon/"
echo_info ""
echo_info "Take a look at the documentation"
echo_info "https://docs.centreon.com/current/en/installation/web-and-post-installation.html."
echo_info "Thanks for using Centreon!"
echo_info "Follow us on https://github.com/centreon/centreon!"

exit 0
