.. _monitor_printer:

#################################
Superviser une imprimante en SNMP
#################################

Rendez-vous dans le menu **Configuration > Plugin Packs** et installez le Plugin
Pack **Printer Standard** :

.. image:: /images/quick_start/quick_start_printer_0.gif
    :align: center

Rendez-vous maintenant dans le menu **Configuration > Hosts > Hosts** et cliquez
sur le bouton **Add** :

.. image:: /images/quick_start/quick_start_printer_1.png
    :align: center

Renseignez les informations suivantes :

* Le nom de votre server
* Une description de votre serveur
* Son adresse IP
* La communauté et la version SNMP

Cliquez sur le bouton **+ Add a new entry** pour le champ **Templates** puis
sélectionnez le modèle **HW-Printer-standard-rfc3805-custom**.

Cliquez sur le bouton **Save**.

Votre équipement a été ajouté à la configuration de la supervision :

.. image:: /images/quick_start/quick_start_printer_2.png
    :align: center

Rendez-vous dans le menu **Configuration > Services > Services by host**. Un
ensemble d'indicateurs a été déployé automatiquement :

.. image:: /images/quick_start/quick_start_printer_3.png
    :align: center

Il est maintenant temps de déployer la supervision via le
:ref:`menu dédié<deployconfiguration>`.

Rendez-vous ensuite dans le menu **Monitoring > Status Details > Services** et
sélectionnez la valeur **All** pour le filtre **Service Status**. Après quelques
minutes, les premiers résultats de la supervision apparaissent :

.. image:: /images/quick_start/quick_start_printer_4.png
    :align: center
