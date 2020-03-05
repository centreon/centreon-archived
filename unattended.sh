#!/bin/sh

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
  error_and_exit "This unattended installation script only supports Red Hat compatible distributions. Please check https://documentation.centreon.com/docs/centreon/en/latest/installation/index.html for alternative installation methods."
fi
rhrelease=`cat /etc/redhat-release`
case "$rhrelease" in
  'CentOS Linux release 7.'*)
    # Good to go.
    ;;
  *)
    error_and_exit "This unattended installation script only supports CentOS 7. Please check https://documentation.centreon.com/docs/centreon/en/latest/installation/index.html for alternative installation methods."
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
yum -q clean all
rpm -q centos-release-scl > /dev/null 2>&1
if [ "x$?" '!=' x0 ] ; then
  yum -q install -y centos-release-scl
  if [ "x$?" '!=' x0 ] ; then
    error_and_exit "Could not install Software Collections repository (package centos-release-scl)"
  fi
fi
rpm -q centreon-release-19.10 > /dev/null 2>&1
if [ "x$?" '!=' x0 ] ; then
  yum -q install -y --nogpgcheck http://yum.centreon.com/standard/19.10/el7/stable/noarch/RPMS/centreon-release-19.10-1.el7.centos.noarch.rpm
  if [ "x$?" '!=' x0 ] ; then
    error_and_exit "Could not install Centreon repository"
  fi
fi
print_step_end

#
# CENTREON
#

print_step_begin "Centreon installation"
yum -q install -y centreon
if [ "x$?" '!=' x0 ] ; then
  error_and_exit "Could not install Centreon (package centreon)"
fi
print_step_end

#
# PHP
#

print_step_begin "PHP configuration"
timezone=`date '+%Z'`
if [ -z "$timezone" ] ; then
  timezone=UTC
fi
echo "date.timezone = $timezone" > /etc/opt/rh/rh-php72/php.d/10-centreon.ini
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
  systemctl enable httpd24-httpd mariadb rh-php72-php-fpm snmpd snmptrapd centcore centreontrapd cbd centengine centreon
  systemctl restart httpd24-httpd mariadb rh-php72-php-fpm snmpd snmptrapd
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
echo "Follow the steps described in Centreon documentation: https://documentation.centreon.com/docs/centreon/en/19.10/installation/from_packages.html#configuration"
