#!/bin/sh


# Enable var directory mod
chown -R apache:apache var/

# Set Binary mod
chmod +x ./bin/*
chown -R centreon:centreon bin/*


