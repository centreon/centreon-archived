.. _centreon_parameters:

===================================================
Options d'administration de la plate-forme Centreon
===================================================

Les options suivantes permettent de modifier les paramètres de l'architecture Centreon.

********
Centreon
********

Cette partie traite de la configuration des options générales de l'interface web Centreon.

#. Rendez-vous dans le menu **Administration > Paramétres**
#. Dans le menu de gauche, cliquez sur **Centreon UI**
#. Cliquez sur **Centreon**

La fenêtre suivante s'affiche :

.. image :: /images/guide_exploitation/ecentreon.png
   :align: center

* Le champ **Répertoire** désigne le répertoire dans lequel Centreon est installé
* Le champ **Répertoire Web de Centreon** indique le répertoire web sur lequel est installé Centreon
* Le champ **Limite par page (par défaut)** définit le nombre d'objet affiché par page de **Configuration**
* Le champ **Limite par page pour les pages de supervision** définit le nombre d'objet affiché par page au sein du menu **Supervision**
* Le champ **Graphique de performance par page** définit le nombre maximum de graphiques affichés sur la page de *Performances**
* Le champ **Nombre d'éléments présent** définit le nombre maximum d'éléments affichés dans chaque boîte de sélection
* Le champ **Durée d'expiration de la session**, exprimé en minutes, indique la durée maximale d'une session
* Le champ **Intervalle de rafraîchissement pour la page des statistiques**, exprimé en secondes, indique l'intervalle de rafraîchissement pour les objets de la page des statistiques
* Le champ **Intervalle de rafraîchissement pour la page de supervision**, exprimé en secondes, indique l'intervalle de rafraîchissement pour les objets de la page supervision
* Le champ **Trier par** indique le tri par défaut pour les pages de supervision des hôtes et des services.
* Le champ **Choix de tri** indique l'ordre par défaut de tri pour les pages de supervision des services et des hôtes.
* Le champ **Trier les problèmes par** permet de choisir comment trier les différents incidents dans le menu **Supervision**
* La champ **Ordre de tri des problèmes** indique l'ordre d'affichage des incidents par ordre de gravité croissant ou décroissant
* Le champ **Afficher les temps d'arrêts et les acquittements sur les graphiques** permet d'afficher ou non ces éléments
* Le champ **Afficher les comentaires sur les graphiques** permet d'afficher ou non ces éléments
* La case **Activer la connexion automatique** autorise les utilisateurs à se connecter à l'interface web via le mécanisme de connexion automatique
* La case **Afficher le raccourci de connexion automatique** permet d'afficher le raccourci de connexion en haut à droite
* La case **Activer l'authentification SSO** active l'authentification SSO
* Le champ **Mode SSO** indique si l'authentification doit avoir lieu uniquement par SSO ou bien en utilisant l'authentification locale également (Mixte). Le mode mixte nécessite l'adresse des clients de confiance.
* Le champ **Adresses des clients SSO de confiance** indique quelles sont les adresses IP/DNS des clients de confiance pour le SSO (correspond à l'adresse du reverse proxy). Chaque client de confiance est séparé par une virgule.
* Le champ **Adresses des clients de bloqués** indique quelles sont les adresses IP/DNS des clients qui seront refusés.
* Le champ **Entête HTTP SSO** indique la variable de l'en-tête qui sera utilisée comme login/pseudo.
* Le champ **Chaine de recherche (pattern) pour l'authentification (login)** indique l'expression rationnelle (pattern) de recherche pour l'utilisateur.
* Le champ **Chaine de remplacement (pattern) pour l'authentification (login)** indique la chaine de remplacement.
* Le champ **Timezone par défaut de l'hôte** permet de définit un timezone par défaut pour application du décalage horaire
* Le champ **Adresse mail de contact du support (de la plate-forme de supervision)** indique l'adresse email de support **Centre des services du client** pour la plate-forme Centreon. Cette adresse mail sera affichée en bas de page sur le lien **Centre des services**

.. warning::
    La fonctionnalité SSO doit être activée seulement dans un environnement dédié et sécurisé pour le SSO. Les accès direct des utilisateurs à Centreon Web doivent être désactivés.

.. _impproxy:

Configuration du proxy
----------------------

La configuraiton du proxy est nécessaire pour bénéficier de l'offre Centreon IMP.

Renseigner les différents champs:

* **URL du proxy web**
* **Port d'accès au proxy internet**
* **Proxy user**
* **Proxy password**

.. image:: /_static/images/adminstration/proxy_configuration.png
    :align: center

Une fois vos paramètres saisi, testez votre configuration en cliquant sur le
bouton **Text Proxy Configuration**. Si votre configuration est correcte, un
message indiquera la réussite :

.. image:: /_static/images/adminstration/proxy_configuration_ok.png
    :align: center

***********
Supervision
***********

Cette partie traite des options générales de l'interface de supervision temps réel.

#. Rendez-vous dans le menu **Administration > Paramétres**
#. Dans le menu de gauche, cliquez sur **Supervision**

.. image :: /images/guide_exploitation/esupervision.png
   :align: center

* Le champ **Unité de temps de référence** indique l'intervalle de temps en seconde utilisé pour planifier les contrôles et les notifications
* Le champ **Répertoire des images** définit le répertoire d'image dans lequel sont stockés les médias
* Le champ **Répertoire des sondes** définit le répertoire où se situent les sondes de supervision
* Le champ **Script de démarrage du broker** contient le chemin vers le script de démarrage du broker
* Le champ **Chemin complet de l'exécutable** contient le chemin vers l'exécutable permettant d'envoyer des mails
* Les listes **Nombre maximum d'hôtes à afficher** et **Nombre maximum de services à afficher** contiennent le nombre maximal d'hôte ou de services à afficher dans la vue d'ensemble (menu **Accueil > Accueil**)
* Le champ **Intervalle de rafraîchissement de la page** définit l'intervalle de rafraîchissement des données dans la vue d'ensemble
* Les cases contenues dans les catégories **Options d'acquittement par défaut** et **Options de temps d'arrêt par défaut** définissent les options par défaut qui seront cochées ou non lors de la définition d'un acquittement ou d'un temps d'arrêt
* Le champ **Durée** permet de définir la durée par défaut d'un temps d'arrêt

********
CentCore
********

Cette partie permet de paramétrer le fonctionnement du processus CentCore.

#. Rendez-vous dans le menu **Administration > Paramétres**
#. Dans le menu de gauche, cliquez sur **Centcore**

.. image :: /images/guide_exploitation/ecentcore.png
   :align: center

* Le champ **Activer la récupération des statistiques de Centreon Broker** active la récupération des statistiques de Centreon Broker par CentCore. Cette option peut être bloquante car la lecture du tuyau (pipe) peut être une action bloquante
* Le champ **Temps maximum d'exécution des commandes Centcore** permet de définir un temps de d'exécution maximal des commandes locales et via SSH du processus
* Le champ **Caractères illégaux pour les commandes Centcore** permet de définir les caractères non autorisés qui seront supprimés des commandes transférées par le processus.

.. _ldapconfiguration:

****
LDAP
****

.. note::
    Si vous souhaitez implémenter une authentification SSO, suivez cette :ref:`procédure <sso>`.
    Vous pouvez également utiliser le SSO Keycloak en suivant cette :ref:`procédure <keycloak>`.

Cette partie permet de configurer la connexion au(x) annuaire(s) LDAP.

Pour ajouter un nouvel annuaire :

#. Rendez-vous dans le menu **Administration > Paramétres**
#. Dans le menu de gauche, cliquez sur **LDAP**
#. Cliquez sur **Ajouter**

.. image:: /images/guide_exploitation/eldap.png
   :align: center

* Les champs **Nom de la configuration** et **Description** définissent le nom et la description du serveur LDAP
* Le champ **Activer l'authentification LDAP** permet d'activer l'authentification à travers le serveur LDAP
* Le champ **Sauvegarde du mot de passe LDAP** permet de stocker le mot de passe des utilisateurs en base de données, utile en cas de perte de connexion avec l'annuaire pour authentifier les utilisateurs
* Le champ **Import automatiques des utilisateurs** permet d'importer automatiquement les utilisateurs de l'annuaire LDAP dans Centreon. En cliquant sur **Importer les utilisateurs manuellement**, vous pouvez choisir les utilisateurs que vous souhaitez importer

.. note::
    Si l'option **Import automatiques des utilisateurs** est cochée, alors pour toute nouvelle personne qui se connecte à l'interface Centreon, ses paramètres LDAP seront automatiquement importés dans Centreon (nom, prénom, adresse mail, ...). Les profils ACL seront appliqués lors de l'accès (Lien vers :ref:`Les ACLs <acl>`). Par contre, si cette option n'est pas cochée, seuls les utilisateurs importés manuellement pourront s'authentifier.

* Le champ **Taille limite de la recherche LDAP** permet de limiter la taille de la recherche des utilisateurs
* Le champ **Temps maximum d'exécution de la recherche LDAP** permet de définir le temps maximum de la recherche LDAP
* Le champ **Modèle de contact** définit le modèle de contact qui sera lié pour tous les utilisateurs importés depuis cet annuaire LDAP
* Le champ optionnel **Groupe de contacts par défaut** permet d'ajouter à un groupe de contact les contacts importés
* Le champ **Utiliser le service DNS** indique s'il faut utiliser le serveur DNS pour résoudre l'adresse IP de l'annuaire LDAP
* Le champ **LDAP servers** permet d'ajouter un ou plusieurs annuaires LDAP vers lequel Centreon va se connecter.

.. image:: /images/guide_exploitation/eldap2.png
    :align: center

* Lorsque l'option **Synchronisation LDAP lors du login** est activée, une mise à jour des données de l'utilisateur provenant du LDAP sera effectuée lors de sa connection et ses ACL seront re-calculées.
* Le champ **Intervalle (en heures), entre les synchronisations LDAP** est affiché si la précedente option est activée. Il permet de spécifier une durée minimale entre deux synchronisation avec le LDAP.

.. note::
   Les données provenant du LDAP ne seront mises à jour que lorsque cet intervalle sera écoulé. Une synchronisation manuelle est possible sur les pages **Administration > Sessions** et **Configuration > Utilisateurs > Contacts / Utilisateurs**.

   L'intervalle est exprimé en heures. Par défaut, ce champs est initié avec la plus basse valeur possible : 1 heure.

.. note::
   Nous sauvegardons en DB, un timestamp comme date de référence et c'est le CRON CentAcl qui le met à jour.

   Cette référence temporelle permet de calculer la prochaine synchronisation avec le LDAP.

   Si vous modifiez l'un de ces deux champs, la base temporelle sera réinitialisée à l'heure de la sauvegarde du formulaire.

   Cette reférence temporelle n'est pas affectée par les modifications apportées sur les autres champs du formulaire.

.. image:: /images/guide_exploitation/eldap3.png
    :align: center

Le tableau ci-dessous résume les différents paramètres à insérer pour ajouter un serveur LDAP :

+-------------------------+------------------------------------------------------------------------------------------------------------+
|   Colonne               |  Description                                                                                               |
+=========================+============================================================================================================+
| Adresse du serveur      | Contient l'adresse IP ou nom DNS du serveur LDAP                                                           |
+-------------------------+------------------------------------------------------------------------------------------------------------+
| Port                    | Indique le port de connexion pour accéder à l'annuaire LDAP                                                |
+-------------------------+------------------------------------------------------------------------------------------------------------+
| SSL                     | Indique si le protocole SSL est utilisé pour la connexion au serveur                                       |
+-------------------------+------------------------------------------------------------------------------------------------------------+
| TLS                     | Indique si le protocole TLS est utilisé pour la connexion au serveur                                       |
+-------------------------+------------------------------------------------------------------------------------------------------------+

.. image:: /images/guide_exploitation/eldap4.png
    :align: center

* Les champs **Utilisateur du domaine** et **Mot de passe** définissent le nom d'utilisateur et le mot de passe pour se connecter au serveur LDAP
* Le champ **Version du protocole** indique la version du protocole à utiliser pour se connecter
* La liste **Modèle** permet de préconfigurer les filtres de recherches des utilisateurs sur l'annuaire LDAP.
  Ces filtres permettent de proposer par défaut une recherche sur un annuaire de type MS Active Directory, Okta ou de type Posix.

.. note::
    Avant tout import, vérifiez les paramètres par défaut proposés. Si vous n'avez sélectionné aucun modèle, vous devez définir manuellement les filtres de recherches en complétant les champs.

.. note::
    Il est possible d'utiliser l'annuaire **Okta** avec le `plugin SWA <https://help.okta.com/en/prod/Content/Topics/Apps/Apps_Configure_Template_App.htm>`_:
    
    * le champ **Utilisateur du domaine** est du type **uid=<USER>,dc=<ORGANIZATION>,dc=okta,dc=com**
    * et le champ **Base de recherche de groupe DN** du type **ou=<OU>,dc=<ORGANIZATION>,dc=okta,dc=com**

Sous CentOS 7, on peut définir de ne pas vérifier le certificat serveur avec la procédure suivante:

Ajouter la ligne suivante dans le fichier "/etc/openldap/ldap.conf": ::

  TLS_REQCERT never

Puis redémarrez le serveur Apache : ::

  # systemctl restart httpd24-httpd

*******
RRDTool
*******

Cette partie permet de configurer le moteur de génération des graphiques RRDTool.
Rendez-vous dans le menu **Administration > Paramétres > RRDTool**

.. image :: /images/guide_exploitation/errdtool.png
   :align: center

* Le champ **Chemin complet de l'exécutable RRDTOOL** définit le chemin vers l'exécutable RRDTool
* Le champ **Version de RRDTool** permet de connaître la version de RRDTool
* Le champ **Activer RRDCached** permet d'activer le processus RRDcached
* Le champ **Port TCP** définit le port sur lequel écoute RRDcached
* Le champ **Chemin d'accès au socket Unix** définit le chemin vers le socket Unix

.. warning::
    N'activez RRDCacheD que si votre plate-forme de supervision rencontre de trop
    nombreux accès disques concernant l'écriture des données dans les fichiers RRD.
    Ne choississez qu'une option (TCP ou socket Unix).

********
Debogage
********

Cette partie permet de configurer l'activation de la journalisation de l'activité des processus Centreon.

#. Rendez-vous dans le menu **Administration > Paramétres**
#. Dans le menu de gauche, cliquez sur **Débogage**

.. image:: /images/guide_exploitation/edebug.png
   :align: center

* Le champ **Répertoire d'enregistrement des journaux** définir le chemin où seront enregistrés les journaux d'évènements
* La case **Enregistrer les authentifications** permet de journaliser les authentifications à l'interface Centreon
* La case **Débogage du moteur de supervision** active la journalisation du débogage de l'ordonnanceur
* La case **Débogage RRDTool** active la journalisation du débogage du moteur de graphique RRDTool
* La case **Débogage de l'import d'utilisateurs LDAP** active la journalisation du débogage de l'import des utilisateurs LDAP
* La case **Enregistrer les requêtes SQL** active la journalisation des requêtes SQL exécutées par l'interface Centreon
* La case **Débogage processus Centcore** active la journalisation du débogage du processus Centcore
* La case **Débogage du processus Centstorage** active la journalisation du débogage du processus Centstorage
* La case **Débogage du moteur de traitement des traps SNMP (centreontrapd)** active la journalisation du débogage du processus Centreontrapd
