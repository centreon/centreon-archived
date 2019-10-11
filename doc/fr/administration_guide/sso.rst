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

OpenID Connect 1.0 & OAuth 2.0
==============================

Pour utiliser ces prococoles il faut installer et activer les modules Apaches suivants :
* mod_auth_openidc : https://github.com/zmartzone/mod_auth_openidc
* mod_oauth2 : https://github.com/zmartzone/mod_oauth2

Des exemples de configurations sont disponibles dans la  documentation et sont à appliquer sur le vhost de votre Centreon web.
```
#exemple
<VirtualHost *:80>
    ServerName myserver.com

    <Location />
        AuthType openid-connect
        Require valid-user
    </Location>

    OIDCProviderMetadataURL https://openid.com/fss/.well-known/openid-configuration
    OIDCClientID MY-Centreon
    OIDCClientSecret abcdefghijklmnop
    OIDCProviderTokenEndpointAuth client_secret_post
    OIDCRedirectURI https://myserver.com/ssoredirect
    OIDCScope "openid profile"
    OIDCCryptoPassphrase mypassphrase
    OIDCAuthNHeader MY_HEADER
    OIDCRemoteUserClaim sub
    OIDCClaimPrefix myprefix_

    ProxyPreserveHost on
    ProxyPass / http://10.10.10.10/
    ProxyPassReverse / http://10.10.10.10/

</VirtualHost>
```




