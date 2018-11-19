****************
SSH Key exchange
****************

The communication between a central server and a poller server is done by SSH.

You should exchange the SSH keys between the servers.

If you don’t have any private SSH keys on the central server for the
**centreon** user: ::

    # su - centreon
    $ ssh-keygen -t rsa

Generate a password for the **centreon** user on the new server: ::

    # passwd centreon

Copy this key on the new server: ::

    # su - centreon
    $ ssh-copy-id -i .ssh/id_rsa.pub centreon@IP_POLLER
