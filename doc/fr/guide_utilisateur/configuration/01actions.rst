==================
Actions génériques
==================

Au sein du menu **Configuration** il est possible d'effectuer certaines actions sur les différents objets.

*****************
Ajouter/Supprimer
*****************

L'ajout d'un nouvel objet se fait via l'instruction **Ajouter** à coté du menu **More actions...**.

Pour supprimer un objet :

 *	Sélectionnez le ou les objets que vous souhaitez supprimer en cochant la ou les cases près du nom de celui-ci
 *	Dans le menu **More actions...** cliquez sur **Supprimer**

Attention : La suppression d'un objet est définitive. Si vous avez supprimer un objet par accident, il vous faudra le recréer.
De même, la suppression d'un objet supprime automatiquement tous les objets qui sont liés à lui.
Exemple : La suppression d'un hôte entraine la suppression de tous les services associés à cet hôte.

*********
Dupliquer
*********

Principe
--------

La duplication d'un objet permet de copier celui-ci afin de pouvoir réutiliser ses attributs pour la création d'un nouvel objet.
Exemple : J'ai 10 serveurs web identiques à superviser :

 *	J'ajoute le premier serveur web avec tous les attributs nécessaires
 *	Je duplique cet hôte en 9 fois
 *	Je n'ai plus qu'à changer les noms d'hôtes et les adresses IP de chaque duplication pour les adapter au 9 autres serveurs web à superviser

Grâce à cette méthode, je n'ai plus la peine de recréer chaque hôte.

Pratique
--------

Pour dupliquer un hôte :

 *	Sélectionnez l'hôte que vous souhaitez dupliquer
 *	Dans la colonne **Options**, entrez le nombre de duplication que vous souhaitez obtenir

[METTRE UNE CAPTURE D'ECRAN avec le champ Options au dessus de 1]

 *	Dans le menu **More actions...** cliquez sur **Dupliquer**

[METTRE UNE CAPTURE D'ECRAN des objets dupliqués]

****************
Changement massif
****************

Principe
--------

Les changements massifs permettent d'appliquer un changement sur plusieurs objets.
Exemple : L'ensemble des serveurs web précèdement créé changent de communauté SNMP.
Un changement massif permet de modifier cette communauté sans avoir la peine de modifier chaque fiche de chaque hôte.

Pratique
--------

Pour effectuer un changement massif :

 *	Sélectionnez les objets que vous souhaitez modifier
 *	Dans le menu **More actions...** cliquez sur **Changement massif**

La fenêtre de changement s'ouvre, il existe deux types de changements :

 *	Incrémentale signifie que la modification va s'ajouter aux options déjà existante
 *	Remplacement signifie que la modification va écraser les options déjà existantes

******************
Activer/Désactiver
******************

Principe
--------

L'activation et la désactivation des objets permet de prendre en compte ou non l'objet lors de la génération de la configuration.
Le principal intérêt est de pouvoir garder la configuration d'un objet sans pour autant l'appliquer.

Pratique
--------

Pour activer/désactiver un objet :

 *	Sélectionnez les objets que vous souhaitez modifier
 *	Dans le menu **More actions...** cliquez sur Activer/Désactiver

**************************
Déployer une configuration
**************************

Procédure
---------

Lors de la création/suppression/modification des objets via l'interface de configuration, les changements effectués ne sont pas appliqués de manière automatique aux serveurs de supervsion.
Afin de pouvoir appliquer les modifications effectuées, il est nécessaire de suivre la procédure suivante :

 *	Rendez-vous dans **Configuration** ==> **Moteurs de supervision**
 *	Le champ **Collecteur** vous permet de choisir le serveur de supervision (central ou serveur satellite) sur lequel vous souhaitez exporter la configuration
 *	Laissez les options par défaut, et cliquez sur **Exporter**
 *	Décochez **Générer les fichiers de configuration** et **Lancer le débogage du moteur de supervision (-v)**
 *	Cochez **Déplacer les fichiers générés** ainsi que **Redémarrer l'ordonnanceur**
 *	Cliquez à nouveau sur **Exporter**

Explications
------------

Au sein de la page de génération de la configuration, plusieurs options sont disponibles :

 *	**Générer les fichiers de configuration** : Génère les fichiers de configuration de l'ordonnanceur dans un répertoire temporaire. Cette configuration est générée à partir des objets configurés via l'interface web
 *	**Lancer le débogage du moteur de supervision (-v)** : Permet à l'ordonnanceur de vérifier la configuration générée
 *	**Déplacer les fichiers générés** : Déplace les fichiers de configuration du répertoire temporaire vers le répertoire de l'ordonnanceur
 *	**Redémarrer l'ordonnanceur** : Redémarre l'ordonnanceur afin d'appliquer les nouveaux fichiers de configuration