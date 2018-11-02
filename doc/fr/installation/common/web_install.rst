*************
Configuration
*************

Connectez-vous à l'interface web via http://[ADRESSE_IP_DE_VOTRE_SERVEUR]/centreon.
L'assistant de configuration de Centreon s'affiche, cliquez sur **Next**.

.. image :: /images/guide_utilisateur/acentreonwelcome.png
   :align: center
   :scale: 65%

L'assistant de configuration de Centreon contrôle la disponibilité des modules, cliquez sur **Next**.

.. image :: /images/guide_utilisateur/acentreoncheckmodules.png
   :align: center

Cliquez sur **Next**.

.. image :: /images/guide_utilisateur/amonitoringengine2.png
   :align: center
   :scale: 65%

Cliquez sur **Next**.

.. image :: /images/guide_utilisateur/abrokerinfo2.png
   :align: center
   :scale: 65%

Définissez les informations concernant l'utilisateur admin, cliquez sur **Next**.

.. image :: /images/guide_utilisateur/aadmininfo.png
   :align: center
   :scale: 65%

Par défaut, le serveur 'localhost' est défini et le mot de passe root est vide. Si vous utilisez un serveur de base de données déporté, il convient de modifier ces deux informations.
Dans notre cas, nous avons uniquement besoin de définir un mot de passe pour l'utilisateur accédant aux bases de données Centreon, à savoir 'centreon'.

Cliquez sur **Next**.

.. image :: /images/guide_utilisateur/adbinfo.png
   :align: center
   :scale: 65%

.. note::
    Si le message d'erreur suivant apparaît **Add innodb_file_per_table=1 in my.cnf file under the [mysqld] section and restart MySQL Server**,
    Effectuez les opérations ci-dessous :
    
    1. Connectez-vous avec l'utilisateur 'root' sur votre serveur
    
    2. Editez le fichier suivant **/etc/my.cnf**
    
    3. Ajoutez la ligne suivante au fichier : ::
    
        [mysqld]
        innodb_file_per_table=1
    
    4. Redémarrez le service mysql: ::
    
        # systemctl restart mysql
    
    5. Cliquez sur **Refresh**

L'assistant de configuration configure les bases de données.

Cliquez sur **Next**.

.. image :: /images/guide_utilisateur/adbconf.png
   :align: center
   :scale: 65%

L'assistant de configuration propose ensuite d'installer les modules présents sur le serveur Centreon.

Cliquez sur **Install**.

.. image :: /images/guide_utilisateur/module_installationa.png
   :align: center
   :scale: 65%

Une fois les modules installés, cliquez sur **Next**.

.. image :: /images/guide_utilisateur/module_installationb.png
   :align: center
   :scale: 65%

À cette étape une publicité permet de connaitre les dernières nouveautés
de Centreon. Si votre plate-forme est connectée à Internet vous disposez
des dernières informations, sinon l’information présente dans cette version
sera proposée.

.. image :: /images/guide_utilisateur/aendinstall.png
   :align: center
   :scale: 65%

L’installation est terminée, cliquez sur **Finish**.

Vous pouvez maintenant vous connecter.

.. image :: /images/guide_utilisateur/aconnection.png
   :align: center
   :scale: 65%

Dans un premier temps, il est nécessaire de passer l'interface en version française. Pour cela :

#. Clicuez sur votre profil utilisateur en haut à droite de l'interface
#. Click sur le bouton **edit profil**
#. Dans le champ **Language**, remplacez **en_US** par **fr_FR.UTF-8**
#. Cliquez sur **Save**

.. image :: /images/guide_utilisateur/alanguage.png
   :align: center
