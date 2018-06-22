#!/bin/sh

# Get version and next version.
oldversion=`grep informations www/install/insertBaseConf.sql | cut -d "'" -f 4`
major=`echo $oldversion | cut -d . -f 1`
minor=`echo $oldversion | cut -d . -f 2`
patch=`echo $oldversion | cut -d . -f 3`
newversion="$major.$minor."$(($patch+1))

# Set new version in base installation script.
sed -i "s/$oldversion/$newversion/" www/install/insertBaseConf.sql
git add www/install/insertBaseConf.sql

# Add update script.
cat << EOF > "www/install/sql/centreon/Update-DB-${oldversion}_to_${newversion}.sql"
-- Change version of Centreon
UPDATE \`informations\` SET \`value\` = '$newversion' WHERE CONVERT( \`informations\`.\`key\` USING utf8 ) = 'version' AND CONVERT ( \`informations\`.\`value\` USING utf8 ) = '$oldversion' LIMIT 1;
EOF
git add "www/install/sql/centreon/Update-DB-${oldversion}_to_${newversion}.sql"

# Add release notes.
cat << EOF > "doc/en/release_notes/centreon-$major.$minor/centreon-$newversion.rst"
###################
Centreon Web $newversion
###################

Enhancements
============

Bug Fixes
=========
EOF
cp "doc/en/release_notes/centreon-$major.$minor/centreon-$newversion.rst" "doc/fr/release_notes/centreon-$major.$minor/centreon-$newversion.rst"
git add "doc/en/release_notes/centreon-$major.$minor/centreon-$newversion.rst"
git add "doc/fr/release_notes/centreon-$major.$minor/centreon-$newversion.rst"

# Add release notes to index.
echo "    centreon-$newversion" >> "doc/en/release_notes/centreon-$major.$minor/index.rst"
echo "    centreon-$newversion" >> "doc/fr/release_notes/centreon-$major.$minor/index.rst"
git add "doc/en/release_notes/centreon-$major.$minor/index.rst"
git add "doc/fr/release_notes/centreon-$major.$minor/index.rst"
