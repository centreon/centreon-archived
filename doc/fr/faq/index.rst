===================
Foire Aux Questions
===================

**************
Administration
**************

Comment la fonction **Supprimer des graphiques** fonctionne ?
=============================================================

Afin de préserver les performances globales, cette action ne supprime pas toutes
les données de la base de données juste après son lancement. Les entrées seront
retirées des tables **index_data** et **metrics** mais pas de la table **data_bin**.

La principale raison est que la table **data_bin** stocke rapidement une énorme quantité
de données et utilise le moteur MyISAM qui ne prend pas en charge le verrouillage par 
ligne Si vous essayez de supprimer trop d'entrées simultanément, vous pourriez bloquer 
toute votre base de données pendant plusieurs heures.

Quoi qu'il en soit, cela ne signifie pas que les données resteront dans votre base de données
indéfiniment. Elles seront supprimée plus tard, en fonction de votre politique de rétention
des données programmée.conservation.

Aucun graphique semble être généré, que dois-je contrôler?
==========================================================

Il ya plusieurs choses à vérifier lorsque les RRDs ne semblent pas être générés.

Espace disque
-------------

Par défaut, les fichiers contenant les graphiques (.rrd) sont stockés dans le
répertoire **/var/lib/centreon/metrics**. Il est évidemment nécessaire de disposer 
d'assez d'espace sur votre système de fichiers.

Permissions
-----------

Est-ce que les fichiers contenant les graphiques (.rrd) peuvent être écrit dans le 
répertoire **/var/lib/centreon/metrics** ?
Le processus qui écrit dans ce répertoire est soit **cdb** soit **centstorage**.

Plugins
-------

Est-ce que vos plugin génère correctement les ddonnées de performance ?
Se référer à la :ref:`documentation officielle <centreon-engine:centengine_plugin_api>` 
pour plus d'informations. 

Si vous utilisez NDOUtils
-------------------------

Vérifier que le processus **centstorage** est e ncours d'exécution :

::

  $ /etc/init.d/centstorage status
  centstorage (pid  30276) is running...


Le chemin d'accès vers **service-perfdata** de votre collecteur doit être 
correctement configuré dans le menu **Configuration ==> Centreon**

De plus ce chemin d'accès doit correspondre avec celui utilisé par les sondes :

::

  $ head -43 plugin_path/process-service-perfdata | tail -1
  PERFFILE="/var/log/centreon-engine/service-perfdata"


Si vous utilisez Centreon Broker
--------------------------------

Centreon Broker doit être configuré correctement. Se référer à la 
:ref:`documentation de configuration <centreon_broker_wizards>` pour plus d'informations.

Le démon cbd rrd doit être en cours d'exécution :

::

  $ /etc/init.d/cbd status
  * cbd_central-rrd is running

Assurez-vous d'avoir correctement rempli le paramètre **Script de démarrage du broker**
dans le menu **Administration ==> Options ==> Monitoring**.
