#!/bin/bash

# Copyright 2005-2021 Centreon
# Centreon is developed by : Julien Mathis and Romain Le Merlus under
# GPL Licence 2.0.
#
# This program is free software; you can redistribute it and/or modify it under
# the terms of the GNU General Public License as published by the Free Software
# Foundation ; either version 2 of the License.
#
# This program is distributed in the hope that it will be useful, but WITHOUT ANY
# WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
# PARTICULAR PURPOSE. See the GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License along with
# this program; if not, see <http://www.gnu.org/licenses>.
#
# Linking this program statically or dynamically with other modules is making a
# combined work based on this program. Thus, the terms and conditions of the GNU
# General Public License cover the whole combination.
#
# As a special exception, the copyright holders of this program give Centreon
# permission to link this program with independent modules to produce an executable,
# regardless of the license terms of these independent modules, and to copy and
# distribute the resulting executable under terms of Centreon choice, provided that
# Centreon also meet, for each linked independent module, the terms  and conditions
# of the license of that module. An independent module is a module which is not
# derived from this program. If you modify this program, you may extend this
# exception to your version of the program, but you are not obliged to do so. If you
# do not wish to do so, delete this exception statement from your version.
#
# For more information : contact@centreon.com

function usage() {
    cat <<EOF
This script aims to upgrade the php library from 7.2 to 7.3. (Centos 7 only)
This script will:
    - Install remi repository
    - Install dependencies needed for the upgrade
    - Copy existing files from the php 7.2 to php 7.4 configuration directory
    - Setup the system to run by default the php 7.4 version
Usage: ./${0##*/}
Parameters:
    -h|--help Print this help
EOF
    exit 0
}

# Log errors
function error_and_exit() {
    echo "[ERROR] $*"
    exit 1
}

# Log information
function info() {
    echo "[INFO] $*"
}

function check_version() {
    distrib=$(cat /etc/os-release | grep -E "^ID=")
    distrib="${distrib##*=}"
    distrib="${distrib//\"/}"
    version=$(cat /etc/os-release | grep -E "^VERSION_ID=")
    version="${version##*=}"
    version="${version//\"/}"

    [[ $distrib == "centos" && $version == "7" ]] && return 0
    return 1
}

if [ \! -e /etc/redhat-release ] ; then
  error_and_exit "This script can only be executed on Centos 7"
fi
rhrelease=$(rpm -E %{rhel})

## Main
case $* in
    -h|--help)
        usage
        ;;
    *)
esac

case "$rhrelease" in
  '7')
    # CentOS 7 specific part
    yum install -q -y \
        https://dl.fedoraproject.org/pub/epel/epel-release-latest-7.noarch.rpm \
        https://rpms.remirepo.net/enterprise/remi-release-7.rpm \
        yum-utils
    yum-config-manager --enable remi-php74
    ;;
  '8')
    # CentOS 8 specific part
    dnf install -q -y \
        https://dl.fedoraproject.org/pub/epel/epel-release-latest-8.noarch.rpm \
        https://rpms.remirepo.net/enterprise/remi-release-8.rpm
    dnf module reset php
    dnf module install php:remi-7.4
    ;;
  *)
    error_and_exit "This unattended installation script only supports CentOS 7 and CentOS 8."
    ;;
esac

info "Installing dependencies for PHP 7.4"
yum install -q -y \
    php74 \
    php74-php-cli \
    php74-php-pdo \
    php74-php-mysqlnd \
    php74-php-gd \
    php74-php-xml \
    php74-php-mbstring \
    php74-php-ldap \
    php74-php-snmp \
    php74-php-intl \
    php74-php-fpm \
    php74-php-curl \
    php74-php-zip \
    php74-php-pear \
    php74-ioncube-loader \
    php74-php-pecl-gnupg

info "Copying php-fpm configuration from 7.2 to 7.4"
\cp /etc/opt/rh/rh-php72/php-fpm.d/*.conf /etc/opt/remi/php74/php-fpm.d/

info "Copying php configuration from 7.2 to 7.4"
cp /etc/opt/rh/rh-php72/php.d/50-centreon.ini /etc/opt/remi/php74/php.d/50-centreon.ini

info "Configuring system to use new PHP 7.4 binary"
mv /opt/rh/rh-php72/root/usr/bin/php{,.backup}
ln -s /opt/remi/php74/root/usr/bin/php /opt/rh/rh-php72/root/usr/bin/php
systemctl -q stop rh-php72-php-fpm
systemctl -q disable rh-php72-php-fpm
systemctl -q start php74-php-fpm
systemctl -q enable php74-php-fpm

info "Upgrade finished"
