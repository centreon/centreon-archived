.. _sskkeypoller:

-------------------
Echange de clés SSH
-------------------

La communication entre le serveur central et un collecteur se fait via SSH.

Vous devez échanger les clés SSH entre les serveurs.

Si vous n’avez pas de clé SSH privée sur le serveur central pour
l’utilisateur 'centreon' : ::

    # su - centreon
    $ ssh-keygen -t rsa

Vous devez copier cette clé sur le collecteur : ::

    $ ssh-copy-id centreon@your_poller_ip

Rendez-vous au chapitre :ref:`Configurer un server dans Centreon<wizard_add_poller>`.
