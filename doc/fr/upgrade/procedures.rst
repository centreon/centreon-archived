===================================
Procédure de mise à jour spécifique
===================================

***************************************************************************
Mettre à jour un collecteur distant après une mise à jour de *Centreon* 2.4
***************************************************************************

Cette procédure explique comment mettre à jour la configuration d'un collecteur
distant après une migration vers *Centreon* 2.4. Les exemples donnés parlent de 
*Nagios*, mais cette procédure fonctionne également avec *Centreon Engine* si 
vous remplacez les fichiers binaires et les chemins.

Modification du collecteur
==========================

Créer un utilisateur ``centreon`` avec un mot de passe associé :

::

  $ useradd centreon
  $ passwd centreon

Ajouter l'utilisateur ``nagios`` au groupe ``centreon`` :

::

  $ usermod -a -G centreon nagios

Editer le fichier de droit sudo :

::

  $ visudo

Ajouter les lignes suivantes :

::

  User_Alias CENTREON=nagios,centreon
  
Puis mettre à jour la configuration existante en remplacement ``nagios`` par ``CENTREON`` :

::

  CENTREON ALL=NOPASSWD: /etc/init.d/nagios restart
  CENTREON ALL=NOPASSWD: /etc/init.d/nagios stop
  CENTREON ALL=NOPASSWD: /etc/init.d/nagios start
  CENTREON ALL=NOPASSWD: /etc/init.d/nagios reload
  CENTREON ALL=NOPASSWD: /usr/bin/nagiostats
  CENTREON ALL=NOPASSWD: /usr/local/etc/bin/nagios *

Sauvegarder les modifications et clore le fichier.

Modifier les droits du répertoire contenant la configuration *Nagios* :

::

  $ chown centreon:centreon </nagios/path/etc/>
  $ chmod 775 </nagios/path/etc/>

Modifier également les droits du fichier *service-perfdata* :

::

  $ chown centreon:centreon </nagios/path/var/>service-perfdata
  $ chmod 775 </nagios/path/var/>service-perfdata

Enfin, il est nécessaire d'exporter les fichiers de configuration du collecteur
et de redémarrer le moteur de supervision via l'interface web.

Vous devriez voir apparaître un message indiquant que *Nagios* a reçu une
instruction de redémarrage via son journal d'évènements.

Modifications du serveur central 
================================

Copier la clé publique de l'utilisateur ``centreon`` vers le collecteur distant :

::

  $ su - centreon
  $ ssh-copy-id -i ~/.ssh/id_rsa.pub centreon@<poller_ip_address>

Remplacer ``<poller_ip_address>`` par l'adresse IP du collecteur.

Pour finaliser l'opération, se connecter au collecteur depuis le 
serveur central :

::

  $ su - centreon
  $ ssh <poller_ip_address>

Répondre ``y`` à la question posée. Vous devriez vous connecter sans
saisir le mot de passe.
