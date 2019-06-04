.. _add_host:

===============
Ajouter un hôte
===============

Votre plate-forme est maintenant prête pour superviser vos premiers serveurs
et équipements réseau mais vous ne savez pas comment faire. Pas de problème,
le démarrage est très rapide !

Premièrement vous devez vous :ref:`connecter<centreon_login>` à l'interface
web Centreon avec un compte administrateur ou un compte disposant des droits
d'accès pour gérer les objets.

Se rendre dans le menu **Configuration > Hosts > Hosts** et cliquer sur le 
bouton **Add** :

.. image:: /_static/images/quick_start/add_host_menu.png
    :align: center

Vous accédez à un formulaire permettant de décrire votre équipement mais ne
soyez pas effrayé, tous les champs ne sont pas obligatoires !

Pour démarrer renseigner les champs suivants :

* Le nom de l'objet via le champ **Host Name**
* La description de l'objet via le champ **Alias**
* Son adresse IP dans le champ **IP Address / DNS**
* Cliquer sur le bouton **+ Add a new entry** et sélectionner dans la liste la valeur **generic-host**
* Sélectionner l'option **Yes** pour le champ **Create Services linked to the Template too**

.. image:: /_static/images/quick_start/add_host_form.png
    :align: center

Sauvegarder les modifications en cliquant sur le bouton **Save**.

.. image:: /_static/images/quick_start/add_host_list.png
    :align: center

L'hôte est maintenant défini dans l'interface Centreon web mais le moteur ne le 
connaît pas encore !

Vous devez :ref:`générer la configuration, l'exporter et l'envoyer au moteur de supervision<deployconfiguration>`.

Le résultat est visible dans le menu **Monitoring > Status Details > Hosts** :

.. image:: /_static/images/quick_start/add_host_monitoring.png
    :align: center
