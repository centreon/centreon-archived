=============
Les commandes
=============

**********
Définition
**********

Une commande est la définition d'une ligne de commande qui utilise un script ou une application afin de réaliser une action.
Il est possible d'exécuter cette commande en précisant des arguments.

Il existe trois types de commandes :

*	Les commandes de **vérification** sont utilisées par les ordonnanceurs afin de vérifier le statut d'un hôte ou d'un service.
*	Les commandes de **notification** sont utilisées par les ordonnanceurs pour alerter les contacts (via mail, SMS...).
*	Des commandes **diverses** sont utilisées par les modules complémentaires (pour effectuer certaines actions), par l'ordonnanceur pour le traitement des données...

Toutes les commandes peuvent être configurées au sein du menu **Configuration** ==> **Commandes**.
 
.. image :: /images/guide_utilisateur/configuration/04commandlist.png
   :align: center 

********************
Ajouter une commande
********************

Avant d'ajouter une commande :

1.	Sélectionnez dans le menu de gauche le type de commande que vous souhaitez ajouter (Contrôles, Notifications, Divers).
 
.. image :: /images/guide_utilisateur/configuration/04leftmenu.png
   :align: center 
 
2. Cliquez sur **Ajouter**
 
.. image :: /images/guide_utilisateur/configuration/04command.png
   :align: center 

.. note::
    Les champs de configuration d'une commande sont les mêmes qu'importe le type de commande choisi.

***************************
Les champs de configuration
***************************

*	Le champ **Nom de la commande** définit le nom de la commande.
*	Le champ **Type de commande** permet de choisir le type de commande.
*	Le champ **Ligne de commande** indique l'application ou le script utilisé avec la commande.
*	La case **Activer le shell** permet d'activer des fonctions propres à un shell tel que le pipe...
*	Les champs **Exemple d'arguments** et **$HOSTADDRESS$** définissent respectivement des exemples d'arguments (chaque argument commence par un "!") et une adresse IP de test.
	Ces champs permettent d'exécuter la ligne de commande définie au-dessus via l'interface web en cliquant sur la flèche bleue : |bluearrow|.
*	Le bouton **Description des arguments** permet d'ajouter une description aux arguments de type "$ARGn$". Cette description sera visible lors de l'utilisation de la commande dans un formulaire d'hôte ou de service.
 
.. image :: /images/guide_utilisateur/configuration/04linkbetweencommandandservices.png
   :align: center 
 
*	Le bouton **Effacer les arguments** efface la description des arguments définie
*	Le champ **Connecteurs** permet de lier un connecteur à la commande. Pour davantage d'informations sur les connecteurs reportez-vous aux chapitres `Documentation connecteurs Perl <http://documentation.centreon.com/docs/centreon-perl-connector/en/latest/>`_ et `Documentation connecteurs SSH <http://documentation.centreon.com/docs/centreon-ssh-connector/en/latest/>`_.
*	Le champ **Modèle de graphique** permet de lier la commande à un modèle de graphique.
*	Le champ **Commentaire** permet de commenter la commande.

*******************
Arguments et macros
*******************

Au sein du champ **Ligne de commande**, il est possible de faire appel à des :ref:`macros<macros>` ainsi qu'à des arguments.

Les arguments sont utilisés afin de pouvoir passer différents paramètres aux scripts appelés par les commandes. 
Lors de l'exécution de la commande par l'ordonnanceur, chacun des arguments et macros sont remplacés par leur valeur respective.
Chaque argument se présente sous la forme **$ARGn$** où n est un entier naturel supérieur à 0.

Exemple de ligne de commande utilisant les arguments : $USER1$/check-bench-process-DB -w $ARG1$ -c $ARG2$ -n $ARG3$

.. note::
	La bonne pratique veut que nous remplacions les arguments par des :ref:`macros personnalisées<custommacros>`.

.. |bluearrow|    image:: /images/bluearrow.png
