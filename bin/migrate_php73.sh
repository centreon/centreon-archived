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
​
function usage() {
    cat <<EOF
This script aims to upgrade the php library from 7.2 to 7.3. (Centos 7 only)
This script will:
    - Install dependencies needed for the upgrade
    - Copy existing files from the php 7.2 to php 7.3 configuration directory
    - Setup the system to run by default the php 7.3 version
Usage: ./${0##*/}
Parameters:
    -h|--help Print this help
EOF
    exit 0
}
​
# Log errors
function error_and_exit() {
    echo "[ERROR] $*"
    exit 1
}
​
# Log information
function info() {
    echo "[INFO] $*"
}
​
function check_version() {
    distrib=$(cat /etc/os-release | grep -E "^ID=")
    distrib="${distrib##*=}"
    distrib="${distrib//\"/}"
    version=$(cat /etc/os-release | grep -E "^VERSION_ID=")
    version="${version##*=}"
    version="${version//\"/}"
​
    [[ $distrib == "centos" && $version == "7" ]] && return 0
    return 1
}
​
check_version || error_and_exit "This script can only be executed on Centos 7"
​
## Main
case $* in
    -h|--help)
        usage
        ;;
    *)
esac
​
info "Installing dependencies for PHP 7.3"
yum install -q -y \
    rh-php73 \
    rh-php73-php-cli \
    rh-php73-php-pdo \
    rh-php73-php-mysqlnd \
    rh-php73-php-gd \
    rh-php73-php-xml \
    rh-php73-php-mbstring \
    rh-php73-php-ldap \
    rh-php73-php-snmp \
    rh-php73-php-intl \
    rh-php73-php-fpm \
    rh-php73-php-curl \
    rh-php73-php-zip \
    rh-php73-php-pear \
    rh-php73-ioncube-loader \
    rh-php73-php-pecl-gnupg
​
info "Copying php-fpm configuration from 7.2 to 7.3"
\cp /etc/opt/rh/rh-php72/php-fpm.d/*.conf /etc/opt/rh/rh-php73/php-fpm.d/

info "Copying php configuration from 7.2 to 7.3"
cp /etc/opt/rh/rh-php72/php.d/50-centreon.ini /etc/opt/rh/rh-php73/php.d/50-centreon.ini
​
info "Configuring system to use new PHP 7.3 binary"
mv /opt/rh/rh-php72/root/bin/php{,.backup}
ln -s /opt/rh/rh-php73/root/bin/php /opt/rh/rh-php72/root/bin/php
systemctl -q stop rh-php72-php-fpm
systemctl -q disable rh-php72-php-fpm
systemctl -q start rh-php73-php-fpm
systemctl -q enable rh-php73-php-fpm
​
info "Upgrade finished"
