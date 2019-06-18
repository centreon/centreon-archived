.. _monitor_cisco:

###################################
Superviser un routeur Cisco en SNMP
###################################

Rendez-vous dans le menu **Configuration > Plugin Packs** et installez le Plugin
Pack **Cisco Standard** :

.. image:: /images/quick_start/quick_start_cisco_0.gif
    :align: center

Rendez-vous maintenant dans le menu **Configuration > Hosts > Hosts** et cliquez
sur le bouton **Add** :

.. image:: /images/quick_start/quick_start_cisco_1.png
    :align: center

Renseignez les informations suivantes :

* Le nom de votre server
* Une description de votre serveur
* Son adresse IP
* La communauté et la version SNMP

Cliquez sur le bouton **+ Add a new entry** pour le champ **Templates** puis
sélectionnez le modèle **Net-Cisco-Standard-SNMP-custom**.

Cliquez sur le bouton **Save**.

Votre équipement a été ajouté à la configuration de la supervision :

.. image:: /images/quick_start/quick_start_cisco_2.png
    :align: center

Rendez-vous dans le menu **Configuration > Services > Services by host**. Un
ensemble d'indicateurs a été déployé automatiquement :

.. image:: /images/quick_start/quick_start_cisco_3.png
    :align: center

D'autres indicateurs peuvent être supervisés. Cliquez sur le bouton **Add**
pour ajouter par exemple la supervision de la bande passante d'une interface
réseau :

.. image:: /images/quick_start/quick_start_cisco_4a.png
    :align: center

Dans le champ **Description**, saisissez le nom du service à ajouter, puis
sélectionner l'hôte auquel lier cet indicateur. Dans le champ **Template**
sélectionner le modèle **Net-Cisco-Standard-Traffic-Generic-Name-SNMP-custom**.

Une liste de macros en correspondance avec le modèle va alors apparaître :

.. image:: /images/quick_start/quick_start_cisco_4b.png
    :align: center

Saisissez le nom de votre interface pour la macro **INTERFACENAME** et cliquez
sur le bouton **Save** pour ajouter cet indicateur à la configuration.

Faites de même pour ajouter la supervision des erreurs de paquets :

.. image:: /images/quick_start/quick_start_cisco_5.png
    :align: center

:ref:`menu dédié<deployconfiguration>`.

Rendez-vous ensuite dans le menu **Monitoring > Status Details > Services** et
sélectionnez la valeur **All** pour le filtre **Service Status**. Après quelques
minutes, les premiers résultats de la supervision apparaissent :

.. image:: /images/quick_start/quick_start_cisco_6.png
    :align: center

********************
Pour aller plus loin
********************

Le Plugin Pack **Cisco Standard** apporte de nombreux modèles de supervision.
Lors de la création d'un service, il est possible de rechercher les
modèles disponibles dans la liste de sélection :

.. image:: /images/quick_start/quick_start_cisco_7.png
    :align: center

Il est également possible d'accèder au menu **Configuration > Services >
Templates** pour en connaître la liste :

.. image:: /images/quick_start/quick_start_cisco_8.png
    :align: center

Pour connaître le nom des interfaces réseau disponibles, vous pouvez exécuter
le plugin Centreon en ligne de commande tel quel : ::

    $ /usr/lib/centreon/plugins/centreon_cisco_standard_snmp.pl --plugin=network::cisco::standard::snmp::plugin --hostname=10.40.1.254 --snmp-community=public --snmp-version=2c --mode=list-interfaces
    List interfaces:
    'Gi1/0/1' [speed = 1000, status = up, id = 10101]
    'Gi1/0/2' [speed = 1000, status = up, id = 10102]
    'Gi1/0/3' [speed = 10, status = down, id = 10103]
    'Gi1/0/4' [speed = 10, status = down, id = 10104]
    'Gi1/0/5' [speed = 10, status = down, id = 10105]
    'Gi1/0/6' [speed = 1000, status = up, id = 10106]
    'Gi1/0/7' [speed = 10, status = down, id = 10107]
    'Gi1/0/8' [speed = 10, status = down, id = 10108]
    'Gi1/0/9' [speed = 10, status = down, id = 10109]
    'Gi1/0/10' [speed = 10, status = down, id = 10110]
    'Gi1/0/11' [speed = 10, status = down, id = 10111]
    'Gi1/0/12' [speed = 10, status = down, id = 10112]
    'Gi1/0/13' [speed = 10, status = down, id = 10113]
    'Gi1/0/14' [speed = 10, status = down, id = 10114]
    'Gi1/0/15' [speed = 10, status = down, id = 10115]
    'Gi1/0/16' [speed = 10, status = down, id = 10116]
    'Gi1/0/17' [speed = 1000, status = up, id = 10117]
    'Gi1/0/18' [speed = 10, status = down, id = 10118]
    'Gi1/0/19' [speed = 10, status = down, id = 10119]
    'Gi1/0/20' [speed = 100, status = up, id = 10120]
    'Gi1/0/21' [speed = 10, status = down, id = 10121]
    'Gi1/0/22' [speed = 1000, status = up, id = 10122]
    'Gi1/0/23' [speed = 10, status = down, id = 10123]
    'Gi1/0/24' [speed = 1000, status = up, id = 10124]
    'Gi1/0/25' [speed = 10, status = down, id = 10125]
    'Gi1/0/26' [speed = 10, status = down, id = 10126]
    'Gi1/0/27' [speed = 10, status = down, id = 10127]
    'Gi1/0/28' [speed = 10, status = down, id = 10128]

Ou pour récupérer la liste des spanning-tree : ::

    $ /usr/lib/centreon/plugins/centreon_cisco_standard_snmp.pl --plugin=network::cisco::standard::snmp::plugin --hostname=10.40.1.254 --snmp-community=mrthsrnrd --snmp-version=2c --mode=list-spanning-trees
    List ports with Spanning Tree Protocol:
    [port = GigabitEthernet1/0/20] [state = forwarding] [op_status = up] [admin_status = up] [index = 10120]
    [port = Port-channel1] [state = forwarding] [op_status = up] [admin_status = up] [index = 5001]
