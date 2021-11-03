#!/bin/sh

CENTREON_MAJOR_VERSION="20.10"
CENTREON_RELEASE_VERSION="$CENTREON_MAJOR_VERSION-2"

function error_and_exit {
  echo -e "$1"
  exit 1
}

function print_step_begin {
  echo -e "\n$1..."
}

function print_step_end {
  if [ -z "$1" ] ; then
    echo -e "\tOK"
  else
    echo -e "\t$1"
  fi
}

#
# ANALYSIS
#

print_step_begin "System analysis"

# Unattended install script only support Red Hat or compatible.
if [ \! -e /etc/redhat-release ] ; then
  error_and_exit "This unattended installation script only supports Red Hat compatible distributions. Please check https://documentation.centreon.com/$CENTREON_MAJOR_VERSION/en/installation/introduction.html for alternative installation methods."
fi
rhrelease=$(rpm -E %{rhel})
case "$rhrelease" in
  '7')
    # CentOS 7 specific part
    RELEASE_RPM_URL="http://yum.centreon.com/standard/$CENTREON_MAJOR_VERSION/el7/stable/noarch/RPMS/centreon-release-$CENTREON_RELEASE_VERSION.el7.centos.noarch.rpm"
    PHP_BIN="/opt/rh/rh-php72/root/bin/php"
    PHP_ETC="/etc/opt/rh/rh-php72/php.d"
    OS_SPEC_SERVICES="rh-php72-php-fpm httpd24-httpd"
    PKG_MGR="yum"
    ;;
  '8')
    # CentOS 8 specific part
    dnf -y install dnf-plugins-core epel-release
    dnf -y update gnutls
    dnf config-manager --set-enabled PowerTools
    RELEASE_RPM_URL="http://yum.centreon.com/standard/$CENTREON_MAJOR_VERSION/el8/stable/noarch/RPMS/centreon-release-$CENTREON_RELEASE_VERSION.el8.noarch.rpm"
    PHP_BIN="/bin/php"
    PHP_ETC="/etc/php.d"
    OS_SPEC_SERVICES="php-fpm httpd"
    PKG_MGR="dnf"
    ;;
  *)
    error_and_exit "This unattended installation script only supports CentOS 7 and CentOS 8. Please check https://documentation.centreon.com/$CENTREON_MAJOR_VERSION/en/installation/introduction.html for alternative installation methods."
    ;;
esac

# systemd check.
command -v systemctl > /dev/null 2>&1
if [ "x$?" '=' x0 ] ; then
  systemctl show > /dev/null 2>&1
  if [ "x$?" '=' x0 ] ; then
    has_systemd=1
  else
    has_systemd=0
  fi
else
  has_systemd=0
fi

print_step_end

#
# SELINUX
#

print_step_begin "SELinux deactivation"
if [ -e /etc/selinux/config ] ; then
  sed -i -e 's/^SELINUX=.*$/SELINUX=disabled/' /etc/selinux/config
fi
command -v selinuxenabled > /dev/null 2>&1
if [ "x$?" '=' x0 ] ; then
  selinuxenabled
  if [ "x$?" '=' x0 ] ; then
    setenforce 0
    if [ "x$?" '!=' x0 ] ; then
      error_and_exit "Could not disable SELinux. You might need to run this script as root."
    fi
    print_step_end
  else
    print_step_end "OK, already disabled"
  fi
else
  print_step_end "OK, not detected"
fi

#
# REPOSITORY
#

print_step_begin "Centreon official repositories installation"
$PKG_MGR -q clean all

if [[ $rhrelease == 7 ]]; then
  rpm -q centos-release-scl > /dev/null 2>&1
  if [ "x$?" '!=' x0 ] ; then
    $PKG_MGR -q install -y centos-release-scl
    if [ "x$?" '!=' x0 ] ; then
      error_and_exit "Could not install Software Collections repository (package centos-release-scl)"
    fi
  fi
fi
rpm -q centreon-release-$CENTREON_MAJOR_VERSION > /dev/null 2>&1
if [ "x$?" '!=' x0 ] ; then
  $PKG_MGR -q install -y --nogpgcheck $RELEASE_RPM_URL
  if [ "x$?" '!=' x0 ] ; then
    error_and_exit "Could not install Centreon repository"
  fi
fi
print_step_end

#
# CENTREON
#

print_step_begin "Centreon installation"
$PKG_MGR -q install -y centreon
if [ "x$?" '!=' x0 ] ; then
  error_and_exit "Could not install Centreon (package centreon)"
fi
print_step_end

#
# PHP
#

print_step_begin "PHP configuration"
timezone=`$PHP_BIN -r '
    $timezoneName = timezone_name_from_abbr(trim(shell_exec("date \"+%Z\"")));

    if (preg_match("/Time zone: (\S+)/", shell_exec("timedatectl"), $matches)) {
        $timezoneName = $matches[1];
    }

    if (date_default_timezone_set($timezoneName) === false) {
      $timezoneName = "UTC";
    }

    echo $timezoneName;
' 2> /dev/null`
echo "date.timezone = $timezone" >> $PHP_ETC/10-centreon.ini
print_step_end "OK, timezone set to $timezone"

#
# FIREWALL
#

print_step_begin "Firewall configuration"
command -v firewall-cmd > /dev/null 2>&1
if [ "x$?" '=' x0 ] ; then
  firewall-cmd --state > /dev/null 2>&1
  if [ "x$?" '=' x0 ] ; then
    for svc in http snmp snmptrap ; do
      firewall-cmd --zone=public --add-service=$svc --permanent > /dev/null 2>&1
      if [ "x$?" '!=' x0 ] ; then
        error_and_exit "Could not configure firewall. You might need to run this script as root."
      fi
    done
    firewall-cmd --reload
    print_step_end
  else
    print_step_end "OK, not active"
  fi
else
  print_step_end "OK, not detected"
fi

#
# SERVICES
#

print_step_begin "Services configuration"
if [ "x$has_systemd" '=' x1 ] ; then
  systemctl enable mariadb $OS_SPEC_SERVICES snmpd snmptrapd gorgoned centreontrapd cbd centengine centreon
  systemctl restart mariadb $OS_SPEC_SERVICES snmpd snmptrapd
  print_step_end
else
  print_step_end "OK, systemd not detected, skipping"
fi

#
# SUMMARY
#

echo
echo "Centreon was successfully installed !"
echo
echo "Log in to Centreon web interface via the URL: http://[SERVER_IP]/centreon"
echo "Follow the steps described in Centreon documentation: https://documentation.centreon.com/$CENTREON_MAJOR_VERSION/en/installation/web-and-post-installation.html"
