#!/bin/bash

for file in $(find ./ -name '*.js')
do
  mv $file $(echo "$file" | sed -r 's|.js|.jsx|g')
done
