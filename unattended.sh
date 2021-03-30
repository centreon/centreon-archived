#!/bin/sh

### Define all supported constants
OPTIONS=":t:v:r:l:"
declare -A SUPPORTED_LOG_LEVEL=([DEBUG]=0 [INFO]=1 [WARN]=2 [ERROR]=3)
declare -A SUPPORTED_TOPOLOGY=([central]=1 [poller]=1)
declare -A SUPPORTED_VERSION=([21.04]=1)
declare -A SUPPORTED_REPOSITORY=([testing]=1 [unstable]=1 [stable]=1)
default_timeout_in_sec=5
script_short_name="$(basename $0)"
###

#Define default values
topology=${ENV_CENTREON_TOPOLOGY:-"central"}   #Default topology to be installed
version=${ENV_CENTREON_VERSION:-"21.04"}       #Default version to be installed
repo=${ENV_CENTREON_REPO:-"stable"}            #Default repository to used
operation=${ENV_CENTREON_OPERATION:-"install"} #Default operation to be executed
runtime_log_level=${ENV_LOG_LEVEL:-"INFO"}     #Default log level to be used

#Generate random MariaDB root password
mariadb_root_password=$(
	date +%s | sha256sum | base64 | head -c 32
	echo
)

##FIXME - to be set dynmically & and support other versions
CENTREON_MAJOR_VERSION=$version
CENTREON_RELEASE_VERSION="$CENTREON_MAJOR_VERSION-2"

#Variables dynamically set
detected_os_release=
detected_os_version=

# Variables will be defined later according to the target system OS
BASE_PACKAGES=
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
	echo " $script_short_name [install|upgrade (default: install)] [-t <central|poller> (default: central)] [-v <20.10> (default: 20.10)] [-r <stable|testing|unstable> (default: stable)] [-l <DEBUG|INFO|WARN|ERROR>"
	echo
	echo Example:
	echo " $script_short_name == install the $version of $topology from the repository $repo"
	echo
	echo " $script_short_name install poller == install the $version of poller from the repository $repo"
	echo
	echo " $script_short_name upgrade -t central -r unstable == upgrade the central to the $version from the unstable repository"
	echo
	echo " $script_short_name upgrade --type central -v 20.10 == upgrade the central to the 20.10 from the stable repository"
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

	# get the log message
	log_message="${2}"

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

			[[ ! ${SUPPORTED_REPOSITORY[$requested_repo]} ]] &&
				log "ERROR" "Unsupported repository : $requested_repo" &&
				usage
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
		log "WARN" "No topology provided : default value '$topology' will be used"
	else
		topology=$requested_topology
	fi

	if [ -z "${requested_version}" ]; then
		log "WARN" "No version provided : default value '$version' will be used"
	else
		version=$requested_version
	fi

	if [ -z "${requested_repo}" ]; then
		log "WARN" "No repository provided : default value '$repo' will be used"
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

#========= begin of function print_step_begin()
# display the starting message of a step
#
function print_step_begin() {
	log "INFO" "$1..."
}
#========= end of function print_step_begin()

#========= begin of function print_step_end()
# display the result of a step
#
function print_step_end() {
	if [ -z "$1" ]; then
		log "INFO" "\tOK"
	else
		log "ERROR" "\t$1"
	fi
}
#========= end of function print_step_end()

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
		CENTREON_REPO+="centreon-$_repo*"
		if ! [ "$_repo" == "${array_repos[@]:(-1)}" ]; then
			CENTREON_REPO+=","
		fi
	done

	log "INFO" "Following Centreon repo will be used [ $CENTREON_REPO ]"

}
#========= end of function set_centreon_repos()

#========= begin of function set_required_prerequisite()
# check if the target OS is compatible with Red Hat and the version is 7 or 8
# then set the required environment variables accordingly
=======
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
		RELEASE_RPM_URL="http://yum.centreon.com/standard/$CENTREON_MAJOR_VERSION/el7/stable/noarch/RPMS/centreon-release-$CENTREON_RELEASE_VERSION.el7.centos.noarch.rpm"
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

		RELEASE_RPM_URL="http://yum.centreon.com/standard/$CENTREON_MAJOR_VERSION/el8/stable/noarch/RPMS/centreon-release-$CENTREON_RELEASE_VERSION.el8.noarch.rpm"
		PHP_BIN="/bin/php"
		PHP_ETC="/etc/php.d"
		OS_SPEC_SERVICES="php-fpm httpd"
		PKG_MGR="dnf"

		case "$detected_os_release" in

		redhat-release*)
			BASE_PACKAGES=(dnf-plugins-core epel-release)
			subscription-manager repos --enable codeready-builder-for-rhel-8-x86_64-rpms
			;;

		centos-release* | centos-linux-release*)
			BASE_PACKAGES=(dnf-plugins-core epel-release)
			$PKG_MGR config-manager --set-enabled powertools
			;;

		oraclelinux-release* | enterprise-release*)
			BASE_PACKAGES=(dnf-plugins-core oracle-epel-release-el8)
			$PKG_MGR config-manager --set-enabled ol8_codeready_builder

			;;
		esac

		#FIXME check the result
		#FIXE install PHP 7.3
		$PKG_MGR module install php:7.3 -y
		$PKG_MGR module enable php:7.3 -y
		$PKG_MGR -y install ${BASE_PACKAGES[@]}
		$PKG_MGR -y update gnutls

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
	if [ "$running_process" = "systemd" ]; then
		has_systemd=1
		log "INFO" "Systemd is running"
	else
		has_systemd=0
		log "WARN" "Systemd is not running"
	fi
}
#========= end of function is_systemd_present()

#========= begin of function disable_selinux()
# disable SELinux
#
function disable_selinux() {

	print_step_begin "SELinux deactivation"

	if [ -e /etc/selinux/config ]; then
		sed -i -e 's/^SELINUX=.*$/SELINUX=disabled/' /etc/selinux/config
	fi
	command -v selinuxenabled >/dev/null 2>&1

	if [ "x$?" '=' x0 ]; then
		selinuxenabled
		if [ "x$?" '=' x0 ]; then
			setenforce 0
			if [ "x$?" '!=' x0 ]; then
				error_and_exit "Could not disable SELinux. You might need to run this script as root."
			fi
			print_step_end
		else
			log "WARN" "SELinux was already disabled"
			print_step_end
		fi
	else
		log "WARN" "SELinux was not detected"
		print_step_end
	fi
}
#========= end of function disable_selinux()

#========= begin of function secure_mariadb_setup()
# apply some secure requests
#
function secure_mariadb_setup() {

	print_step_begin "Secure MariaDB setup"
	log "WARN" "We are applying some requests that will enhance your MariaDB setup security"
	log "WARN" "Please consult the official documentation https://mariadb.com/kb/en/mysql_secure_installation/ for more details"
	log "WARN" "Random generated password for user root is [ $mariadb_root_password ]"
	log "WARN" "You can use mysqladmin in order to set a new password for user root"

	systemctl restart mariadb

	mysql -u root <<-EOF
		UPDATE mysql.global_priv SET priv=json_set(priv, '$.plugin', 'mysql_native_password', '$.authentication_string', PASSWORD('$mariadb_root_password')) WHERE User='root';
		DELETE FROM mysql.global_priv WHERE User='root' AND Host NOT IN ('localhost', '127.0.0.1', '::1');
		DELETE FROM mysql.global_priv WHERE User='';
		DROP DATABASE IF EXISTS test;
		DELETE FROM mysql.db WHERE Db='test' OR Db='test\\_%';
		FLUSH PRIVILEGES;
	EOF

	if [ "x$?" '!=' x0 ]; then
		error_and_exit "Could not apply the requests"
	fi

	systemctl restart mariadb

	print_step_end
}
#========= end of function secure_mariadb_setup()

#========= begin of function install_centreon_repo()
# install the centos-release-scl under CentOS7
# then install Centreon official repositories
#
function install_centreon_repo() {

	print_step_begin "Centreon official repositories installation"
	$PKG_MGR -q clean all

	rpm -q centreon-release-$CENTREON_MAJOR_VERSION >/dev/null 2>&1
	if [ "x$?" '!=' x0 ]; then
		$PKG_MGR -q install -y $RELEASE_RPM_URL ##FIXME - add key for secure mode
		if [ "x$?" '!=' x0 ]; then
			error_and_exit "Could not install Centreon repository"
		fi
	fi
	print_step_end
}
#========= end of function install_centreon_repo()

#========= begin of function update_firewall_config()
# add firewall configuration for newly added services
#
function update_firewall_config() {

	print_step_begin "Firewall configuration"
	command -v firewall-cmd >/dev/null 2>&1

	if [ "x$?" '=' x0 ]; then
		firewall-cmd --state >/dev/null 2>&1
		if [ "x$?" '=' x0 ]; then
			for svc in http snmp snmptrap; do
				firewall-cmd --zone=public --add-service=$svc --permanent >/dev/null 2>&1
				if [ "x$?" '!=' x0 ]; then
					error_and_exit "Could not configure firewall. You might need to run this script as root."
				fi
			done
			firewall-cmd --reload
			print_step_end
		else
			log "WARN" "Firewall was not active"
			print_step_end
		fi
	else
		log "WARN" "Firewall was not detected"
		print_step_end
	fi
}
#========= end of function update_firewall_config()

#========= begin of function enable_new_services()
# enable newly added services to make them active after system reboot
#
function enable_new_services() {

	print_step_begin "Services configuration"
	if [ "x$has_systemd" '=' x1 ]; then
		case $topology in

		central)
			systemctl enable mariadb $OS_SPEC_SERVICES snmpd snmptrapd gorgoned centreontrapd cbd centengine centreon
			systemctl restart mariadb $OS_SPEC_SERVICES snmpd snmptrapd
			;;

		poller)
			systemctl enable centreon centengine centreontrapd snmptrapd
			systemctl start centreontrapd snmptrapd
			;;
		esac
		print_step_end
	else
		log "WARN" "Systemd not detected, skipping"
		print_step_end
	fi
}
#========= end of function enable_new_services()

#========= begin of function setup_before_installation()
# execute some tasks before installing Centreon
# - disable SELinux
# - install Centreon official repositories
function setup_before_installation() {

	# FIXME - make it optional for secure mode
	disable_selinux

	install_centreon_repo
}
#========= end of function setup_before_installation()

#========= begin of function install_central()
# install the Centreon Central
#
function install_central() {

	print_step_begin "Centreon $topology installation from ${CENTREON_REPO}"

	#FIXME : repo testing enabled for master
	$PKG_MGR -q clean all --enablerepo="*" && $PKG_MGR -q install -y centreon --enablerepo="$CENTREON_REPO"

	if [ "x$?" '!=' x0 ]; then
		error_and_exit "Could not install Centreon (package centreon)"
	fi
	print_step_end

	#
	# PHP
	#

	print_step_begin "PHP configuration"
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
	log "INFO" "PHP date.timezone set to $timezone"
	print_step_end

	secure_mariadb_setup

}
#========= end of function install_central()

#========= begin of function install_poller()
# install the Centreon Poller
#
function install_poller() {
	print_step_begin "Poller installation from ${CENTREON_REPO}"
	$PKG_MGR -q clean all --enablerepo="*" && $PKG_MGR -q install -y centreon-poller-centreon-engine --enablerepo=$CENTREON_REPO
	if [ "x$?" '!=' x0 ]; then
		error_and_exit "Could not install Centreon (package centreon)"
	fi
	print_step_end
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
	log "WARN" "No provided operation : default value '$operation' will be used"
	#usage
	operation="install"
	parse_subcommand_options "$@"
	;;

esac

## Display all configured parameters
log "INFO" "Start to execute operation [$operation] with following configuration parameters:"
log "INFO" " topology   : \t$topology"
log "INFO" " version    : \t$version"
log "INFO" " repository : \t$repo"

log "WARN" "It will start in '$default_timeout_in_sec' seconds. If you don't want to way, press any key to continue or Ctrl-C to exit"
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
		install_central
		CENTREON_DOC_URL="https://documentation.centreon.com/$CENTREON_MAJOR_VERSION/en/installation/web-and-post-installation.html"
		;;

	poller)
		install_poller
		CENTREON_DOC_URL=" https://documentation.centreon.com/$CENTREON_MAJOR_VERSION/en/monitoring/monitoring-servers/add-a-poller-to-configuration.html"
		;;
	esac

	update_after_installation

	log "INFO" "Centreon $topology successfully installed !"
	log "INFO" "Log in to Centreon web interface via the URL: http://[SERVER_IP]/centreon"
	log "INFO" "Follow the steps described in Centreon documentation: $CENTREON_DOC_URL"
	;;

upgrade)
	error_and_exit "Upgrade operation is not supported yet" ##FIXME
	;;
esac

exit 0
