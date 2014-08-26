Migration d'une base de Centreon 2.x vers 3.0.0
-----------------------------------------------

Ce script regroupe la base Centreon et Centreon Storage en une seule base.
Pour des questions de temps de maintenance, la base Centreon sera migrée vers la base Centreon Storage.

Lancement du script de migration :

.. code-block:: shell

   external/bin/migrateDb.sh

Cette commande effectue la migration de la base nommée *centreon* vers la base nommée *centreon_storage*.

On peut paramétrer cette commande avec les options suivantes :

:-s:
    Le nom de la base données sources. Par défaut : *centreon*

:-d:
    Le nom de la base données destination. Par défaut: *centreon_storage*

:-t:
    Le répertoire pour les fichiers temporaires. Par défaut: */tmp*

:-u:
    L'utilisateur pour la connexion à la base de données.

:-p:
    Le mot de passe pour la connexion à la base de données.

:-H:
    Le nom de l'hôte pour la connexion à la base de données.

:-v:
    Le mode verbose.

:-D:
    Supprime la base de données sources à la fin de l'exécution du scripts.
