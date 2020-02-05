.. _contactconfiguration:

============
Les contacts
============

**********
Définition
**********

Les contacts au sein de Centreon sont utilisés afin de :

* Pouvoir se connecter à l'interface web de Centreon : chaque contact dispose de ses propres droits afin de se connecter à l'interface web.
* Etre alerté en cas de nécessité (notification).

Afin d'ajouter un contact, il suffit de se rendre dans le menu **Configuration > Utilisateurs > Ajouter**.

.. image :: /images/guide_utilisateur/configuration/06useradd.png
   :align: center 

Pour afficher la matrice de notification d'un contact, cliquez sur **Afficher les notifications du contact** (à côté du menu **Ajouter**).

**********************
Informations générales
**********************

* Le champ **Alias/Login** définit le login afin d'accéder à l'interface web.
* Le champ **Nom complet** contient le nom et prénom de l'utilisateur.
* Les champs **Mail** et **Bippeur** contiennent respectivement l'adresse mail et le numéro de téléphone de l'utilisateur (dans le cas d'une notification par SMS ou appel par exemple).
* Le champ **Modèle de contact utilisé** permet de lier le contact à un modèle de contact.
* La liste **Lié avec le groupe de contacts** associe le contact à un ou plusieurs groupes de contacts.
* Le champ **Activer les notifications** permet d'activer l'envoi de notifications pour l'utilisateur.
* Le champ **Options de notifications d'hôte/de service** permet de définir les statuts pour lesquels il y a envoi de notifications.
* Le champ **Période de notification d'hôte/de service** permet de choisir la période temporelle pour laquelle il y a envoi de notification.
* Le champ **Commande de notification d'hôte/de service** permet de choisir la commande de notification pour un hôte ou pour un service.

.. _autologin:

*************************
Authentification Centreon
*************************

* Le champ **Autoriser l'utilisateur à se connecter à l'interface web** permet d'autoriser l'utilisateur à accéder à l'interface web de Centreon.
* Les champs **Mot de passe** et **Confirmation du mot de passe** contiennent le mot de passe utilisateur.
* Le champ **Langue par défaut** permet de définir la langue de l'interface Centreon pour cet utilisateur.
* Le champ **Administrateur** définit si cet utilisateur est administrateur de la plateforme de supervision ou non.
* Le champ **Clé d'auto-connexion** permet de définir une clé de connexion pour l'utilisateur. L'utilisateur n'aura plus besoin d'entrer son login et mot de passe mais utilisera directement cette clé pour se connecter. Syntaxe de connexion : ::

    http://[IP_DU_SERVEUR_CENTRAL]/index.php?autologin=1&useralias=[login_utilisateur]&token=[valeur_autologin]

.. note:: 
    La possibilité de connexion automatique (auto login) doit être activée dans le menu **Administration > Options**.

* Le champ **Source d'authentification** spécifie si les informations de connexion proviennent d'un annuaire LDAP ou d'informations stockées localement sur le serveur.
* Le champ **Groupes de liste d'accès** permet de définir un groupe d'accès pour un utilisateur, groupe utilisé pour les contrôles d'accès (ACL).

.. note::
    Un utilisateur **Administrateur** ne peut souffrir de contrôle d'accès même lié dans un groupe d'accès.

****************************
Informations supplémentaires
****************************

* Les champs d'adresses permettent de spécifier des informations de contacts supplémentaires (autre mail, autre numéro de téléphone...).
* Les champs **Statut** et **Commentaires** permettent d'activer ou de désactiver le contact et de commenter celui-ci.

