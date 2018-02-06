.. _deployconfiguration:

==========================
Déployer une configuration
==========================

*********
Procédure
*********

Lors de la création/suppression/modification des objets via l'interface de configuration, les changements effectués ne sont pas appliqués de manière automatique aux serveurs de supervision.
Afin de pouvoir appliquer les modifications effectuées, il est nécessaire de suivre la procédure suivante ci-dessous.

Première étape
==============

#. Rendez-vous dans le menu **Configuration** ==> **Collecteurs**
#. Choisissez les collecteurs sur lesquels exporter la configuration
#. Cliquez sur **Appliquez la configurartion**

.. image:: /images/guide_utilisateur/configuration/poller_menu_generate.png
    :align: center

#. Cochez les cases **Générer les fichiers de configuration** et **Lancer le débogage du moteur de supervision (-v)**
#. Cliquez sur **Exporter**
 
.. image:: /images/guide_utilisateur/configuration/poller_generate_1.png
   :align: center 

Vérifier qu'aucune erreur n'apparait lors de la génération. 

.. note::
    Si cela est le cas, corriger les erreurs et refaire la première étape.

Deuxième étape
==============
 #.	Décochez les cases **Générer les fichiers de configuration** et **Lancer le débogage du moteur de supervision (-v)**
 #.	Puis cochez les cases **Déplacer les fichiers générés** ainsi que **Redémarrer l'ordonnanceur**
 #.	Cliquez sur **Exporter**
 
.. image:: /images/guide_utilisateur/configuration/poller_generate_2.png
   :align: center 

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
