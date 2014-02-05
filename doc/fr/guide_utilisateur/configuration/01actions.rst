==================
Actions génériques
==================

Au sein du menu **Configuration** il est possible d'effectuer certaines actions sur les différents objets.

*****************
Ajouter/Supprimer
*****************

L'ajout d'un nouvel objet se fait via l'instruction **Ajouter** à coté du menu **More actions...**.

Pour supprimer un objet :

 #.	Sélectionnez le ou les objets que vous souhaitez supprimer en cochant la ou les cases près du nom de celui-ci
 #.	Dans le menu **More actions...** cliquez sur **Supprimer**

Attention : La suppression d'un objet est définitive. Si vous avez supprimer un objet par accident, il vous faudra le recréer.
De même, la suppression d'un objet supprime automatiquement tous les objets qui sont liés à lui.
Exemple : La suppression d'un hôte entraine la suppression de tous les services associés à cet hôte.

Pour modifier un objet, cliquez sur son nom.

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

 1.	Sélectionnez l'hôte que vous souhaitez dupliquer
 2.	Dans la colonne **Options**, entrez le nombre de duplication que vous souhaitez obtenir
 
[METTRE UNE CAPTURE D'ECRAN avec le champ Options au dessus de 1] 

 3.	Dans le menu **More actions...** cliquez sur **Dupliquer**

[METTRE UNE CAPTURE D'ECRAN des objets dupliqués]

****************
Changement massif
****************

Principe
--------

Les changements massifs permettent d'appliquer un changement sur plusieurs objets.

Exemple : L'ensemble des serveurs web précèdement créés changent de communauté SNMP.
Un changement massif permet de modifier cette communauté sans avoir la peine de modifier chaque fiche de chaque hôte.

Pratique
--------

Pour effectuer un changement massif :

 #.	Sélectionnez les objets que vous souhaitez modifier
 #.	Dans le menu **More actions...** cliquez sur **Changement massif**

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

 #.	Sélectionnez les objets que vous souhaitez modifier
 #.	Dans le menu **More actions...** cliquez sur Activer/Désactiver

Il est également possible d'activer ou de désactiver un hôte via le champ Statut de l'objet (voir la fiche de l'objet).
Ou en utilisant les icônes suivantes :

 *	[METTRE L'IMAGE] pour activer
 *	[METTRE L'IMAGE] pour désactiver