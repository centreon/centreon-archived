Format du fichier *config.json*
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

.. highlight:: json

   {
     "name": "Mon module",
     "shortname": "mon",
     "version": "1.0.0",
     "author": [
       "author"
     ],
     "url": "http://www.domain.tld/mon_module",
     "description": "Description de mon module",
     "core version": "3.0.0",
     "dependencies": [
     ],
     "optionnal dependencies": [
     ],
     "php module dependencies": [
     ],
     "program dependencies": [
     ]
   }


Explication des variables

name::
    Le nom du module utilisé pour l'affichage dans l'interface

shortname::
    Le nom court du module qui sera utilisé dans l'url et qui doit être le nom du répertoire sans le terme 'Module' à la fin

version::
    La version du module. Cette version doit respecter la norme `semver <http://semver.org/>`

author::
    Les listes des contributeurs pour ce module

url::
    Le lien vers la page d'accueil internet du module

description::
    La description du module

core version::
    La version minimum de Centreon

dependencies::
    Les modules obligatoires pour le fonctionnement de ce module.

optionnal dependencies::
    Le modules optionnels pour ce module

php module dependencies::
    Les modules PHP utilisés pour ce module

program dependencies::
    Les programmes externes utilisés par ce module


La classe d'installation Install.php
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Le fichier d'installation contient une classe Installer qui étend la classe \Centreon\Internal\Module\Installer.

Voici les méthodes qui doivent être surchargés.

customPreInstall::
    Exécute du code PHP avant l'installation

customInstall::
    Exécute du code PHP lors l'installation du module

customRemove::
    Exécute du code PHP lors de suppression du module

La base de données
~~~~~~~~~~~~~~~~~~

Les fichiers pour l'ajout de table dans la base de données se situent dans le répertoire /install/db/centreon. Le format du nom de fichier est nom_table.xml.
Le fichier contient la définition de la table. Le format est comme décrit `ici <http://propelorm.org/Propel/reference/schema.html>`.

La différence entre la base de données et les fichiers définis dans ce répertoires est effectuée au moment de l'installation ou de la mise à jour.

Les formulaires
~~~~~~~~~~~~~~~

La définition des formulaires se trouve dans le répertoire /install/forms. Le format du fichier est du XML et défini comme `ici`.

La différence entre les formulaires et les fichiers définis dans ce répertoires est effectuée au moment de l'installation ou de la mise à jour.
