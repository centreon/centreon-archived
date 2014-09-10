Comment écrire d'un module pour Centreon 3
==========================================

**Centreon** permet la modification et l'ajout de fonctionnalité par un système de module.

Pour écrire un module, le module doit suivre l'architecture suivante et respecter les règles de nommages.
Centreon 3.0 embarque u générateur de modules en ligne de commande qui créé un module simple prêt à être utilisé.

Règle de nommage
----------------

Le nom du module doit se finir avec le terme Module et être en `camel case <http://fr.wikipedia.org/wiki/CamelCase>`.

Architecture du système de fichiers
-----------------------------------

Le répertoire du module doit contenir les répertoires suivants.

    - **/api** : 
        - **/internal** : contains the base api that should be use by other modules (mandatory)
        - **/rest** : RESTful API exposition based upon the internal API (optionnal)
        - **/soap** : soap API exposition based upon the internal API (optionnal)
    - **/config** : contains the module specific configuration files
    - **/controllers** : contains the modules controllers (mandatory)
    - **/customs** : contains modules' external libraireis, custom functions
    - **/internal** : contains modules' specific classes
    - **/models** : contains modules' models
    - **/respositories** : contains modules' repositories (business logic sql request)
    - **/views** : contains modules' HTML templates
    - **/tests** : containes modules' unit tests
    - **/install** : contient les fichiers pour l'installation (mandatory)
        - config.json : les informations sur le module (mandatory)
        - Installer.php : installation du module (mandatory)
        - **/db** : les tables associés à ce module
        - **/forms** : les définitions des formulaires pour ce modules
        - menu.json : les liens dans le menu

Générateur de modules en ligne de commande
------------------------------------------

A l'aide de la commande ./centreonConsole core:module:tools:generate, vous pouvez gérer un embryon de module fonctionnel.
Il suffit de suivre les instructions affichées.

Partie installation
-------------------

.. toctree::
   :maxdepth: 2

   how_to_write_module/installation

Ajout de page
---------------

Les pages sont desservies par les contrôleurs.

.. toctree::
   :maxdepth: 2

   how_to_write_module/controllers
