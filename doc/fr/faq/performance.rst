.. _performance:

===========
Performance
===========

Ce chapitre est un guide pour optimiser Centreon

****************
Bases de données
****************

Le serveur de base de données est l'un des éléments centraux de Centreon. 
Sa performance a un impact direct sur l'utilisateur de l'interface web.
Centreon utilise deux ou trois bases de données en fonction de votre broker:

* ``centreon`` -- Stockage de la configuration
* ``centreon_storage`` -- Données temps réelle et historique
* ``centreon_status`` -- Données temps réelle si ``ndo2db`` est utilisé

La base de données ``centreon_status`` est installée même si vous n'utilisez pas ``ndo2db``.

Index
=====

Les bases de données utilisent des index pour accélérer les requêtes. Dans le 
cas où des index sont manquants les requêtes sont plus longues à être exécutées. 

.. _synchronizing-indexes:

Synchronisation des index
*************************

Des fichiers d'index sont générées pour chaque version de Centreon depuis la version `2.4.0``.
Ils sont situés dans le répertoire ``data`` normalement situé dans les répertoires ``bin``
ou ``www``. Il y a un fichier JSON pour chaque base de données:

* ``centreonIndexes.json`` -- Index pour la base ``centreon``
* ``centreonStorageIndexes.json`` -- Index pour la base ``centreon_storage``
* ``centreonStatusIndexes.json`` -- Index pour la base ``centreon_status``

Vérifiez si votre base de données est désynchronisée:
  ::

    $ cd CENTREONBINDIR
    $ ./import-mysql-indexes -d centreon -i ../data/centreonIndexes.json

Si des différences sont détectées, vous pouvez synchroniser votre base de données.
Le processus prend quelques minutes mais **si votre base de données contient un fort
volume de données sans index, cela peut prendre plus de 2 heures**. Soyez sûre d'avoir
assez de place disponible sur la partition pour reconstruire les index :
 ::

    $./import-mysql-indexes -d centreon -i ../data/centreonIndexes.json -s

.. note::
    **Les index utilisés par les clés étrangères ('foreign keys ') ne peuvent être synchronisés.**

L'option ``-s`` ou ``--sync`` doit être utilisée pour mettre à jour la base de données.
Si vous avez besoin de définir l'utilisateur et le mot de passe, utiliser respectivement
les options ``-u`` et ``-p``.

Optimisations InnoDB 
====================

Cette section n'est pas encore documentée.
