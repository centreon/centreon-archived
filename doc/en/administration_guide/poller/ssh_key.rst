*******************
Exchanging SSH Keys
*******************

Communication between a central server and a poller server is done through SSH.

You need to exchange SSH keys between the servers.

If you do not have any private SSH keys on the central server for the
**centreon** user: ::

    # su - centreon
    $ ssh-keygen -t rsa

.. note::
    Hit enter when it prompts for a file to save the key to use the default location, or, create one in a specified
    directory. **Leave the passphrase blank**. You will receive a key fingerprint and a randomart image.

Generate a password for the **centreon** user on the new server: ::

    # passwd centreon

Copy this key on to the new server: ::

    # su - centreon
    $ ssh-copy-id -i .ssh/id_rsa.pub centreon@IP_NEW_SERVER
