*******************
Echange de clés SSH
*******************

La communication entre le serveur central et un collecteur se fait via SSH.

Vous devez échanger les clés SSH entre les serveurs.

Si vous n’avez pas de clé SSH privée sur le serveur central pour
l’utilisateur **centreon** : ::

    # su - centreon
    $ ssh-keygen -t rsa

.. note::
    Appuyez sur la touche *entrée* quand il vous sera demandé de saisir un fichier pour enregistrer la clé.
    **Laissez le mot de passe vide**. Vous recevrez une empreinte digitale de clé et une image randomart.

Générez un mot de passe sur le nouveau serveur pour l'utilisateur
**centreon** : ::

    # passwd centreon

Vous devez copier cette clé sur le nouveau serveur : ::

    # su - centreon
    $ ssh-copy-id -i .ssh/id_rsa.pub centreon@IP_NEW_POLLER
