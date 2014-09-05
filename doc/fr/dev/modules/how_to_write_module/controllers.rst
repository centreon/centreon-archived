Gestion des contrôleurs
~~~~~~~~~~~~~~~~~~~~~~~~

Les contrôleurs se trouvent dans le répertoire **/controllers**. Le nom de fichier est en `camel case <http://fr.wikipedia.org/wiki/CamelCase>` et fini par le terme Controller.

Le fichier contient une classe du même nom qui étend la classe \Centreon\Internal\Controller. Pour le fonctionnement du contrôler, il faut implémenter les variables.

$module::
    Le nom du module sans le terme Module

Les routes du module
~~~~~~~~~~~~~~~~~~~~

Pour ajouter une route au module, il faut ajouter une méthode à la classe contrôleur. La définition de la route est configurée dans les commentaires de la fonction.

.. highlight:: php

   /**
    * Ma fonction pour ma nouvelle route
    *
    * @method get
    * @route /module/ma_route
    */

L'annotation @method défini la méthode HTTP qui sera acceptée par cette route.

L'annotation @route défini le chemin de la route.
