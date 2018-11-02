.. _sskkeypoller:

----------------
SSH Key exchange
----------------

The communication between a central server and a poller server is done by SSH.

You should exchange the SSH keys between the servers.

If you don’t have any private SSH keys on the central server for the Centreon user: ::

    # su - centreon
    $ ssh-keygen -t rsa

Copy this key on the new server: ::

    # su - centreon
    $ ssh-copy-id -i .ssh/id_rsa.pub centreon@IP_POLLER

Go to the :ref:`Configure a server in Centreon<wizard_add_poller>`.
