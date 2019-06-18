.. _monitor_linux:

###################################
Superviser un serveur Linux en SNMP
###################################

Rendez-vous dans le menu **Configuration > Plugin Packs** et installez le Plugin
Pack **Linux SNMP** :

.. image:: /images/quick_start/quick_start_linux_0.gif
    :align: center

Rendez-vous maintenant dans le menu **Configuration > Hosts > Hosts** et cliquez
sur le bouton **Add** :

.. image:: /images/quick_start/quick_start_linux_1.png
    :align: center

Renseignez les informations suivantes :

* Le nom de votre server
* Une description de votre serveur
* Son adresse IP
* La communauté et la version SNMP

Cliquez sur le bouton **+ Add a new entry** pour le champ **Templates** puis
sélectionnez le modèle **OS-Linux-SNMP-custom**.

Cliquez sur le bouton **Save**.

Votre équipement a été ajouté à la configuration de la supervision :

.. image:: /images/quick_start/quick_start_linux_2.png
    :align: center

Rendez-vous dans le menu **Configuration > Services > Services by host**. Un
ensemble d'indicateurs a été déployé automatiquement :

.. image:: /images/quick_start/quick_start_linux_3.png
    :align: center

D'autres indicateurs peuvent être supervisés. Cliquez sur le bouton **Add**
pour ajouter par exemple la supervision de la bande passante d'une interface
réseau :

.. image:: /images/quick_start/quick_start_linux_4a.png
    :align: center

Dans le champ **Description**, saisissez le nom du service à ajouter, puis
sélectionner l'hôte auquel lier cet indicateur. Dans le champ **Template**
sélectionner le modèle **OS-Linux-Traffic-Generic-Name-SNMP-custom**.

Une liste de macros en correspondance avec le modèle va alors apparaître :

.. image:: /images/quick_start/quick_start_linux_4b.png
    :align: center

Saisissez le nom de votre interface pour la macro **INTERFACENAME** et cliquez
sur le bouton **Save** pour ajouter cet indicateur à la configuration.

Faites de même pour ajouter la supervision des erreurs de paquets :

.. image:: /images/quick_start/quick_start_linux_5.png
    :align: center

Ou la supervision d'une partition système :

.. image:: /images/quick_start/quick_start_linux_6.png
    :align: center

Il est maintenant temps de déployer la supervision via le
:ref:`menu dédié<deployconfiguration>`.

Rendez-vous ensuite dans le menu **Monitoring > Status Details > Services** et
sélectionnez la valeur **All** pour le filtre **Service Status**. Après quelques
minutes, les premiers résultats de la supervision apparaissent :

.. image:: /images/quick_start/quick_start_linux_7.png
    :align: center

********************
Pour aller plus loin
********************

Le Plugin Pack **Linux SNMP** apporte de nombreux modèles de supervision.
Lors de la création d'un service, il est possible de rechercher les
modèles disponibles dans la liste de sélection :

.. image:: /images/quick_start/quick_start_linux_8.png
    :align: center

Il est également possible d'accèder au menu **Configuration > Services >
Templates** pour en connaître la liste :

.. image:: /images/quick_start/quick_start_linux_9.png
    :align: center

Pour connaître le nom des partitions disponibles, vous pouvez exécuter le
plugin Centreon en ligne de commande tel quel : ::

    $  /usr/lib/centreon/plugins/centreon_linux_snmp.pl --plugin=os::linux::snmp::plugin --hostname=10.40.1.169 --snmp-community=public --snmp-version=2c --mode=list-storages
    List storage:
    Skipping storage 'Physical memory': no type or no matching filter type
    Skipping storage 'Swap space': no type or no matching filter type
    Skipping storage 'Virtual memory': no type or no matching filter type
    '/' [size = 21003583488B] [id = 31]
    '/dev/shm' [size = 1986875392B] [id = 36]
    '/run' [size = 1986875392B] [id = 38]
    '/sys/fs/cgroup' [size = 1986875392B] [id = 39]
    '/boot' [size = 1015308288B] [id = 57]
    '/var/cache/centreon/backup' [size = 5150212096B] [id = 58]
    '/var/lib/centreon-broker' [size = 5150212096B] [id = 59]
    Skipping storage 'Memory buffers': no type or no matching filter type
    '/var/lib/centreon' [size = 7264002048B] [id = 60]
    '/var/log' [size = 10434662400B] [id = 61]
    '/var/lib/mysql' [size = 16776032256B] [id = 62]
    '/run/user/0' [size = 397377536B] [id = 63]
    Skipping storage 'Cached memory': no type or no matching filter type
    Skipping storage 'Shared memory': no type or no matching filter type

Faites de même pour connaître la liste des interfaces réseau : ::

    $  /usr/lib/centreon/plugins/centreon_linux_snmp.pl --plugin=os::linux::snmp::plugin --hostname=10.40.1.169 --snmp-community=public --snmp-version=2c --mode=list-interfaces
    List interfaces:
    'lo' [speed = 10, status = up, id = 1]
    'enp0s3' [speed = 1000, status = up, id = 2]
