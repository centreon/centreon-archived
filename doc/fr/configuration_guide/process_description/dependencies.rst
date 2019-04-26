==============================
Gérer les dépendances logiques
==============================

Vous avez vu dans le chapitre sur la configuration :ref:`des dépendances<dependancy>` comment configurer des dépendances entre objets (hôtes, services, groupes d'hôtes, ...).
Ce sous-chapitre permet d'illustrer l'utilisation de ces dépendances au travers de quelques cas concrets.

.. note::
    Les dépendances reposent sur des critères d'échec à savoir "ne pas faire si". Ne pas notifier si le service est dans un état Critique. Ne pas exécuter le contrôle si le service est dans un état Critique, d'Alerte, Inconnu, ...

***********************
Dépendance d'un service
***********************

Un service est vérifié en utilisant un scénario Sélénium.
Ce scénario se connecte à une interface web avec un identifiant et un mot de passe. Ces informations de connexions sont stockées dans une base de données MySQL.

Par conséquent, si jamais le serveur de base de données ne répond plus, alors le scénario Sélénium ne peut aboutir.
Il parait évident qu'il est nécessaire de créer un lien de dépendance logique entre le service qui utilise le scénario Sélénium et le service qui est chargé de vérifier le statut du serveur MySQL.

De plus, étant donné que le scénario Sélénium ne peut pas s'exécuter correctement, aucune donnée de performances ne peut être stockée en base de données. Il faut donc arrêter non seulement la notification pour le service utilisant le scénario Sélénium mais aussi la vérification.

Afin de créer cette dépendance :

#. Rendez-vous dans le menu **Configuration > Notifications**
#. Dans le menu de gauche en dessous de **Dépendances**, cliquez sur **Services**
#. Cliquez sur **Ajouter**
#. Entrez le nom et la description de la dépendance
#. Pour les champs **Critères d'échec d'exécution** et **Critères d'échec de notification**, cochez Alerte, Critique, Inconnu et En attente
#. Dans la liste **Service**, sélectionnez le service qui est chargé de vérifier le statut du serveur MySQL
#. Dans la liste **Services dépendants**, sélectionnez le service qui utilise le scénario Sélénium
#. Sauvegardez

A partir de ce moment, si le service chargé de vérifier le statut du serveur MySQL a un statut "Alerte", "Critique", "Inconnu" ou "En attente", alors le service chargé d'exécuter le scénario Sélénium ne sera plus exécuté jusqu'à ce que le service maître redevienne OK.

********************
Dépendance d'un hôte
********************

Prenons le cas de deux hôtes qui fonctionnent en cluster. Trois hôtes sont créés afin de pouvoir superviser ce cluster : un hôte A, un hôte B (tous les deux membres du cluster) et un hôte C (qui centralise les informations du cluster).

Si jamais, l'hôte A ou l'hôte B a un statut non-OK, alors les services de l'hôte C sera automatiquement considéré comme non-OK. Il est donc nécessaire d'ajouter une dépendance qui empêche l'envoi de notifications si jamais l'hôte A ou l'hôte B devient défaillant. Cependant, la remontée des données de performances doit toujours être fonctionnelle, c'est pourquoi il est nécessaire de continuer la supervision de l'hôte C.

Afin de créer cette dépendance :

#. Rendez-vous dans le menu **Configuration > Notifications**
#. Dans le menu de gauche en dessous de **Dépendances**, cliquez sur **Hôtes**
#. Cliquez sur **Ajouter**
#. Entrez le nom et la description de la dépendance
#. Pour le champ **Critères d'échec de notification**, cochez Alerte, Critique, Inconnu et En attente
#. Dans la liste **Nom d'hôtes**, sélectionnez l'hôte A
#. Dans la liste **Nom d'hôte liés**, sélectionnez l'hôte C
#. Sauvegardez

Répétez cette opération pour l'hôte B.

*********************************
Dépendance d'un groupe de service
*********************************

Prenons l'exemple d'un ensemble de services Oracle sur lequel se base l'application ERP. Il faut deux groupes de services :

* Le groupe Application Oracle
* Le groupe Application ERP

Si les services Oracle deviennent critiques, alors les services de l'application ERP sont automatiquement critiques.
Il est nécessaire de créer un lien de dépendance afin d'empêcher la vérification et la notification des services de l'application ERP si l'application Oracle est non-OK.

Afin de créer cette dépendance :

#. Rendez-vous dans le menu **Configuration > Notifications**
#. Dans le menu de gauche en dessous de **Dépendances**, cliquez sur **Groupes de services**
#. Cliquez sur **Ajouter**
#. Entrez le nom et la description de la dépendance
#. Pour le champ **Critères d'échec d'exécution** et **Critères d'échec de notification**, cochez Critique et En attente
#. Dans la liste **Noms des groupes de services**, sélectionnez le groupe de services **Application Oracle**
#. Dans la liste **Noms des groupes de services liés**, sélectionnez le groupe de services **Application ERP**
#. Sauvegardez
