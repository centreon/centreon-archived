#!/bin/bash

export LANG=C

AUTO=0
if [ $# -eq 1 ]; then
  if [ $1 = "-auto" ]; then
    AUTO=1
  fi
fi

USE_SUDO=""
if [ ${EUID} -ne 0 ]; then
  USE_SUDO="sudo "
fi

OLDKEY_NAME="gpg-pubkey-8a7652bc-4cb6f1f6"
OLDKEY_ID="1024D/8A7652BC"

NEWKEY_NAME="gpg-pubkey-3fc49c1b-6166eb52"

rpm -q gpg-pubkey --qf '%{NAME}-%{VERSION}-%{RELEASE}\t%{SUMMARY}\n' | grep -q "${OLDKEY_NAME}"

if [ $? -eq 0 ]; then
  if [ ${AUTO} -eq 0 ]; then
    echo "
  The old key is in your RPM database, you should remove it.
  You can remove it by running this command:

    sudo rpm -e ${OLDKEY_NAME}

  Then restart this script.
  "
    exit 1
  else
    echo "The old key is in your RPM detabase. Removing it..."
    ${USE_SUDO}rpm -e ${OLDKEY_NAME}
    if [ $? -ne 0 ]; then
      echo "Error removing the old key"
      exit 1
    fi
  fi
fi

rpm -q gpg-pubkey --qf '%{NAME}-%{VERSION}-%{RELEASE}\t%{SUMMARY}\n' | grep -q "${NEWKEY_NAME}"
if [ $? -ne 0 ]; then
  if [ ${AUTO} -eq 0 ]; then
    echo "
  The new key is not in your RPM database, you should add it.
  You can add it by running this command:

    sudo rpm --import https://yum-gpg.centreon.com/RPM-GPG-KEY-CES

  Then restart this script.
  "
    exit 1
  else
    echo "The new key is not in your RPM detabase. Adding it.."
    ${USE_SUDO}rpm --import "https://yum-gpg.centreon.com/RPM-GPG-KEY-CES"
    if [ $? -ne 0 ]; then
      echo "Error removing the old key"
      exit 1
    fi
  fi
fi

for key in /etc/pki/rpm-gpg/*
do
  gpg $key | grep -q "pub  $OLDKEY_ID"
  if [ $? -eq 0 ]; then
    if [ ${AUTO} -eq 0 ]; then
      echo "
  The old key is in your system keys, you should remove it: $key
  Upgrading the centreon-release package will remove it for you:

    sudo yum update centreon*release

  Then restart this script.
  "
      exit 1
    else
      echo "Updating centreon release RPM..."
      ${USE_SUDO}yum update -q -y centreon*release
      if [ $? -ne 0 ]; then
        echo "Error upgrading repositories configuration"
        exit 1
      fi
    fi
  fi
done

echo "The new key is correctly imported."