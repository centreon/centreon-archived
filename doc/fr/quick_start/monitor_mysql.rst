.. _monitor_mysql:

###############################################
Superviser une base de données MySQL ou MariaDB
###############################################

Rendez-vous dans le menu **Configuration > Plugin Packs** et installez le Plugin
Pack **MySQL/MariaDB** :

.. image:: /images/quick_start/quick_start_mysql_0.gif
    :align: center

Rendez-vous maintenant dans le menu **Configuration > Hosts > Hosts** et cliquez
sur le bouton **Add** :

.. image:: /images/quick_start/quick_start_mysql_1a.png
    :align: center

Renseignez les informations suivantes :

* Le nom de votre server
* Une description de votre serveur
* Son adresse IP

Cliquez sur le bouton **+ Add a new entry** pour le champ **Templates** puis
sélectionnez le modèle **App-DB-MySQL-custom**.

Une liste de macros en correspondance avec le modèle va alors apparaître :

.. image:: /images/quick_start/quick_start_mysql_1b.png
    :align: center

Renseigner la valeur des macros suivantes :

* **MYSQLUSERNAME** : le nom de l'utilisateur pour se connecter à la base de données.
* **MYSQLPASSWORD** : le mot de passe associé à l'utilisateur.
* **MYSQLPORT** : le port d'écoute de la base de données, par défaut 3306.

Puis, cliquez sur le bouton **Save**.

Votre équipement a été ajouté à la configuration de la supervision :

.. image:: /images/quick_start/quick_start_mysql_2.png
    :align: center

Rendez-vous dans le menu **Configuration > Services > Services by host**. Un
ensemble d'indicateurs a été déployé automatiquement :

.. image:: /images/quick_start/quick_start_mysql_3.png
    :align: center

Il est maintenant temps de déployer la supervision via le
:ref:`menu dédié<deployconfiguration>`.

Rendez-vous ensuite dans le menu **Monitoring > Status Details > Services** et
sélectionnez la valeur **All** pour le filtre **Service Status**. Après quelques
minutes, les premiers résultats de la supervision apparaissent :

.. image:: /images/quick_start/quick_start_mysql_4.png
    :align: center

********************
Pour aller plus loin
********************

Le Plugin Pack **MySQL/MariaDB** apporte de nombreux modèles de supervision.
Lors de la création d'un service, il est possible de rechercher les
modèles disponibles dans la liste de sélection :

.. image:: /images/quick_start/quick_start_mysql_5.png
    :align: center

Il est également possible d'accèder au menu **Configuration > Services >
Templates** pour en connaître la liste :

.. image:: /images/quick_start/quick_start_mysql_6.png
    :align: center
