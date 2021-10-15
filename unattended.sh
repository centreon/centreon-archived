#!/bin/sh

### Define all supported constants
OPTIONS=":t:v:r:l:"
declare -A SUPPORTED_LOG_LEVEL=([DEBUG]=0 [INFO]=1 [WARN]=2 [ERROR]=3)
declare -A SUPPORTED_TOPOLOGY=([central]=1 [poller]=1)
declare -A SUPPORTED_VERSION=([21.04]=1)
declare -A SUPPORTED_REPOSITORY=([testing]=1 [unstable]=1 [stable]=1)
default_timeout_in_sec=5
script_short_name="$(basename $0)"
default_ip=$(hostname -I | awk '{print $1}')
###

#Define default values

passwords_file=/etc/centreon/generated.tobesecured         #File where the generated passwords will be temporaly saved
tmp_passwords_file=$(mktemp /tmp/generated.XXXXXXXXXXXXXX) #Random tmp file as the /etc/centreon does not exist yet

topology=${ENV_CENTREON_TOPOLOGY:-"central"}    #Default topology to be installed
version=${ENV_CENTREON_VERSION:-"21.04"}        #Default version to be installed
repo=${ENV_CENTREON_REPO:-"stable"}             #Default repository to used
operation=${ENV_CENTREON_OPERATION:-"install"}  #Default operation to be executed
runtime_log_level=${ENV_LOG_LEVEL:-"INFO"}      #Default log level to be used
selinux_mode=${ENV_SELINUX_MODE:-"permissive"}  #Default SELinux mode to be used
wizard_autoplay=${ENV_WIZARD_AUTOPLAY:-"false"} #Default the install wizard is not run auto
central_ip=${ENV_CENTRAL_IP:-$default_ip}       #Default central ip is the first of hostname -I

function genpasswd() {
	local _pwd
	_pwd=$(date +%s%N | sha256sum | base64 | head -c 32)

	echo "Random password generated for user [$1] is [$_pwd]" >>$tmp_passwords_file

	if [ $? -ne 0 ]; then
		echo "ERROR : Cannot save the random password to [$tmp_passwords_file]"
		exit 1
	fi

	#return the generated password
	echo $_pwd

}

# Set MariaDB password from ENV or random password if not defined
mariadb_root_password=${ENV_MARIADB_ROOT_PASSWD:-"$(genpasswd "MariaDB user : root")"}

if [ "$wizard_autoplay" == "true" ]; then
	# Set from ENV or random MariaDB centreon password
	mariadb_centreon_password=${ENV_MARIADB_CENTREON_PASSWD:-"$(genpasswd "MariaDB user : centreon")"}
	# Set from ENV or random Centreon admin password
	centreon_admin_password=${ENV_CENTREON_ADMIN_PASSWD:-"$(genpasswd "Centreon user : admin")"}
	# Set from ENV or Administrator first name
	centreon_admin_firstname=${ENV_CENTREON_ADMIN_FIRSTNAME:-"John"}
	# Set from ENV or Administrator last name
	centreon_admin_lastname=${ENV_CENTREON_ADMIN_LASTNAME:-"Doe"}
	# Set from ENV or Administrator e-mail
	centreon_admin_email=${ENV_CENTREON_ADMIN_EMAIL:-"admin@admin.tld"}
fi

CENTREON_MAJOR_VERSION=$version
centreon_release_version="$CENTREON_MAJOR_VERSION-${CENTREON_RELEASE_MINOR_VERSION:-"5"}"

#Variables dynamically set
detected_os_release=
detected_os_version=

# Variables will be defined later according to the target system OS
BASE_PACKAGES=
CENTREON_SELINUX_PACKAGES=
RELEASE_RPM_URL=
PHP_BIN=
PHP_ETC=
OS_SPEC_SERVICES=
PKG_MGR=
has_systemd=
CENTREON_REPO=
CENTREON_DOC_URL=

#########################################################
############### ALL INTERNAL FUNCTIONS ##################

#========= begin of function usage()
# display help usage
#
function usage() {

	echo
	echo "Usage :"
	echo
	echo " $script_short_name [install|upgrade (default: install)] [-t <central|poller> (default: central)] [-v <21.04> (default: 21.04)] [-r <stable|testing|unstable> (default: stable)] [-l <DEBUG|INFO|WARN|ERROR>"
	echo
	echo Example:
	echo
	echo " $script_short_name == install the $version of $topology from the repository $repo"
	echo
	echo " $script_short_name install -r unstable,testing == install the central to the $version from the unstable & testing repository"
	echo
	exit 1
}
#======== end of function usage()

#========= begin of function log()
# print out the message according to the level
# with timestamp
#
# usage:
# log "$LOG_LEVEL" "$message" ($LOG_LEVEL = DEBUG|INFO|WARN|ERROR)
#
# example:
# log "DEBUG" "This is a DEBUG_LOG_LEVEL message"
# log "INFO" "This is a INFO_LOG_LEVEL message"
#
function log() {

	TIMESTAMP=$(date --rfc-3339=seconds)

	if [[ -z "${1}" || -z "${2}" ]]; then
		echo "${TIMESTAMP} - ERROR : Missing argument"
		echo "${TIMESTAMP} - ERROR : Usage log \"INFO\" \"Message log\" "
		exit 1
	fi

	# get the message log level
	log_message_level="${1}"

	# shift once to get the log message (string or array)
	shift
	
	# get the log message (full log message)
	log_message="${@}"

	# check if the log_message_level is greater than the runtime_log_level
	[[ ${SUPPORTED_LOG_LEVEL[$log_message_level]} ]] || return 1

	((${SUPPORTED_LOG_LEVEL[$log_message_level]} < ${SUPPORTED_LOG_LEVEL[$runtime_log_level]})) && return 2

	echo -e "${TIMESTAMP} - $log_message_level - $log_message"

}
#======== end of function log()

#========= begin of function parse_subcommand_options()
# parse the provided arguments and check values
# the script will display usage (and aborted) for any
# unsupported argument/option (which are defined in constants)
#
function parse_subcommand_options() {
	local requested_topology=""
	local requested_version=""
	local requested_repo=""
	local OPTIND
	OPTIND=2
	while getopts $OPTIONS opt; do
		case ${opt} in
		t)
			requested_topology=$OPTARG
			log "INFO" "Requested topology   : '$requested_topology'"

			[[ ! ${SUPPORTED_TOPOLOGY[$requested_topology]} ]] &&
				log "ERROR" "Unsupported topology : $requested_topology" &&
				usage
			;;

		v)
			requested_version=$OPTARG
			log "INFO" "Requested version    : '$requested_version'"

			[[ ! ${SUPPORTED_VERSION[$requested_version]} ]] &&
				log "ERROR" "Unsupported version : $requested_version" &&
				usage
			;;

		r)
			requested_repo=$OPTARG
			log "INFO" "Requested repository : '$requested_repo'"

			set_centreon_repos $requested_repo
			;;

		l)
			log_level=$OPTARG
			if [ ! ${SUPPORTED_LOG_LEVEL[$log_level]} ]; then
				log "ERROR" "Unsupported and ignored log level : $log_level"
			else
				runtime_log_level=$log_level
			fi
			log "INFO" "Runtime log level set : $runtime_log_level"
			;;

		\?)
			log "ERROR" "Invalid option: -"$OPTARG""
			usage
			exit 1
			;;

		:)
			log "ERROR" "Option -"$OPTARG" requires an argument."
			usage
			exit 1
			;;
		esac
	done
	shift $((OPTIND - 1))

	## check the configuration parameters
	if [ -z "${requested_topology}" ]; then
		log "WARN" "No topology provided : default value [$topology] will be used"
	else
		topology=$requested_topology
	fi

	if [ -z "${requested_version}" ]; then
		log "WARN" "No version provided : default value [$version] will be used"
	else
		version=$requested_version
	fi

	if [ -z "${requested_repo}" ]; then
		log "WARN" "No repository provided : default value [$repo] will be used"
	else
		repo=$requested_repo
	fi
}
#======== end of function parse_subcommand_options()

#========= begin of function error_and_exit()
# display the ERROR log message then exit the script
function error_and_exit() {
	log "ERROR" "$1"
	exit 1
}
#========= end of function error_and_exit()

#========= begin of function pause()
# add pause prompt message ($1) for ($2) seconds
#
function pause() {
	local timeout=$default_timeout_in_sec
	if [ -n $2 ]; then
		timeout=$2
	fi
	read -t $timeout -s -n 1 -p "${1}"
	echo ""
}
#========= end of function pause()

#========= begin of function get_os_information()
# get the OS release
# if the detected release is not supported the script will be ended
#
function get_os_information() {

	# Unattended install script only support Red Hat or compatible.
	if ! detected_os_release=$(rpm -q --whatprovides /etc/redhat-release); then
		log "ERROR" "Unsupported distribution $detected_os_release detected"
		error_and_exit "This '$script_short_name' script only supports Red Hat compatible distributions. Please check https://documentation.centreon.com/$CENTREON_MAJOR_VERSION/en/installation/introduction.html for alternative installation methods."
	fi

	if [ "$(echo "${detected_os_release}" | wc -l)" -ne 1 ]; then
		error_and_exit "Unable to determine your running OS as there are multiple packages providing redhat-release : $detected_os_release"
	fi

	detected_os_version=$(rpm -q "${detected_os_release}" --qf "%{version}")

	log "INFO" "Your running OS is $detected_os_release (version: ${detected_os_version})"

}
#========= end of function get_os_information()

#========= begin of function set_centreon_repos()
# split the repos from the args (separated by , )
# then concat the string for $CENTREON_REPO
#
function set_centreon_repos() {

	IFS=', ' read -r -a array_repos <<<"$repo"

	CENTREON_REPO=""
	for _repo in "${array_repos[@]}"; do

		[[ ! ${SUPPORTED_REPOSITORY[$_repo]} ]] &&
			log "ERROR" "Unsupported repository : $_repo" &&
			usage

		CENTREON_REPO+="centreon-$_repo*"
		if ! [ "$_repo" == "${array_repos[@]:(-1)}" ]; then
			CENTREON_REPO+=","
		fi
	done

	log "INFO" "Following Centreon repo will be used [$CENTREON_REPO]"

}
#========= end of function set_centreon_repos()

#========= begin of function set_required_prerequisite()
# check if the target OS is compatible with Red Hat and the version is 7 or 8
# then set the required environment variables accordingly
#
function set_required_prerequisite() {

	log "INFO" "Check if the system OS is supported and set the environment variables"

	get_os_information

	case "$detected_os_version" in
	7*)
		log "INFO" "Setting specific part for v7 ($detected_os_version)"

		case "$detected_os_release" in
		centos-release* | centos-linux-release*)
			BASE_PACKAGES=(centos-release-scl)
			;;

		oraclelinux-release* | enterprise-release*)
			BASE_PACKAGES=(oraclelinux-release-el7)
			;;
		esac
        RELEASE_RPM_URL="https://yum.centreon.com/standard/$CENTREON_MAJOR_VERSION/el7/stable/noarch/RPMS/centreon-release-$centreon_release_version.el7.centos.noarch.rpm"
		log "INFO" "Install Centreon from ${RELEASE_RPM_URL}"
		PHP_BIN="/opt/rh/rh-php73/root/bin/php"
		PHP_ETC="/etc/opt/rh/rh-php73/php.d/"
		OS_SPEC_SERVICES="rh-php73-php-fpm httpd24-httpd"
		PKG_MGR="yum"

		set_centreon_repos

		log "INFO" "Installing required base packages"
		if ! $PKG_MGR -y -q install ${BASE_PACKAGES[@]}; then
			error_and_exit "Failed to install required base packages  ${BASE_PACKAGES[@]}"
		fi
		;;

	8*)
		log "INFO" "Setting specific part for v8 ($detected_os_version)"

		RELEASE_RPM_URL="https://yum.centreon.com/standard/$CENTREON_MAJOR_VERSION/el8/stable/noarch/RPMS/centreon-release-$centreon_release_version.el8.noarch.rpm"
		PHP_BIN="/bin/php"
		PHP_ETC="/etc/php.d"
		OS_SPEC_SERVICES="php-fpm httpd"
		PKG_MGR="dnf"

		case "$detected_os_release" in

		redhat-release*)
			BASE_PACKAGES=(dnf-plugins-core epel-release)
			subscription-manager repos --enable codeready-builder-for-rhel-8-x86_64-rpms
			;;

		centos-release* | centos-linux-release* | centos-stream-release*)
			BASE_PACKAGES=(dnf-plugins-core epel-release)
			$PKG_MGR config-manager --set-enabled powertools
			;;

		oraclelinux-release* | enterprise-release*)
			BASE_PACKAGES=(dnf-plugins-core oracle-epel-release-el8)
			$PKG_MGR config-manager --set-enabled ol8_codeready_builder
			;;
		esac

		log "INFO" "Installing PHP 7.3 and enable it"
		$PKG_MGR module install php:7.3 -y -q
		$PKG_MGR module enable php:7.3 -y -q

		log "INFO" "Installing packages ${BASE_PACKAGES[@]}"
		$PKG_MGR -y -q install ${BASE_PACKAGES[@]}

		log "INFO" "Updating package gnutls"
		$PKG_MGR -y -q update gnutls

		set_centreon_repos
		;;

	*)
		error_and_exit "This '$script_short_name' script only supports Red-Hat compatible distribution (v7 and v8). Please check https://documentation.centreon.com/$CENTREON_MAJOR_VERSION/en/installation/introduction.html for alternative installation methods."
		;;
	esac

}
#========= end of function set_required_prerequisite()

#========= begin of function is_systemd_present()
#
function is_systemd_present() {
	# systemd check.
	running_process=$(ps --no-headers -o comm 1)
	if [ "$running_process" == "systemd" ]; then
		has_systemd=1
		log "INFO" "Systemd is running"
	else
		has_systemd=0
		log "WARN" "Systemd is not running"
	fi
}
#========= end of function is_systemd_present()

#========= begin of function set_selinux_config()
# change SELinux config : $1 (permissive | enforcing | disabled)
#
function set_selinux_config() {

	log "INFO" "Change SELinux config to mode [$1]"

	if [ -e /etc/selinux/config ]; then
		log "WARN" "Modifying /etc/selinux/config. You must reboot your machine."

		sed -i "s/^SELINUX=.*\$/SELINUX=$1/" /etc/selinux/config

		if [ $? -ne 0 ]; then
			error_and_exit "Could not change SELinux mode. You might need to run this script as root."
		fi
	else
		log "WARN" "Cannot read /etc/selinux/config. Do nothing"
	fi

}
#========= end of function set_selinux_config()

#========= begin of function set_runtime_selinux_mode ()
# set runtime SELinux mode : $1 (permissive | enforcing)
#
function set_runtime_selinux_mode() {

	log "INFO" "Set runtime SELinux mode to [$1]"

	_current_mode=$(getenforce | tr '[:upper:]' '[:lower:]')

	log "DEBUG" "Current SELinux mode is [$_current_mode]"

	shopt -s nocasematch

	if [ "$_current_mode" == "$1" ]; then
		log "DEBUG" "Current SELinux mode is already set as requested. Nothing to do"
		return
	fi

	_request_mode=0 #Default mode is permissive
	case $1 in
	permissive)
		log "DEBUG" "Change runtime mode to [permissive]"
		_request_mode=0
		;;

	enforcing)
		log "DEBUG" "Change runtime mode to [enforcing]"
		_request_mode=1
		;;
	esac

	setenforce $_request_mode

	if [ $? -eq 2 ]; then
		error_and_exit "Could not change SELinux mode. You might need to run this script as root."
	elif [ $? -eq 1 ]; then
		log "WARN" "Current SELinux mode is disabled. Nothing to do"
	fi

}

#========= end of function set_runtime_selinux_mode()

#========= begin of function secure_mariadb_setup()
# apply some secure requests
#
function secure_mariadb_setup() {

	log "INFO" "Secure MariaDB setup..."
	log "WARN" "We are applying some requests that will enhance your MariaDB setup security"
	log "WARN" "Please consult the official documentation https://mariadb.com/kb/en/mysql_secure_installation/ for more details"
	log "WARN" "You can use mysqladmin in order to set a new password for user root"

	log "INFO" "Restarting MariaDB service first"
	systemctl restart mariadb

	log "INFO" "Executing SQL requests"
	mysql -u root <<-EOF
		UPDATE mysql.global_priv SET priv=json_set(priv, '$.plugin', 'mysql_native_password', '$.authentication_string', PASSWORD('$mariadb_root_password')) WHERE User='root';
		DELETE FROM mysql.global_priv WHERE User='root' AND Host NOT IN ('localhost', '127.0.0.1', '::1');
		DELETE FROM mysql.global_priv WHERE User='';
		DROP DATABASE IF EXISTS test;
		DELETE FROM mysql.db WHERE Db='test' OR Db='test\\_%';
		FLUSH PRIVILEGES;
	EOF

	if [ $? -ne 0 ]; then
		error_and_exit "Could not apply the requests"
	else
		log "INFO" "Successfully applied the SQL requests for enhancing your MariaDB"
	fi

}
#========= end of function secure_mariadb_setup()

#========= begin of function install_centreon_repo()
# install the centos-release-scl under CentOS7
# then install Centreon official repositories
#
function install_centreon_repo() {

	log "INFO" "Centreon official repositories installation..."
	$PKG_MGR -q clean all

	rpm -q centreon-release-$CENTREON_MAJOR_VERSION >/dev/null 2>&1
	if [ $? -ne 0 ]; then
		$PKG_MGR -q install -y $RELEASE_RPM_URL
		if [ $? -ne 0 ]; then
			error_and_exit "Could not install Centreon repository"
		fi
	else
		log "INFO" "Centreon repository seems to be already installed"
	fi
}
#========= end of function install_centreon_repo()

#========= begin of function update_firewall_config()
# add firewall configuration for newly added services
#
function update_firewall_config() {

	log "INFO" "Update firewall configuration..."
	command -v firewall-cmd >/dev/null 2>&1

	if [ $? -eq 0 ]; then
		firewall-cmd --state >/dev/null 2>&1
		if [ $? -eq 0 ]; then
			for svc in http snmp snmptrap; do
				firewall-cmd --zone=public --add-service=$svc --permanent >/dev/null 2>&1
				if [ $? -ne 0 ]; then
					error_and_exit "Could not configure firewall. You might need to run this script as root."
				fi
			done
			for port in "5556/tcp" "5669/tcp"; do
				firewall-cmd --zone=public --add-port=$port --permanent >/dev/null 2>&1
				if [ $? -ne 0 ]; then
					error_and_exit "Could not configure firewall. You might need to run this script as root."
				fi
			done
			log "INFO" "Reloading firewall rules"
			firewall-cmd --reload
		else
			log "WARN" "Firewall was not active"
		fi
	else
		log "WARN" "Firewall was not detected"
	fi
}
#========= end of function update_firewall_config()

#========= begin of function enable_new_services()
# enable newly added services to make them active after system reboot
#
function enable_new_services() {

	log "INFO" "Enable and restart services ..."
	if [ $has_systemd -eq 1 ]; then
		case $topology in

		central)
			log "DEBUG" "On central..."
			systemctl enable mariadb $OS_SPEC_SERVICES snmpd snmptrapd gorgoned centreontrapd cbd centengine centreon
			systemctl restart mariadb $OS_SPEC_SERVICES snmpd snmptrapd
			;;

		poller)
			log "DEBUG" "On poller..."
			systemctl enable centreon centengine centreontrapd snmptrapd
			systemctl start centreontrapd snmptrapd
			;;
		esac
	else
		log "WARN" "Systemd not detected, skipping"
	fi
}
#========= end of function enable_new_services()

#========= begin of function setup_before_installation()
# execute some tasks before installing Centreon
# - disable SELinux
# - install Centreon official repositories
function setup_before_installation() {

	set_runtime_selinux_mode "disabled"

	install_centreon_repo
}
#========= end of function setup_before_installation()

#========= begin of function install_wizard_post()
# execute a post request of the install wizard
# - session coocky
# - php command
# - request body
function install_wizard_post() {
	log "INFO" " wizard install step ${2} response ->  $(curl -s -o /dev/null -w "%{http_code}" "http://${central_ip}/centreon/install/steps/process/${2}" \
		-H 'Content-Type: application/x-www-form-urlencoded; charset=UTF-8' \
		-H "Cookie: ${1}" --data "${3}")"
}
#========= end of function install_wizard_post()

#========= begin of function play_install_wizard()
function play_install_wizard() {
	log "INFO" "Playing install wizard"

	sessionID=$(curl -s -v "http://${central_ip}/centreon/install/install.php" 2>&1 | grep Set-Cookie | awk '{print $3}')
	curl -s "http://${central_ip}/centreon/install/steps/step.php?action=stepContent" -H "Cookie: ${sessionID}" >/dev/null
	install_wizard_post ${sessionID} "process_step3.php" 'install_dir_engine=%2Fusr%2Fshare%2Fcentreon-engine&centreon_engine_stats_binary=%2Fusr%2Fsbin%2Fcentenginestats&monitoring_var_lib=%2Fvar%2Flib%2Fcentreon-engine&centreon_engine_connectors=%2Fusr%2Flib64%2Fcentreon-connector&centreon_engine_lib=%2Fusr%2Flib64%2Fcentreon-engine&centreonplugins=%2Fusr%2Flib%2Fcentreon%2Fplugins%2F'
	install_wizard_post ${sessionID} "process_step4.php" 'centreonbroker_etc=%2Fetc%2Fcentreon-broker&centreonbroker_cbmod=%2Fusr%2Flib64%2Fnagios%2Fcbmod.so&centreonbroker_log=%2Fvar%2Flog%2Fcentreon-broker&centreonbroker_varlib=%2Fvar%2Flib%2Fcentreon-broker&centreonbroker_lib=%2Fusr%2Fshare%2Fcentreon%2Flib%2Fcentreon-broker'
	install_wizard_post ${sessionID} "process_step5.php" "admin_password=${centreon_admin_password}&confirm_password=${centreon_admin_password}&firstname=${centreon_admin_firstname}&lastname=${centreon_admin_lastname}&email=${centreon_admin_email}"
	install_wizard_post ${sessionID} "process_step6.php" "address=&port=&root_user=root&root_password=${mariadb_root_password}&db_configuration=centreon&db_storage=centreon_storage&db_user=centreon&db_password=${mariadb_centreon_password}&db_password_confirm=${mariadb_centreon_password}"
	install_wizard_post ${sessionID} "configFileSetup.php"
	install_wizard_post ${sessionID} "installConfigurationDb.php"
	install_wizard_post ${sessionID} "installStorageDb.php"
	install_wizard_post ${sessionID} "createDbUser.php"
	install_wizard_post ${sessionID} "insertBaseConf.php"
	install_wizard_post ${sessionID} "partitionTables.php"
	install_wizard_post ${sessionID} "generationCache.php"
	install_wizard_post ${sessionID} "process_step8.php" 'modules%5B%5D=centreon-license-manager&modules%5B%5D=centreon-pp-manager&modules%5B%5D=centreon-autodiscovery-server&widgets%5B%5D=engine-status&widgets%5B%5D=global-health&widgets%5B%5D=graph-monitoring&widgets%5B%5D=grid-map&widgets%5B%5D=host-monitoring&widgets%5B%5D=hostgroup-monitoring&widgets%5B%5D=httploader&widgets%5B%5D=live-top10-cpu-usage&widgets%5B%5D=live-top10-memory-usage&widgets%5B%5D=service-monitoring&widgets%5B%5D=servicegroup-monitoring&widgets%5B%5D=tactical-overview'
	install_wizard_post ${sessionID} "process_step9.php" 'send_statistics=1'

}
#========= end of function play_install_wizard()

#========= begin of function install_central()
# install the Centreon Central
#
function install_central() {

	log "INFO" "Centreon [$topology] installation from [${CENTREON_REPO}]"

	# install core Centreon packages from enabled repo
	$PKG_MGR -q clean all --enablerepo="*" && $PKG_MGR -q install -y centreon --enablerepo="$CENTREON_REPO"

	if [ $? -ne 0 ]; then
		error_and_exit "Could not install Centreon (package centreon)"
	fi

	#
	# PHP
	#

	log "INFO" "PHP configuration"
	timezone=$($PHP_BIN -r '
		$timezoneName = timezone_name_from_abbr(trim(shell_exec("date \"+%Z\"")));
		if (preg_match("/Time zone: (\S+)/", shell_exec("timedatectl"), $matches)) {
			$timezoneName = $matches[1];
		}
		if (date_default_timezone_set($timezoneName) === false) {
			$timezoneName = "UTC";
		}
		echo $timezoneName;
	' 2>/dev/null)
	echo "date.timezone = $timezone" >$PHP_ETC/50-centreon.ini

	log "INFO" "PHP date.timezone set to [$timezone]"

	secure_mariadb_setup
}
#========= end of function install_central()

#========= begin of function install_poller()
# install the Centreon Poller
#
function install_poller() {
	log "INFO" "Poller installation from ${CENTREON_REPO}"
	$PKG_MGR -q clean all --enablerepo="*" && $PKG_MGR -q install -y centreon-poller-centreon-engine --enablerepo=$CENTREON_REPO
	if [ $? -ne 0 ]; then
		error_and_exit "Could not install Centreon (package centreon)"
	fi
}
#========= end of function install_poller()

#========= begin of function update_after_installation()
# execute some tasks after having installed Centreon
# - update firewall config
# - enable some newly added services
#
# ## FIXME -- according to the $topology
#
function update_after_installation() {

	update_firewall_config

	enable_new_services

	# install Centreon SELinux packages first (as getenforce is still at 0)
	$PKG_MGR -q install -y ${CENTREON_SELINUX_PACKAGES[@]} --enablerepo="$CENTREON_REPO"
	if [ $? -ne 0 ]; then
		log "ERROR" "Could not install Centreon SELinux packages"
	else
		log "INFO" "Centreon SELinux rules are installed. Please consult the documentation https://docs.centreon.com/$CENTREON_MAJOR_VERSION/en/administration/secure-platform.html for more details."
	fi

	#then change the SELinux mode
	set_runtime_selinux_mode $selinux_mode

	set_selinux_config $selinux_mode
}
#========= end of function update_after_installation()

#####################################################
################ MAIN SCRIPT EXECUTION ##############

## Process the provided arguments in line
case "$1" in

upgrade)
	operation="upgrade"
	parse_subcommand_options "$@"
	;;

install)
	operation="install"
	parse_subcommand_options "$@"
	;;

*)
	log "WARN" "No provided operation : default value [$operation] will be used"
	#usage
	operation="install"
	parse_subcommand_options "$@"
	;;

esac

## Display all configured parameters
log "INFO" "Start to execute operation [$operation] with following configuration parameters:"
log "INFO" " topology   : \t[$topology]"
log "INFO" " version    : \t[$version]"
log "INFO" " repository : \t[$repo]"

log "WARN" "It will start in [$default_timeout_in_sec] seconds. If you don't want to wait, press any key to continue or Ctrl-C to exit"
pause "" $default_timeout_in_sec

##
# Analyze system and set the variables
##
set_required_prerequisite

##
# Check if systemd is present
##
is_systemd_present

## Start to execute
case $operation in

install)
	setup_before_installation
	case $topology in
	central)
		CENTREON_SELINUX_PACKAGES=(centreon-common-selinux centreon-web-selinux centreon-broker-selinux centreon-engine-selinux centreon-gorgoned-selinux centreon-plugins-selinux)
		install_central
		CENTREON_DOC_URL="https://docs.centreon.com/$CENTREON_MAJOR_VERSION/en/installation/web-and-post-installation.html"
		;;

	poller)
		CENTREON_SELINUX_PACKAGES=(centreon-common-selinux centreon-broker-selinux centreon-engine-selinux centreon-gorgoned-selinux centreon-plugins-selinux)
		install_poller
		CENTREON_DOC_URL="https://docs.centreon.com/$CENTREON_MAJOR_VERSION/en/monitoring/monitoring-servers/add-a-poller-to-configuration.html"
		;;
	esac

	update_after_installation

	if [ "$topology" == "central" ] && [ "$wizard_autoplay" == "true" ]; then
		play_install_wizard
		log "INFO" "Log in to Centreon web interface via the URL: http://$central_ip/centreon"
	else
		log "INFO" "Follow the steps described in Centreon documentation: $CENTREON_DOC_URL"
	fi

	log "INFO" "Centreon [$topology] successfully installed !"
	;;

upgrade)
	error_and_exit "Upgrade operation is not supported yet" ##TODO
	;;
esac

## Major change - remind it again (in case of log level is ERROR)
if [ -e $tmp_passwords_file ] && [ "$topology" == "central" ]; then
	# Move the tmp file to the dest file
	mv $tmp_passwords_file $passwords_file
	echo
	echo "****** IMPORTANT ******"
	if [ "$wizard_autoplay" == "true" ]; then
		echo "As you will need passwords for users such as MariaDB [root,centreon] and Centreon [admin], random passwords are generated"
	else
		echo "As you will need password for user MariaDB [root], random password are generated"
	fi
	echo "Passwords are currently saved in [$passwords_file]"
	cat $passwords_file
	echo
	echo "Please save them securely and then delete this file!"
	echo
fi

exit 0