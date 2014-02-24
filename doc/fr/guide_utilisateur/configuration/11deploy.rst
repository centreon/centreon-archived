==========================
Déployer une configuration
==========================

*********
Procédure
*********

Lors de la création/suppression/modification des objets via l'interface de configuration, les changements effectués ne sont pas appliqués de manière automatique aux serveurs de supervision.
Afin de pouvoir appliquer les modifications effectuées, il est nécessaire de suivre la procédure suivante ci dessous.

.. note::
   Celle-ci doit toujours être déroulée en 2 étapes.
   
Première étape
==============
 #.	Rendez-vous dans le menu **Configuration** ==> **Moteurs de supervision** ==> **Générer**
 #.	Selectionner le **Collecteur** dans la liste déroulant.
 #.	Cochez les cases **Générer les fichiers de configuration** et **Lancer le débogage du moteur de supervision (-v)**
 #. Cliquez sur **Exporter**
 
 [TODO mettre une capture d'écran]

Vérifier qu'aucune erreur n'apparait lors de la génération :

.. note::
    Si cela est le cas, corriger les erreurs et refaire la première étape.

Deuxième étape
==============
 #.	Décochez **Générer les fichiers de configuration** et **Lancer le débogage du moteur de supervision (-v)**
 #.	Cochez **Déplacer les fichiers générés** ainsi que **Redémarrer l'ordonnanceur**
 #.	Cliquez sur **Exporter**
 
[METTRE UNE CAPTURE D'ECRAN LORSQUE L'EXPORTATION EST TERMINEE]

.. note::
    L'option **Commande exécutée post-génération** permet de demander l'exécution de la commande post-génération paramétrée au niveau de la configuration de l'ordonnanceur.

************
Explications
************

Au sein de la page de génération de la configuration, plusieurs options sont disponibles :

 #.	**Générer les fichiers de configuration** : Génère les fichiers de configuration de l'ordonnanceur dans un répertoire temporaire. Cette configuration est générée à partir des objets configurés via l'interface web
 #.	**Lancer le débogage du moteur de supervision (-v)** : Permet à l'ordonnanceur de vérifier la configuration générée
 #.	**Déplacer les fichiers générés** : Déplace les fichiers de configuration du répertoire temporaire vers le répertoire de l'ordonnanceur
 #.	**Redémarrer l'ordonnanceur** : Redémarre l'ordonnanceur afin d'appliquer les nouveaux fichiers de configuration
 #. **Commande exécutée post-génération** : Exécute la commande post-génération paramétrée au niveau de la configuration de l'ordonnanceur 
