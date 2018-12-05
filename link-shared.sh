#!/usr/bin/env bash

SHARED=( react-components )
mkdir -p ./www/front_src/node_modules/@centreon
pushd ./www/front_src/node_modules/@centreon > /dev/null

for lib in "${SHARED[@]}"; do
  rm -rf $lib
  ln -s ./centreon-$lib $lib
  npm install --prefix $lib/ --loglevel=warn
done

popd > /dev/null
