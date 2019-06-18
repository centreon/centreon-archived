.. _monitor_windows:

#####################################
Superviser un serveur Windows en SNMP
#####################################

Rendez-vous dans le menu **Configuration > Plugin Packs** et installez le Plugin
Pack **Windows SNMP** :

.. image:: /images/quick_start/quick_start_windows_0.gif
    :align: center

Rendez-vous maintenant dans le menu **Configuration > Hosts > Hosts** et cliquez
sur le bouton **Add** :

.. image:: /images/quick_start/quick_start_windows_1.png
    :align: center

Renseignez les informations suivantes :

* Le nom de votre server
* Une description de votre serveur
* Son adresse IP
* La communauté et la version SNMP

Cliquez sur le bouton **+ Add a new entry** pour le champ **Templates** puis
sélectionnez le modèle **OS-Windows-SNMP-custom**.

Cliquez sur le bouton **Save**.

Votre équipement a été ajouté à la configuration de la supervision :

.. image:: /images/quick_start/quick_start_windows_2.png
    :align: center

Rendez-vous dans le menu **Configuration > Services > Services by host**. Un
ensemble d'indicateurs a été déployé automatiquement :

.. image:: /images/quick_start/quick_start_windows_3.png
    :align: center

D'autres indicateurs peuvent être supervisés. Cliquez sur le bouton **Add**
pour ajouter par exemple la supervision de la partition C :

.. image:: /images/quick_start/quick_start_windows_4a.png
    :align: center

Dans le champ **Description**, saisissez le nom du service à ajouter, puis
sélectionner l'hôte auquel lier cet indicateur. Dans le champ **Template**
sélectionner le modèle **OS-Windows-Disk-Generic-Name-SNMP-custom**.

Une liste de macros en correspondance avec le modèle va alors apparaître :

.. image:: /images/quick_start/quick_start_windows_4b.png
    :align: center

Saisissez le nom de votre partition pour la macro **DISKNAME**, ajoutez la
valeur **--regexp** pour la macro **EXTRAOPTIONS** afin de ne pas donner le
nom complet de la partition et cliquez sur le bouton **Save** pour ajouter cet
indicateur à la configuration.

Faites de même pour ajouter la supervision de la bande passante des interfaces
réseau :

.. image:: /images/quick_start/quick_start_windows_5.png
    :align: center

Il est maintenant temps de déployer la supervision via le
:ref:`menu dédié<deployconfiguration>`.

Rendez-vous ensuite dans le menu **Monitoring > Status Details > Services** et
sélectionnez la valeur **All** pour le filtre **Service Status**. Après quelques
minutes, les premiers résultats de la supervision apparaissent :

.. image:: /images/quick_start/quick_start_windows_6.png
    :align: center

********************
Pour aller plus loin
********************

Le Plugin Pack **Windows SNMP** apporte de nombreux modèles de supervision.
Lors de la création d'un service, il est possible de rechercher les
modèles disponibles dans la liste de sélection :

.. image:: /images/quick_start/quick_start_windows_7.png
    :align: center

Il est également possible d'accèder au menu **Configuration > Services >
Templates** pour en connaître la liste :

.. image:: /images/quick_start/quick_start_windows_8.png
    :align: center

Pour connaître le nom des partitions disponibles, vous pouvez exécuter le
plugin Centreon en ligne de commande tel quel : ::

    $ /usr/lib/centreon/plugins/centreon_windows_snmp.pl --plugin=os::windows::snmp::plugin --hostname=10.24.11.66 --snmp-version='2c' --snmp-community='public' --mode=list-storages
    List storage:
    'C:\ Label:  Serial Number 2cb607df' [size = 53317988352B] [id = 1]
    Skipping storage 'Virtual Memory': no type or no matching filter type
    Skipping storage 'Physical Memory': no type or no matching filter type

Faites de même pour connaître la liste des interfaces réseau : ::

    $ /usr/lib/centreon/plugins/centreon_windows_snmp.pl --plugin=os::windows::snmp::plugin --hostname=10.24.11.66 --snmp-version='2c' --snmp-community='public' --mode=list-interfaces
    List interfaces:
    'loopback_0' [speed = 1073, status = up, id = 1]
    'ethernet_3' [speed = , status = notPresent, id = 10]
    'ppp_1' [speed = , status = notPresent, id = 11]
    'ethernet_10' [speed = 1000, status = up, id = 12]
    'tunnel_4' [speed = 0.1, status = down, id = 13]
    'ethernet_4' [speed = , status = up, id = 14]
    'ethernet_5' [speed = , status = up, id = 15]
    'ethernet_6' [speed = , status = up, id = 16]
    'ethernet_7' [speed = , status = up, id = 17]
    'ethernet_8' [speed = , status = up, id = 18]
    'ethernet_9' [speed = , status = up, id = 19]
    'tunnel_0' [speed = , status = down, id = 2]
    'ethernet_11' [speed = 1000, status = up, id = 20]
    'ethernet_12' [speed = 1000, status = up, id = 21]
    'ethernet_13' [speed = 1000, status = up, id = 22]
    'tunnel_1' [speed = , status = down, id = 3]
    'tunnel_2' [speed = , status = down, id = 4]
    'tunnel_3' [speed = , status = down, id = 5]
    'ppp_0' [speed = , status = down, id = 6]
    'ethernet_0' [speed = , status = up, id = 7]
    'ethernet_1' [speed = , status = up, id = 8]
    'ethernet_2' [speed = , status = up, id = 9]
