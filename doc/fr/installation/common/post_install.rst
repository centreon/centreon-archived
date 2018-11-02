Pour démarrer l'ordonnanceur de supervision :

1. Sur l'interface web, rendez-vous dans le menu **Configuration** ==> **Moteur de supervision**
2. Laissez les options par défaut, et cliquez sur **Exporter**
3. Sélectionnez le collecteur **Central** dans la liste de sélection
4. Décochez **Générer les fichiers de configuration** et **Lancer le débogage du moteur de supervision (-v)**
5. Cochez **Déplacer les fichiers générés** ainsi que **Redémarrer l'ordonnanceur**
6. Cliquez à nouveau sur **Exporter**
7. Connectez-vous avec l'utilisateur 'root' sur votre serveur
8. Démarrez le composant Centreon Broker ::

    # systemctl cbd start

9. Démarrez Centreon Engine ::

    # systemctl centengine start

10. Démarrez centcore ::

    # systemctl centcore start

11. Démarrez centreontrapd ::

    # systemctl centreontrapd start

La supervision est maintenant opérationnelle.

Activer le lancement automatique de services au démarrage.

Lancer les commandes suivantes sur le serveur Central : ::

    # systemctl enable centcore
    # systemctl enable centreontrapd
    # systemctl enable cbd
    # systemctl enable centengine

L'interface web de Centreon est composée de plusieurs menus, chaque menu a une fonction bien précise :

.. image :: /images/guide_utilisateur/amenu.png
   :align: center

* Le menu **Accueil** permet d'accéder au premier écran d'accueil après s'être connecté. Il résume l'état général de la supervision.
* Le menu **Supervision** regroupe l'état de tous les éléments supervisés en temps réel et en différé au travers de la visualisation des logs
* Le menu **Vues** permet de visualiser et de configurer les graphiques de performances pour chaque élément du système d'informations
* Le menu **Rapports** permet de visualiser de manière intuitive (via des diagrammes) l'évolution de la supervision sur une période donnée
* Le menu **Configuration** permet de configurer l'ensemble des éléments supervisés ainsi que l'infrastructure de supervision
* Le menu **Administration** permet de configurer l'interface web Centreon ainsi que de visualiser l'état général des serveurs

***************************************
Configurez votre supervision facilement
***************************************

En lui-même Centreon est un excellent outil de supervision et peut être
configuré pour correspondre exactement à vos besoins. Cependant vous
trouverez peut-être utile d'utiliser Centreon IMP pour vous aider à
configurer rapidement votre supervision. Centreon IMP vous fournit des
Plugin Packs qui sont des paquets contenant des modèles de configuration
qui réduisent drastiquement le temps nécessaire pour superviser la
plupart des services de votre réseau.

Centreon IMP nécessite les composants techniques Centreon License
Manager et Centreon Plugin Pack Manager pour fonctionner.

Si vous n'avez pas installé les modules durant l'instalaltion de Centreon,
Rendez-vous au menu **Administration > Extensions > Modules**.

Clicquez sur le bouton **Install/Upgrade all** et validez l'action :

.. image:: /_static/images/installation/install_imp_1.png
   :align: center

Une fois l'instalaltion terminée, cliquez sur **Back**.
Les modules sont maintenant installés :

.. image:: /_static/images/installation/install_imp_2.png
   :align: center

Vous pouvez maintenant vous rendre au menu **Configuration > Extensions
-> Plugin Packs**. Vous y trouverez vos six premiers Plugin Packs
gratuits pour vous aider à démarrer. Cinq Plugin Packs supplémentaires
sont débloqués après vous être inscrit et plus de 150 sont disponibles
si vous souscrivez à l'offre IMP (plus d'informations sur
`notre site web <https://www.centreon.com>`_).

.. image:: /_static/images/installation/install_imp_3.png
   :align: center

Vous pouvez continuer à configurer votre supervision en utilisant
Centreon IMP en suivant :ref:`ce guide <impconfiguration>`.
