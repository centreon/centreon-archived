======================
Ajouter un utilisateur
======================

Un utilisateur Centreon est à la fois un contact qui recevra les alertes issues
de la supervision et une personne qui pourra se connecter à l'interface web
Centreon.

Premièrement vous devez vous :ref:`connecter<centreon_login>` à l'interface
web Centreon avec un compte administrateur ou un compte disposant des droits
d'accès pour gérer les objets.

Se rendre dans le menu **Configuration > Users > Contacts / Users**
et cliquer sur le bouton **Add** :

.. image:: /_static/images/quick_start/add_user_menu.png
    :align: center

Vous accédez à un formulaire assez complet permettant de définir un utilisateur
mais pas de panique tous les champs ne sont pas nécessaires !

Le formulaire est divisé en 3 parties distinctes :

* La première partie concerne les paramètres liés à la notification
* La seconde partie, les informations pour se connecter à l'interface web Centreon
* Et la dernière pour des informations optionnelles.

Paramètres obligatoires
=======================

Dans le premier onglet **General Information** renseigner :

* votre pseudo (**Alias**), qui sera utilisé pour se connecter à l'interface web Centreon
* votre nom complet via le champ **Full Name**
* votre adresse mail via le champ **Email**

.. image:: /_static/images/quick_start/add_user_general_options.png
    :align: center

Options de notification
=======================

Pour recevoir des notifications, définir les paramètres suivants :

* Activer la notification via le champ **Enable Notifications**
* Définir les types de notification d'hôte que vous souhaitez recevoir via le champ **Host Notification Options**, par exemple : Down, Recovery, Flapping, Downtime Scheduled
* Définir la plage durant laquelle vous souhaitez recevoir vos notifications d'hôte via le champ **Host Notification Period**, par exemple : 24x7
* Définir la manière dont vous recevrez vos notifications d'hôte via le champ **Host Notification Commands**, par exemple : host-notify-by-email
* Définir les types de notification de service que vous souhaitez recevoir via le champ **Service Notification Options**, par exemple : Warning, Unknown, Critical, Recovery, Flapping, Downtime Scheduled
* Définir la plage durant laquelle vous souhaitez recevoir vos notifications de service via le champ **Service Notification Period**, par exemple : 24x7
* Définir la manière dont vous recevrez vos notifications de service via le champ **Service Notification Commands**, par exemple : service-notify-by-email

.. image:: /_static/images/quick_start/add_user_notification_options.png
    :align: center

Accès à l'interface web Centreon
================================

Pour se connecter à l'interface Centreon saisir les paramètres suivants :

* Autoriser l'accès via le champ **Reach Centreon Front-end**
* Définir votre mot de passe (**Password**) et le confirmer (**Confirm Password**)
* Définir votre fuseau horaire via le champ **Timezone / Location**
* Définir si votre compte est administrateur de la plate-forme (**Admin**) ou un simple utilisateur

.. image:: /_static/images/quick_start/add_user_access_options.png
    :align: center

Sauvegarder les modifications en cliquant sur le bouton **Save**.

.. image:: /_static/images/quick_start/add_user_list.png
    :align: center

Suivant la configuration réalisée, votre compte est prêt à recevoir des 
notifications et/ou se connecter à l'interface web Centreon.
