********************************
Initialisation de la supervision
********************************

Pour démarrer l'ordonnanceur de supervision :

1. Sur l'interface web, rendez-vous dans le menu **Configuration > Collecteurs**
2. Laissez les options par défaut, et cliquez sur **Exporter la configuration**
3. Sélectionnez le collecteur **Central** dans la liste de sélection
4. Décochez **Générer les fichiers de configuration** et **Lancer le débogage du moteur de supervision (-v)**
5. Cochez **Déplacer les fichiers générés** ainsi que **Redémarrer l'ordonnanceur** en sélectionnant l'option **Redémarrer**
6. Cliquez à nouveau sur **Exporter**
7. Connectez-vous avec l'utilisateur 'root' sur votre serveur
8. Démarrez le composant Centreon Broker ::

    # systemctl start cbd

9. Démarrez Centreon Engine ::

    # systemctl start centengine

10. Démarrez centcore ::

    # systemctl start centcore

11. Démarrez centreontrapd ::

    # systemctl start centreontrapd

La supervision est maintenant opérationnelle.

Activer le lancement automatique de services au démarrage.

Lancer les commandes suivantes sur le serveur Central : ::

    # systemctl enable centcore
    # systemctl enable centreontrapd
    # systemctl enable cbd
    # systemctl enable centengine
    # systemctl enable centreon

************************************
Installer les extensions disponibles
************************************

Rendez-vous au menu **Administration > Extensions > Manager**.
Clicquez sur le bouton **Installall** :

.. image:: /_static/images/installation/install_imp_2.png
   :align: center

****************
Démarrage rapide
****************

Rendez vous au chapitre :ref:`démarrage rapide<quickstart>` pour mettre en place
votre première supervision.
