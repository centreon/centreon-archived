=============
Les commandes
=============

**********
Définition
**********

Une commande est une ligne de commande qui utilise un script ou une application afin de réaliser une action.
Il est possible de passer des arguments à une commande.

Il existe trois types de commandes :

 *	Les commandes de vérification sont utilisées par les ordonnanceurs afin de vérifier le statut d'un hôte ou d'un service
 *	Les commandes de notification sont utilisées par les ordonnanceurs pour alerter les contacts (via email, SMS...)
 *	Des commandes diverses sont utilisées par les modules complémentaires (pour effectuer certaines actions), par l'ordonnanceur pour le traitement des données...

Toutes les commandes peuvent être configurées au sein de **Configuration** ==> **Commandes**.
 
[METTRE UNE CAPTURE D'ECRAN]

********************
Ajouter une commande
********************

Avant d'ajouter une commande :

 1.	Sélectionnez dans le menu de gauche le type de commande que vous souhaitez ajouter
 
[METTRE UNE CAPTURE D'ECRAN DU MENU]
 
 2. Cliquez sur **Ajouter**
 
[METTRE UNE CAPTURE D'ECRAN DE LA CONFIGURATION D'UNE COMMANDE]

Notez bien : Les champs de configuration d'une commande sont les mêmes qu'importe le type de commande choisi.

***************************
Les champs de configuration
***************************

 *	Le champ **Nom de la commande** définit le nom de la commande
 *	Le champ **Type de commande** permet de choisir le type de commande
 *	Le champ **Ligne de commande** indique l'application ou le script utilisé avec la commande
 *	La case **Activer le shell** permet d'activer des fonctions propres à un shell tel que le pipe...
 *	Les champs **Exemple d'arguments** et **$HOSTADDRESS$** définissent respectivement des exemples d'arguments (chaque argument commence par un "!") et une adresse IP de test.
	Ces champs permettent d'exécuter la ligne de commande définie au dessus via l'interface web en cliquant sur [METTRE UNE IMAGE]
 *	Le bouton **Description des arguments** permet de décrire les arguments définis dans le champ **Ligne de commande**. Cette description sera visible lors de l'utilisation de la commande
 
[METTRE UNE CAPTURE D'ECRAN faisant le lien entre la description des arguments et la définition des arguments dans une fiche de service)
 
 *	Le bouton **Effacer les arguments** efface les arguments définis
 *	Le champ **Connecteurs** permet de lié un connecteur à la commande. Pour davantage d'informations sur les connecteurs rendez-vous dans `Documentation connecteurs Perl <http://documentation.centreon.com/docs/centreon-perl-connector/en/latest/>`_ et `Documentation connecteurs SSH <http://documentation.centreon.com/docs/centreon-ssh-connector/en/latest/>`_
 *	Le champ **Modèle de graphique** permet de lier la commande à un modèle de graphique
 *	Le champ **Commentaire** permet de commenter la commande

*******************
Arguments et macros
*******************

Au sein du champ **Ligne de commande**, il est possible de faire appel à des macros (voir le sous-chapitre dédié aux macros) ainsi qu'à des arguments.

Les arguments sont utilisés afin de pouvoir passer différents paramètres aux scripts appelés par les commandes. 
Puis, lors de l'utilisation d'une commande de vérification pour la création d'un service ou d'un hôte la valeur de chaque argument est définie.
Chaque argument se présente sous la forme **$ARGn$** où n est un entier naturel supérieur à 0.

Exemple de ligne de commande utilisant les arguments : $USER1$/check-bench-process-DB -w $ARG1$ -c $ARG2$ -n $ARG3$

**La bonne pratique veut que nous remplaçions les arguments par des macros personnalisées (voir le sous-chapitre dédié aux macros).**
