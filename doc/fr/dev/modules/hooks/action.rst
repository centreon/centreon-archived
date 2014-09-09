Les hooks d'action
------------------

Les hooks d'action permettent d'ajouter des méthodes fonctionnelles au moment de certaines actions.

Par exemple, avant/après la sauvegarde d'un objet, au chargement de données ou avant l'exécution d'une requête SQL...

centreon-configuration.copy.files
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Description
###########

Copie les fichiers de configuration générer par Centreon dans le répertoires de configuration.

Paramètres
##########

Nom de la classe: \CentreonConfiguration\Events\CopyFiles

pollerId: L'identifiant du collecteur.

.. highlight:: php

   <?php

   $params = new CopyFiles($pollerId);

Exemple
#######

.. highlight:: php

   <?php

   namespace \Module\Listeners\CentreonConfiguration;

   class CopyFiles {
        static public function execute($params) {
            copy($tmpFile, $params->getPollerId());
        }
   }

centreon-configuration.engine.process
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Description
###########

Exécute une action de restart, reload, forcereload ..., sur un moteur.

Paramètres
##########

Nom de la classe: \CentreonConfiguration\Events\EngineProcess

pollerId::
  L'identifiant du collecteur.

action::
  L'action a effectué sur le processus du moteur.

.. highlight:: php

   <?php

   $params = new EngineProcess($pollerId, $action);

Exemple
#######

.. highlight:: php

   <?php

   namespace \Module\Listeners\CentreonConfiguration;

   class EngineProcess {
        static public function execute($params) {
          $command = getCommand($pollerId);
          exec($command . " " . $action, $output, $status);
          $params->setOutput($output);
          $params->setStatus($status);
        }
   }

centreon-configuration.generate.engine
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Description
###########

Génére dans un répertoire temporaire les fichiers de configuration pour un moteur.

Paramètres
##########

Nom de la classe: \CentreonConfiguration\Events\GenerateEngine

pollerId::
  L'identifiant du collecteur.

.. highlight:: php

   <?php

   $params = new GenerateEngine($pollerId);

Exemple
#######

.. highlight:: php

   <?php

   namespace \Module\Listeners\CentreonConfiguration;

   class GenerateEngine {
        static public function execute($params) {
          $tmpDir = getTmpDir($pollerId);
          geneateConf($tmpDir);
        }
   }

centreon-configuration.run.test
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Description
###########

Test la validité des fichiers de configurations.

Paramètres
##########

Nom de la classe: \CentreonConfiguration\Events\RunTest

pollerId::
  L'identifiant du collecteur.

.. highlight:: php

   <?php

   $params = new RunTest($pollerId);

Exemple
#######

.. highlight:: php

   <?php

   namespace \Module\Listeners\CentreonConfiguration;

   class RunTest {
        static public function execute($params) {
          $tmpDir = getTmpDir($pollerId);
          testConfig($tmpDir);
        }
   }
