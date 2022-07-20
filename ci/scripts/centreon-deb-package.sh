#!/bin/sh
set -e

if [ -z "$VERSION" -o -z "$RELEASE" -o -z "$DISTRIB" ] ; then
  echo "You need to specify VERSION / RELEASE / DISTRIB variables"
  exit 1
fi

echo "################################################## PACKAGING WEB ##################################################"

AUTHOR="Luiz Costa"
AUTHOR_EMAIL="me@luizgustavo.pro.br"

# fix version to debian format accept
VERSION="$(echo $VERSION | sed 's/-/./g')"
MAJOR_VERSION="$(echo $VERSION | egrep -o '^[0-9][0-9].[0-9][0-9]')"

cd centreon/

# Replace basic macros.
COMMIT=$(git log -1 HEAD --pretty=format:%h)
find ./www/include/Administration/about -type f | xargs --delimiter='\n' sed -i -e "s/@COMMIT@/$COMMIT/g"

# set locale
mkdir -p www/locale/en_US.UTF-8/LC_MESSAGES
php bin/centreon-translations.php en lang/fr_FR.UTF-8/LC_MESSAGES/messages.po www/locale/en_US.UTF-8/LC_MESSAGES/messages.ser
for i in lang/* ; do
  localefull=`basename $i`
  langcode=`echo $localefull | cut -d _ -f 1`
  mkdir -p "www/locale/$localefull/LC_MESSAGES"
  msgfmt "lang/$localefull/LC_MESSAGES/messages.po" -o "www/locale/$localefull/LC_MESSAGES/messages.mo" || exit 1
  msgfmt "lang/$localefull/LC_MESSAGES/help.po" -o "www/locale/$localefull/LC_MESSAGES/help.mo" || exit 1
  php bin/centreon-translations.php "$langcode" "lang/$localefull/LC_MESSAGES/messages.po" "www/locale/$localefull/LC_MESSAGES/messages.ser"
done
rm -rf lang

# Generate API documentation.
apt install -y npm && sleep 30
npm install -g redoc-cli
/usr/local/bin/redoc-cli bundle --options.hideDownloadButton=true doc/API/centreon-api-v${MAJOR_VERSION}.yaml -o ../centreon-api-v${MAJOR_VERSION}.html

# Make tar with original content
cd ..
ls -lart
tar czpf centreon-$VERSION.tar.gz centreon
cd centreon/

# make debian config
cp -rf ci/debian .
sed -i "s/^centreon:version=.*$/centreon:version=${MAJOR_VERSION}/" debian/substvars
debmake -f "${AUTHOR}" -e "${AUTHOR_EMAIL}" -u "$VERSION" -y -r "${DISTRIB}"
debuild-pbuilder
cd ../
ls -lart
#mv *.deb /src
