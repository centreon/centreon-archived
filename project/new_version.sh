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
cat << EOF > "www/install/php/Update-${newversion}.php"
<?php
/*
 * Copyright 2005 - 2019 Centreon (https://www.centreon.com/)
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * For more information : contact@centreon.com
 *
 */
EOF
git add "www/install/php/Update-${newversion}.php"

# Add release notes.
cat << EOF > nv.rst
====================
Centreon Web $newversion
====================

Enhancements
------------

Bug Fixes
---------

EOF
cat nv.rst "doc/en/release_notes/centreon-$major.$minor.rst" > rn.rst
cp rn.rst "doc/en/release_notes/centreon-$major.$minor.rst"
cp rn.rst "doc/fr/release_notes/centreon-$major.$minor.rst"
rm -f nv.rst rn.rst
git add "doc/en/release_notes/centreon-$major.$minor.rst"
git add "doc/fr/release_notes/centreon-$major.$minor.rst"
