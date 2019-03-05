#!/bin/sh

@PHP_BIN@ @INSTALL_DIR_CENTREON@/bin/composer --working-dir=@INSTALL_DIR_CENTREON@ dump-autoload -o
