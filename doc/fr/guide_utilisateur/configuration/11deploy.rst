==========================
Déployer une configuration
==========================

*********
Procédure
*********

Lors de la création/suppression/modification des objets via l'interface de configuration, les changements effectués ne sont pas appliqués de manière automatique aux serveurs de supervision.
Afin de pouvoir appliquer les modifications effectuées, il est nécessaire de suivre la procédure suivante :

 #.	Rendez-vous dans **Configuration** ==> **Moteurs de supervision**
 #.	Le champ **Collecteur** vous permet de choisir le serveur de supervision (central ou serveur satellite) sur lequel vous souhaitez exporter la configuration
 #.	Laissez les options par défaut, et cliquez sur **Exporter**
 #.	Décochez **Générer les fichiers de configuration** et **Lancer le débogage du moteur de supervision (-v)**
 #.	Cochez **Déplacer les fichiers générés** ainsi que **Redémarrer l'ordonnanceur**
 #.	Cliquez à nouveau sur **Exporter**
 
[METTRE UNE CAPTURE D'ECRAN LORSQUE L'EXPORTATION EST TERMINEE]

************
Explications
************

Au sein de la page de génération de la configuration, plusieurs options sont disponibles :

 #.	**Générer les fichiers de configuration** : Génère les fichiers de configuration de l'ordonnanceur dans un répertoire temporaire. Cette configuration est générée à partir des objets configurés via l'interface web
 #.	**Lancer le débogage du moteur de supervision (-v)** : Permet à l'ordonnanceur de vérifier la configuration générée
 #.	**Déplacer les fichiers générés** : Déplace les fichiers de configuration du répertoire temporaire vers le répertoire de l'ordonnanceur
 #.	**Redémarrer l'ordonnanceur** : Redémarre l'ordonnanceur afin d'appliquer les nouveaux fichiers de configuration