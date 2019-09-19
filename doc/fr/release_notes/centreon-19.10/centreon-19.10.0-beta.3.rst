===========================
Centreon Web 19.10.0-beta.3
===========================

New features
------------

* Add Keycloak SSO authentication in Centreon (PR/#7700)

Enhancements
------------

* [Remote-Server] Allow to use direct ssh connection to poller from central (PR/#7680)

Performance
-----------

Bug fixes
---------

* [API] Delete services when host template is detached from host (PR/#7784)
* [Doc] Fix typo Centreon word (and one variable) (PR/#7796)

Technical
---------

* [Configuration] Move filesGeneration directory to /var/cache/centreon (PR/#7735)

Known issue
-----------

* [logs] Correction de la limitation de la valeur maximale pour la clé primaire de la table centreon_storage.logs (:ref:`update_centreon_storage_logs`)
