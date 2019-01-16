#!/usr/bin/env bash

SHARED=( react-components )
mkdir -p node_modules/@centreon
pushd node_modules/@centreon > /dev/null

for lib in "${SHARED[@]}"; do
  rm -rf $lib
  ln -s ../../centreon-$lib $lib
  sudo npm install --prefix $lib/ --loglevel=warn
done

popd > /dev/null
