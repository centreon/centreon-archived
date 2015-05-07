#!/bin/bash

########################################################################
# Install script for Centreon 3 Poller on CentOS 6 (RH 6)
########################################################################

# Install base packages
yum install -y gcc curl wget git ntpdate

# To prevent restored VM having a bad date
ntpdate pool.ntp.org

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

# Check and create group/user centreon
getent group centreon &>/dev/null || groupadd -r centreon
getent passwd centreon &>/dev/null || useradd -g centreon -m -d /var/spool/centreon -r centreon

# Add group dependancy for many users
usermod -a -G centreon,centreon-engine nagios
usermod -a -G centreon,centreon-engine,nagios centreon-broker
usermod -a -G centreon,centreon-broker,nagios centreon-engine
usermod -a -G centreon-engine,centreon-broker centreon

# Start services
# Nothing to do, they should already be running due to previous steps

# Activate services on boot
chkconfig --level 2345 centengine on
chkconfig --level 2345 snmpd on

# End of script

