Gestion des hooks
~~~~~~~~~~~~~~~~~

Les hooks sont divisés en deux parties, les hooks d'affichage qui permet d'ajouter des informations au moment de l'affichage de l'interface et les events qui permettent d'ajouter des modifications fonctionnelles au moteur Centreon.

Enrgistrement de hooks
######################

Les events
^^^^^^^^^^^^^^^^^^

Pour enregistrer une action auprès d'un event, il faut créer un fichier dans le répertoire *listeners* du module.

Voici le format du fichier : listeners\ModuleSource\EventName.php

Ce fichier répondera au event **ModuleSource.EventName**. Ce fichier contient une classe avec un méthode statique *execute*. Cette méthode prend un paramètre qui est un objet.

Exemple

.. highlight:: php

   <?php

   namespace \MonModule\Listeners\ModuleSource;

   class EventName {
        static public function execute($objParam)
        {
            myaction();          
        }
   }

 
La liste des events ce trouve `ici`.

Les hooks d'affichage
^^^^^^^^^^^^^^^^^^^^^

L'enregistrement des actions liées aux hooks d'affichage se font au moment de l'installation ou de la mise à jour du module.

Les actions des hooks d'affichage sont défini dans un fichier json du nom de *install/registeredHooks.json*.

.. highlight:: json

   [
     {
       "name": "hookName",
       "moduleHook": "hookNameInModule",
       "moduleHookDescription": "Description of hook in module"
     }
   ]

Il faut ensuite créer un fichier dans le répertoire *hooks* du nom moduleHook en camel case. Ce fichier contient une classe avec un méthode statique *execute*. Cette méthode prend un paramètre qui est un objet.

Exemple hooks/HookNameInModule.php

.. highlight:: php

   <?php

   namespace \MonModule;

   class HookNameInModule {
        static public function execute($objParam)
        {
            myaction();          
        }
   }

La liste des events ce trouve `ici`.

Mise à disposition d'un nouveau hook
####################################

Les events
^^^^^^^^^^^^^^^^^^

Pour mettre à disposition un event, il faut effectuer un appel avec son nom.

Exemple

.. highlight:: php

   <?php

   $emitter = \Centreon\Internal\Di::getDefault()->get('events');
   $params = new EventNameEvent("param1");
   $emitter->emit('ModuleSource.EventName', array($params));

Les hooks d'affichage
^^^^^^^^^^^^^^^^^^^^^

Si votre module propose un nouveau hook d'affichage, il faut créer un fichier *install/hooks.json* qui contient la liste des hooks d'affichage que le module met à disposition.

.. highlight:: json

   [
     {
       "name": "hookName",
       "description": "Description of my new hook"
     }
   ]

Cette action de mise à disposition est effectuée au moment de l'installation ou de la mise à jour d'un module.

Pour effectuer un appel aux actions enregistrées au hook. Il faut ajouter l'appel suivant dans votre template.

.. highlight:: jinja2

   <div>{{ 'hookName' container='<ul>[hook]</ul>' }}</div>

Le paramètre *container* est un chaîne HTML avec un macro **[hook]** qui sera remplacé par le retour du hook.
