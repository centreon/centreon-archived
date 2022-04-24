#!/bin/sh
set -e

if [ -z "$VERSION" -o -z "$RELEASE" -o -z "$DISTRIB" ] ; then
  echo "You need to specify VERSION / RELEASE / DISTRIB variables"
  exit 1
fi

echo "################################################## PACKAGING WEB ##################################################"

AUTHOR="Luiz Costa"
AUTHOR_EMAIL="me@luizgustavo.pro.br"
ls -lart
tar czpf centreon-$VERSION.tar.gz centreon
cd centreon/
cp -rf ci/debian .
debmake -f "${AUTHOR}" -e "${AUTHOR_EMAIL}" -u "$VERSION" -y -r "$RELEASE"
debuild-pbuilder
cd ../
ls -lart
#mv *.deb /src
