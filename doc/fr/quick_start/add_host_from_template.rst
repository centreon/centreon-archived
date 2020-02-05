.. _add_host_template:

=====================================
Déployer un hôte à partir d'un modèle
=====================================

Dans le précédent guide de démarrage rapide vous avez :ref:`ajouté un hôte<add_host>`
à partir du modèle d'hôte **generic-host**.
Ce modèle permet d'apporter une configuration minimale pour définir votre hôte.

Cependant les modèles d'objets dans l'interface Centreon web apportent beaucoup plus
que la simple pré-configuration de certaines valeurs. Dans Centreon web vous pouvez
`lier des modèles de services à des modèles d'hôtes<hosttemplates>`.
Grâce à cela vous pouvez déployer facilement et en une seule fois des services
pour votre hôte.

Dans cet exemple nous utiliserons un modèle d'hôte ajouté par un **Plugin Pack Centreon**
afin de contrôler un serveur Linux. Ce modèle d'hôte apporte les contrôles suivants :

* CPU
* Load
* Memory
* Swap

Premièrement vous devez vous :ref:`connecter<centreon_login>` à l'interface
web Centreon avec un compte administrateur ou un compte disposant des droits
d'accès pour gérer les objets.

Se rendre dans le menu **Configuration > Hosts > Hosts** et cliquer sur le
bouton **Add** :

.. image:: /_static/images/quick_start/add_host_menu.png
    :align: center

Vous accédez à un formulaire permettant de définir votre équipement. Pour démarrer
la supervision de ce dernier, renseignez :

* Le nom de celui-ci via le champ **Host Name**
* Sa description via le champ **Alias**
* Son adresse IP ou FQDN dans le champ **IP Address / DNS**
* Cliquer sur le bouton **+ Add a new entry** et sélectionner dans la liste la valeur **OS-Linux-SNMP**
* Sélectionner l'option **Yes** pour le champ **Create Services linked to the Template too**

.. image:: /_static/images/quick_start/add_template_form.png
    :align: center

Sauvegarder les modifications en cliquant sur le bouton **Save**.

.. image:: /_static/images/quick_start/add_template_list.png
    :align: center

L'hôte est maintenant défini dans l'interface Centreon web mais le moteur ne le
connaît pas encore !

Vous devez :ref:`générer la configuration, l'exporter et l'envoyer au moteur de supervision<deployconfiguration>`.

Le résultat est visible dans le menu **Monitoring > Status Details > Services** :

.. image:: /_static/images/quick_start/add_template_monitoring.png
    :align: center
