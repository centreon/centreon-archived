==================
Ajouter un service
==================

Vous venez :ref:`d'ajouter un hôte<add_host>` et vous souhaitez ajouter des 
points de contrôle supplémentaires.

.. note::
	Un point de contrôle ou indicateur est appelé **service** dans Centreon.

Se rendre dans le menu **Configuration  > Services > Services by host**
et cliquer sur le bouton **Add** :

.. image:: /_static/images/quick_start/add_service_menu.png
    :align: center

Pour ajouter un service à un hôte, seuls 3 champs sont nécessaires :

* Sélectionner votre hôte via le champ **Linked with Hosts**
* Définir le nom du point de contrôle via le champ **Description**
* Sélectionner un modèle de service, par exemple **Base-Ping-LAN** via le champ **Service Template**

.. note::
    Après avoir sélectionné un modèle de service, de nouveaux champs peuvent apparaître.
    Ces champs décrivent les arguments nécessaires au contrôle du service.
    Le plus souvent il s'agit des seuils d'alerte pour déclencher la notification.
    Vous pouvez laisser ces valeurs par défaut ou les modifier.

.. image:: /_static/images/quick_start/add_service_form.png
    :align: center

Sauvegarder les modifications en cliquant sur le bouton **Save**.

.. image:: /_static/images/quick_start/add_service_list.png
    :align: center

Le service est maintenant défini dans l'interface Centreon web mais le moteur ne le
connaît pas encore !

Vous devez :ref:`générer la configuration, l'exporter et l'envoyer au moteur de supervision<deployconfiguration>`.

Le résultat est visible dans le menu **Monitoring > Status Details > Hosts** :

.. image:: /_static/images/quick_start/add_service_monitoring.png
    :align: center
