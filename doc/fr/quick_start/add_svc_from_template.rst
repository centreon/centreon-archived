========================================
Déployer un service à partir d'un modèle
========================================

Dans le précédent guide de démarrage rapide vous avez 
:ref:`ajouté un hôte à partir d'un modèle<add_host_template>`
à partir du modèle d'hôte **OS-Linux-SNMP**. Ce modèle a permis de créer 
automatiquement les services suivants :

* CPU
* Load
* Memory
* Swap

Cependant certains indicateurs n'ont pu être ajouté automatiquement
parce que leur configuration dépend de certains paramètres liés à l'équipement
lui-même (nom des partitions, des interfaces réseau, etc.).

Premièrement vous devez vous :ref:`connecter<centreon_login>` à l'interface
web Centreon avec un compte administrateur ou un compte disposant des droits
d'accès pour gérer les objets.

Se rendre dans le menu **Configuration > Services > Services by host** et cliquer sur le
bouton **Add** :

.. image:: /_static/images/quick_start/add_service_menu.png
    :align: center

Pour ajouter un nouveau service à votre hôte vous devez définir 3 choses :

* Sélectionner l'hôte auquel lier ce service via le champ **Linked with Hosts**
* Définir le nom du service via le champ **Description**, par exemple **Traffic-eth0** pour superviser la bande passante de l'interface eth0
* Sélectionner le modèle de service **OS-Linux-Traffic-Generic-Name-SNMP** via le champ **Service Template**

.. note::
    Après avoir sélectionné un modèle de service, de nouveaux champs peuvent apparaître.
    Ces champs décrivent les arguments nécessaires au contrôle du service.
    Le plus souvent il s'agit des seuils d'alerte pour déclencher la notification.
    Vous pouvez laisser ces valeurs par défaut ou les modifier

Modifier la valeur de la macro **INTERFACENAME** pour saisir le nom de votre 
interface réseau, **eth0** dans	 notre exemple.

.. image:: /_static/images/quick_start/add_svc_template_form.png
    :align: center

Sauvegarder les modifications en cliquant sur le bouton **Save**.

.. image:: /_static/images/quick_start/add_svc_template_list.png
    :align: center

Le service est maintenant défini dans l'interface Centreon web mais le moteur ne le
connaît pas encore !

Vous devez :ref:`générer la configuration, l'exporter et l'envoyer au moteur de supervision<deployconfiguration>`.

Le résultat est visible dans le menu **Monitoring > Status Details > Services** :

.. image:: /_static/images/quick_start/add_svc_template_monitoring.png
    :align: center
