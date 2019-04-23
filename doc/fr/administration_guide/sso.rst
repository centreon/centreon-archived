.. _sso:

*********************
Implementation du SSO
*********************

Comment fonctionne le SSO avec Centreon ?
=========================================

Voici un exemple d'architecture SSO avec LemonLDAP :

.. image:: /images/howto/SSO_architecture.png
   :align: center

1. L'utilisateur s'authentifie sur le portail SSO
2. Le portail d'authentification vérifie les droits d'accès auprès du serveur LDAP
3. Le serveur LDAP renvoie les données de l'utilisateur
4. Le portail d'authentification crée une session pour stocker les données de l'utilisateur et renvoie un cookie SSO à l'utilisateur
5. L'utilisateur est redirigé vers Centreon Web and intercepté par le handler SSO wui vérifie les droits d'accès de l'utilisateur
6. Le handler envoie une requête à Centreon Web avec l'en-tête d'authentification (ex: HTTP_AUTH_USER)
7. Centreon Web vérifie les droits d'accès auprès du serveur LDAP grâce à l'en-tête de la requête
8. Le serveur LDAP renvoie les informations d el'utilisateur
9. Centreon Web renvoie les informations au handler
10. Le handler SSO transfère les informations à l'utilisateur

Comment configurer le SSO dans Centreon ?
=========================================

Vous pouvez configurer le SSO dans le menu **Administration > Paramètres** :

.. image:: /images/howto/SSO_configuration.png
   :align: center

Pour plus d'informations, se référer :ref:`ici<centreon_parameters>`

Avertissement de sécurité
=========================

La fonctionnalité SSO doit être activée seulement dans un environnement dédié et sécurisé pour le SSO.
Les accès direct des utilisateurs à Centreon Web doivent être désactivés.

