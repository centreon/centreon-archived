#!/bin/sh
set -e

if [ -z "$VERSION" -o -z "$RELEASE" -o -z "$DISTRIB" ] ; then
  echo "You need to specify VERSION / RELEASE / DISTRIB variables"
  exit 1
fi
rm -rf /root/.cache/*
echo "################################################## PACKAGING WEB ##################################################"

AUTHOR="Luiz Costa"
AUTHOR_EMAIL="me@luizgustavo.pro.br"
export
# fix version to debian format accept
VERSION="$(echo $VERSION | sed 's/-/./g')"

tar czpf centreon-$VERSION.tar.gz centreon
cd centreon/
cp -rf ci/debian .
sed -i "s/^centreon:version=.*$/centreon:version=$(echo $VERSION | egrep -o '^[0-9][0-9].[0-9][0-9]')/" debian/substvars
debmake -f "${AUTHOR}" -e "${AUTHOR_EMAIL}" -u "$VERSION" -y -r "${DISTRIB}"
debuild-pbuilder

