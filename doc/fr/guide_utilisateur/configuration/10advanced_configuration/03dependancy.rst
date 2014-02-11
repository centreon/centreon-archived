===============
Les dépendances
===============

********
Principe
********

Les dépendances sont utilisées afin de répondre à deux besoins :

* Limiter l'envoi de notifications
Exemples :

Si un hôte est indisponible, il est nécessaire d'envoyer une notification pour l'hôte indisponible mais l'envoi de notifications pour les services liés à cette hôte doit être désactivé (étant donné que l'hôte ne peut pas être interrogé).

Si un service est vérifié via le protocole SNMP, si l'agent SNMP de la machine cible est indisponible alors ce service ne peut pas être joint. Une seule notification doit être envoyée : celle pour l'agent SNMP indisponible.

* Mettre en place une hiérarchie entre les hôtes et les services. Un switch est connecté à un hôte. Par conséquent, l'hôte est dépendant de ce switch. Si le switch devient indisponible, alors l'hôte lié à celui-ci est injoignable.

********************************************
Gestion des dépendances avec Centreon Broker
********************************************

[ TODO Besoin d'informations]

************************
Les liens de dépendances
************************

Les hôtes
---------



Les services
------------



Les groupes
-----------