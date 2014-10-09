Les hooks pour l'affichage
--------------------------

Enregistrement de votre hook
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Afin d'utiliser un hook dans votre module, il faut enregistrer son appel dans le fichier
**modules/YourModule/install/registeredHooks.json**. 

Exemple du fichier **registeredHooks.json**::
   [
      {
         "name": "displayLeftMenu",
         "moduleHook": "displayExample",
         "moduleHookDescription": "A short description of what my hook displays !"
      }
   ]


Ecriture du hook
~~~~~~~~~~~~~~~~

Puis, il vous faut créer une classe portant le nom de votre Hook, dans le dossier **modules/YourModule/hooks/**. Cette
dernière doit contenir une méthode statique *execute()*. Le tableau de retour doit contenir la clé **template**
avec pour valeur le nom du fichier de template que vous allez afficher. Il est aussi possible de passer des variables 
au fichier template en utilisant la clé **variables** dans le tableau de retour.

Exemple du fichier **DisplayExample.php**::
   class DisplayExample
   {
      public static function execute($params)
      {
         return array(
            'template' => 'myTemplate.tpl'
            'variables' => array(
               'userName' => 'John Doe'
               'welcomeMessage' => 'Hello'
            )
         );
      }
   }

Le fichier template doit se trouver dans **modules/YourModule/views/**.

Exemple du fichier **myTemplate.tpl**::
   <div>
      {$variables.welcomeMessage} {$variables.userName}
   </div>

Sur l'interface Web, vous verrez alors en dessous du menu à gauche::
   <div>
      Hello John Doe
   </div>

Certains hooks peuvent passer des paramètres à votre méthode **execute()**, vous pouvez donc les utiliser !

Vous trouverez une multitude de hooks fournis par les différents modules de Centreon, mais vous pouvez également
implémenter vos propres hooks dans vos modules: suivez la documentation pour savoir `comment implémenter 
un hook`.


CentreonMain
------------

displayLeftMenu
~~~~~~~~~~~~~~~

Description
###########

Permet d'ajouter des informations dans le menu latéral gauche.

Paramètres
##########

Aucun



CentreonConfiguration
---------------------

displayNodePaths
~~~~~~~~~~~~~~~~

Description
###########

Permet d'ajouter des éléments dans l'onglet *Paths* du formulaire de configuration des pollers.

Paramètres
##########

pollerId: l'identifiant du poller lorsque le formulaire est en mode Edition.
