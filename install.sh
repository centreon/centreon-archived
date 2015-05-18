#!/bin/bash

########################################################################
# Install script for Centreon 3 master on CentOS 6 (RH 6)
########################################################################

# Install base packages
yum install -y gcc rrdtool rrdtool-devel curl wget ntpdate

# To prevent restored VM having a bad date
ntpdate pool.ntp.org

# Install LA*P stack
yum install -y centos-release-SCL
yum install -y php54 php54-php-cli php54-php-mysql php54-php-xml php54-php-pdo php54-php-mbstring php54-php-devel php54-php php54-php-process php54-php-pear php54-php-gd
# FIXME for compat' with shebang of centreonConsole
ln -sf /opt/rh/php54/root/usr/bin/php /usr/bin/php

# Replace timezone in /opt/rh/php54/root/etc/php.ini
sed -i 's/^\(;date.timezone.*\)/\1\ndate.timezone = Europe\/Paris/' /opt/rh/php54/root/etc/php.ini

# Add RRD PHP extension not available as a module
# OK: /opt/rh/php54/root/usr/bin/pecl install rrd
# Not tested: 
scl enable php54 "pecl install rrd"
# FIXME check build status ;)
cat << EOF > /opt/rh/php54/root/etc/php.d/rrd.ini
extension=rrd.so
EOF

# Add MariaDB
cat << EOF > /etc/yum.repos.d/mariadb.repo
[mariadb]
name = MariaDB
baseurl = http://yum.mariadb.org/10.0/centos6-amd64
gpgkey=https://yum.mariadb.org/RPM-GPG-KEY-MariaDB
gpgcheck=1
EOF

yum install -y MariaDB MariaDB-client

service mysql start
mysql -u root -e "grant all privileges on centreon.* to 'centreon'@'localhost' identified by 'centreon';"
mysql -u root -e "create database centreon;"

# Configure Apache
cat << EOF > /etc/httpd/conf.d/centreon.conf
<VirtualHost *:80>
  DocumentRoot /srv/centreon/www

  <Directory "/srv/centreon/www">
          Options +Indexes +FollowSymLinks
          AllowOverride All
          Order allow,deny
          Allow from all
  
        php_value output_buffering 4096
  </Directory>
</VirtualHost>
EOF
service httpd start

# Add Centreon repos (CES + internal dev)

cat << EOF > /etc/yum.repos.d/ces-standard.repo
[ces-standard]
name=Centreon Enterprise Server RPM repository for ces \$releasever
baseurl=http://yum.centreon.com/standard/3.0/stable/\$basearch/
enabled=1
gpgcheck=1
gpgkey=file:///etc/pki/rpm-gpg/RPM-GPG-KEY-CES

[ces-standard-noarch]
name=Centreon Enterprise Server RPM repository for ces \$releasever
baseurl=http://yum.centreon.com/standard/3.0/stable/noarch/
enabled=1
gpgcheck=1
gpgkey=file:///etc/pki/rpm-gpg/RPM-GPG-KEY-CES

[ces-standard-deps]
name=Centreon Enterprise Server dependencies RPM repository for ces \$releasever
baseurl=http://yum.centreon.com/standard/3.0/stable/dependencies/\$basearch/
enabled=1
gpgcheck=1
gpgkey=file:///etc/pki/rpm-gpg/RPM-GPG-KEY-CES

[ces-standard-deps-noarch]
name=Centreon Enterprise Server dependencies RPM repository for ces \$releasever
baseurl=http://yum.centreon.com/standard/3.0/stable/dependencies/noarch/
enabled=1
gpgcheck=1
gpgkey=file:///etc/pki/rpm-gpg/RPM-GPG-KEY-CES

[ces-standard-devel]
name=Centreon Enterprise Server development RPM repository for ces \$releasever
baseurl=http://yum.centreon.com/standard/3.0/stable/devel/\$basearch/
enabled=0
gpgcheck=1
gpgkey=file:///etc/pki/rpm-gpg/RPM-GPG-KEY-CES

[ces-standard-devel-noarch]
name=Centreon Enterprise Server development RPM repository for ces \$releasever
baseurl=http://yum.centreon.com/standard/3.0/stable/devel/noarch/
enabled=0
gpgcheck=1
gpgkey=file:///etc/pki/rpm-gpg/RPM-GPG-KEY-CES
EOF

cat << EOF > /etc/yum.repos.d/centreon3-dev.repo
[centreon3-dev-noarch]
name=Centreon 3 Devel noarch
baseurl=http://srvi-ces-repository.merethis.net/repos/centreon3-dev/el\$releasever/noarch/
gpgcheck=1
gpgkey=file:///etc/pki/rpm-gpg/RPM-GPG-KEY-CES
enabled=1

[centreon3-dev]
name=Centreon 3 Devel
baseurl=http://srvi-ces-repository.merethis.net/repos/centreon3-dev/el\$releasever/\$basearch/
gpgcheck=1
gpgkey=file:///etc/pki/rpm-gpg/RPM-GPG-KEY-CES
enabled=1
EOF

wget http://yum.centreon.com/standard/3.0/stable/RPM-GPG-KEY-CES -O /etc/pki/rpm-gpg/RPM-GPG-KEY-CES

# Broker
# Note "*" is important to install modules
yum install -y centreon-broker*
service cbd start

# Engine
yum install -y centreon-engine
chown centreon-engine.centreon-engine /etc/centreon-engine
chmod 775 /etc/centreon-engine
# FIXME, default conf/layout not good
rm -rf /etc/centreon-engine/objects/*
service centengine start

# Configure sudo
cat << EOF > /etc/sudoers.d/centreon
## BEGIN: CENTREON SUDO
#Add by CENTREON installation script
User_Alias      CENTREON=apache,nagios,centreon,centreon-engine,centreon-broker
Defaults:CENTREON !requiretty
## Centreontrapd Restart
CENTREON   ALL = NOPASSWD: /etc/init.d/centreontrapd restart
## CentStorage
CENTREON   ALL = NOPASSWD: /etc/init.d/centstorage *
# Centengine Restart
CENTREON   ALL = NOPASSWD: /etc/init.d/centengine restart
# Centengine stop
CENTREON   ALL = NOPASSWD: /etc/init.d/centengine start
# Centengine stop
CENTREON   ALL = NOPASSWD: /etc/init.d/centengine stop
# Centengine reload
CENTREON   ALL = NOPASSWD: /etc/init.d/centengine reload
# Centengine test config
CENTREON   ALL = NOPASSWD: /usr/sbin/centengine -v *
# Centengine test for optim config
CENTREON   ALL = NOPASSWD: /usr/sbin/centengine -s *
# Broker Central restart
CENTREON   ALL = NOPASSWD: /etc/init.d/cbd restart
# Broker Central reload
CENTREON   ALL = NOPASSWD: /etc/init.d/cbd reload
# Broker Central start
CENTREON   ALL = NOPASSWD: /etc/init.d/cbd start
# Broker Central stop
CENTREON   ALL = NOPASSWD: /etc/init.d/cbd stop
## END: CENTREON SUDO
EOF

chmod 440 /etc/sudoers.d/centreon

# Install SNMP
yum install -y perl-Net-SNMP.noarch net-snmp-perl.x86_64 net-snmp.x86_64 net-snmp-utils.x86_64
cat << EOF > /etc/snmp/snmpd.conf
####
# First, map the community name "public" into a "security name"

#       sec.name  source          community
com2sec notConfigUser  default       public

####
# Second, map the security name into a group name:

#       groupName      securityModel securityName
group   notConfigGroup v1           notConfigUser
group   notConfigGroup v2c           notConfigUser

####
# Third, create a view for us to let the group have rights to:

# Make at least  snmpwalk -v 1 localhost -c public system fast again.
#       name           incl/excl     subtree         mask(optional)
view centreon included .1.3.6.1
view    systemview    included   .1.3.6.1.2.1.1
view    systemview    included   .1.3.6.1.2.1.25.1.1

####
# Finally, grant the group read-only access to the systemview view.

#       group          context sec.model sec.level prefix read   write  notif
access notConfigGroup "" any noauth exact centreon none none
access  notConfigGroup ""      any       noauth    exact  systemview none none

includeAllDisks 10%
EOF

service snmpd start

# Install centreon-plugins
git clone https://github.com/centreon/centreon-plugins.git /usr/lib/nagios/plugins/centreon-plugins/

# On to the PHP soft now, first let's install composer + update Centreon dependencies
curl -sS https://getcomposer.org/installer | scl enable php54 "php -- --install-dir=/usr/local/bin"
mv /usr/local/bin/composer.phar /usr/local/bin/composer
cd /srv/centreon
scl enable php54 "composer update"

# Edit centreon.ini
sed -i 's/^\(username.=.*\)/username=centreon/' /srv/centreon/config/centreon.ini
sed -i 's/^\(password.=.*\)/password=centreon/' /srv/centreon/config/centreon.ini

external/bin/centreonConsole core:internal:install
external/bin/centreonConsole core:module:manage:install moduleName=centreon-broker
external/bin/centreonConsole core:module:manage:install moduleName=centreon-engine
external/bin/centreonConsole core:module:manage:install moduleName=centreon-performance 
\cp -r modules/CentreonAdministrationModule/static/centreon-administration/ www/static/
\cp -r modules/CentreonPerformanceModule/static/centreon-performance/ www/static/
\cp -r modules/CentreonConfigurationModule/static/centreon-configuration/ www/static/

chown apache.apache /srv/centreon/www/uploads/images
usermod -a -G centreon-engine apache
usermod -a -G centreon-broker apache

# Check and create group/user centreon
getent group centreon &>/dev/null || groupadd -r centreon
getent passwd centreon &>/dev/null || useradd -g centreon -m -d /var/spool/centreon -r centreon
usermod -a -G centreon apache

# Needed to apply new groups to the process
service httpd restart

# Create default generation directory
mkdir -p /tmp/broker/generate /tmp/broker/apply /tmp/engine/generate /tmp/engine/apply
chown centreon: /tmp/broker/generate /tmp/broker/apply /tmp/engine/generate /tmp/engine/apply
chmod g+ws /tmp/broker/generate /tmp/broker/apply /tmp/engine/generate /tmp/engine/apply
setfacl -R -m d:u:centreon:rwX,d:g:centreon:rwX,d:o:r-X /tmp/broker/generate /tmp/broker/apply /tmp/engine/generate /tmp/engine/apply

# Start services
# Nothing to do, they should already be running due to previous steps

# Activate services on boot
chkconfig --level 2345 mysql on
chkconfig --level 2345 httpd on
chkconfig --level 2345 cbd on
chkconfig --level 2345 centengine on
chkconfig --level 2345 snmpd on

# FIXME We should add somewhere a oif checks like SE Linux disbaled + PHP version and so on

# End of script
