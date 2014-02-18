=======================
Les vues personnalisées
=======================

************
Présentation
************

Les vues personnalisées permettent à chaque utilisateur d'avoir sa propre vue de sa supervision.
Une vue peut contenir de 1 à 3 colonnes. Chaque colonne peut contenir des widgets.

Un widget est un module permettant de visualiser certaines informations sur certains objets.
Il est possible d'insérer au sein d'une même vue plusieurs widgets de différents types.
Par défaut, MERETHIS propose des widgets permettant d'obtenir des informations sur : les hôtes, les groupes d'hôtes,
les services, les groupes de services. Enfin, un dernier widget permet de visualiser les graphiques de performances en temps réel.

****************
Gestion des vues
****************

Toutes les manipulations ci-dessous se déroulent au sein de la page **Accueil** ==> **Vues personnalisées**. Cette page est également la première page affichée lors de la connexion
d'un utilisateur au sein de Centreon.

Ajouter une vue
---------------

Pour ajouter une vue, cliquez sur **Ajouter une vue**.

[ TODO Mettre une capture d'écran]

* Le champ **Nom de la vue** indique le nom de la vue qui sera visible par l'utilisateur
* Le champ **Mise en page** permet de choisir le nombre de colonne de la vue

Pour modifier une vue existante, cliquez sur **Editer une vue**.
Notez-bien : la diminution du nombre de colonnes enlève les widgets associées à la colonne.

Partager une vue
----------------

Il est possible de partager une vue existante avec un ou plusieurs utilisateurs.
Pour cela, cliquez sur **Partager la vue**.

* Si le champ **Vérrouillée** est définit à **Oui** alors les autres utilisateurs ne pourront pas modifier la vue
* La liste **Liste des utilisateurs** permet de définir les utilisateurs avec lesquels sont partagés la vue
* La liste **Liste des groupes utilisateur** permet de définir les groupes d'utilisateurs avec lesquels sont partagés la vue

Insérer un widget
-----------------

Pour ajouter un widget, cliquez sur **Ajouter un widget**.

[ TODO Mettre une capture d'écran]

* Le champ **Titre du widget** permet de définir un nom pour son widget
* Choisissez dans le tableau en dessous le type de widget que vous souhaitez ajouter

Personnaliser son widget
------------------------

Il est possible de déplacer un widget en faisant un drag-and-drop depuis la barre de titre.
Pour réduire un widget, cliquez sur [ TODO Mettre l'icône].
Par défaut les informations contenus au sein du widget sont rafraichies de manière régulière.
Pour les rafraîchir manuellement cliquez sur [ TODO Mettre l'icône].

Pour personnaliser son widget, cliquez sur [ TODO Mettre l'icône].

Supprimer un widget
-------------------

Il est possible de supprimer le widget en cliquant sur [ TODO Mettre l'icône].

******************
Détail des widgets
******************

Les paragraphes ci-dessous détaillent les attributs de chaque widget après avoir cliqué sur [ TODO Mettre l'icône]

[ TODO Pas de traduction pour les widgets ?]

Le widget d'hôtes
-----------------

Filters
^^^^^^^

* Le champ **Host Name Search** permet de faire une recherche sur un ou plusieurs noms d'hôtes
* Si la case **Display Up** est cochée, les hôtes en statut UP seront affichés
* Si la case **Display Down** est cochée, les hôtes en statut DOWN seront affichés
* Si la case **Display Unreachable** est cochée, les hôtes en statut UNREACHABLE seront affichés
* La liste **Acknowledgement Filter** permet d'afficher les hôtes acquittés ou non acquittés (si la liste est vide les deux types d'hôtes seront affichés)
* La liste **Downtime Filter** permet d'afficher les hôtes qui subissent un temps d'arrêt ou non (si la liste est vide les deux types d'hôtes seront affichés)
* La liste **State Type** permet d'afficher les hôtes en état SOFT ou HARD (si la liste est vide les deux types d'hôtes seront affichés)
* La liste **Hostgroup** permet d'afficher les hôtes appartenant à un certain groupe d'hôtes (si la liste est vide tous les hôtes seront affichés)
* La liste **Results** limite le nombre de résultats

Columns
^^^^^^^

* Si la case **Display Host Name** est cochée alors le nom d'hôte sera affiché
* Si la case **Display Output** est cochée alors le message associé au statut de l'hôte sera affiché
* La liste **Output Length** permet de limiter la longueur du message affiché
* Si la case **Display Status** est cochée alors le statut de l'hôte est affiché
* Si la case **Display IP** est cochée alors l'adresse IP de l'hôte est affiché
* Si la case **Display Last Check** est cochée alors la date et l'horaire de la dernière vérificatin est affichée
* Si la case **Display Duration** est cochée alors la durée durant laquelle l'hôte a conservé son statut est affichée
* Si la case **Display Hard State Duration** est cochée alors la durant laquelle l'hôte a conservé son état HARD est affichée
* Si la case **Display Tries** est cochée alors le nombre d'essais avant la validation de l'état est affichée
* La liste **Order By** permet de classer les hôtes par ordre alphabétique suivant plusieurs paramètres

Misc
^^^^

* Le champ **Refresh Interval (seconds)** permet de définir la durée avant le rafraichissement des données

Le widget de services
---------------------

Filters
^^^^^^^

* Le champ **Host Name** permet de faire une recherche sur un ou plusieurs noms d'hôtes
* Le champ **Service Description** peret de faire une recherche sur un ou plusieurs noms de services
* Si la case **Display Ok** est cochée, les services en statut OK seront affichés
* Si la case **Display Warning** est cochée, les services en statut WARNING seront affichés
* Si la case **Display Critical** est cochée, les services en statut CRITICAL seront affichés
* Si la case **Display Unknown** est cochée, les services en statut UNKNOWN seront affichés
* Si la case **Display Pending** est cochée, les services en statut PENDING seront affichés
* La liste **Acknowledgement Filter** permet d'afficher les services acquittés ou non acquittés (si la liste est vide les deux types d'hôtes seront affichés)
* La liste **Downtime Filter** permet d'afficher les services qui subissent un temps d'arrêt ou non (si la liste est vide les deux types d'hôtes seront affichés)
* La liste **State Type** permet d'afficher les services en état SOFT ou HARD (si la liste est vide les deux types d'hôtes seront affichés)
* La liste **Hostgroup** permet d'afficher les services appartenant à des hotes faisant partie d'un certain groupe d'hôtes (si la liste est vide tous les services seront affichés)
* La liste **Servicegroup** permet d'afficher les services appartenant à un certain groupe de services (si la liste est vide tous les services seront affichés)
* La liste **Results** limite le nombre de résultats

Columns
^^^^^^^

* Si la case **Display Host Name** est cochée alors le nom d'hôte sera affiché
* Si la case **Display Service Description** est cochée alors le nom du service sera affiché
* Si la case **Display Output** est cochée alors le message associé au statut du service sera affiché
* La liste **Output Length** permet de limiter la longueur du message affiché
* Si la case **Display Status** est cochée alors le statut du service est affiché
* Si la case **Display Last Check** est cochée alors la date et l'horaire de la dernière vérificatin est affichée
* Si la case **Display Duration** est cochée alors la durée durant laquelle le service a conservé son statut est affichée
* Si la case **Display Hard State Duration** est cochée alors la durant laquelle le service a conservé son état HARD est affichée
* Si la case **Display Tries** est cochée alors le nombre d'essais avant la validation de l'état est affichée
* La liste **Order By** permet de classer les services par ordre alphabétique suivant plusieurs paramètres

Misc
^^^^

* Le champ **Refresh Interval (seconds)** permet de définir la durée avant le rafraichissement des données

Le widget de graphes
--------------------

* Le champ **Service** permet de choisir le service pour lequel le graphe sera affiché
* La liste **Graph period** permet de choisir la période de temps que le graphe doit afficher
* Le champ **Refresh Interval (seconds)** permet de définir la durée avant le rafraichissement des données

Le widget de groupe d'hôtes
---------------------------

* Le champ **Hostgroup Name Search** permet de choisir les groupes d'hôtes affichés
* Si la case **Enable Detailed Mode** est cochée, alors tous les noms d'hôtes ainsi que les services associés à ces hôtes seront affichés pour les groupes d'hôtes sélectionnés
* La liste **Results** permet de limiter le nombre de résultats
* La liste **Order By** permet de classer les groupes d'hôtes par ordre alphabétique suivant plusieus paramètres
* Le champ **Refresh Interval (seconds)** permet de définir la durée avant le rafraichissement des données

Le widget de groupes de services
--------------------------------

* Le champ **Servicegroup Name Search** permet de choisir les groupes de services affichés
* Si la case **Enable Detailed Mode** est cochée, alors tous les noms d'hôtes ainsi que les services associés à ces hôtes seront affichés pour les groupes de services sélectionnés
* La liste **Results** permet de limiter le nombre de résultats
* La liste **Order By** permet de classer les groupes de services par ordre alphabétique suivant plusieus paramètres
* Le champ **Refresh Interval (seconds)** permet de définir la durée avant le rafraichissement des données

****************
Créer son widget
****************

Dans ce chapitre, nous prendrons comme exemple un widget Centreon appellé Widget Dummy

Structure du répertoire
-----------------------

Afin de créer son widget, il est nécessaire de créer un dossier au sein du dossier web de Centreon :

::

	$ centreon/www/widgets/nomDeVotreWidget

Fichier de configuration
------------------------

Le fichier **configs.xml** est obligatoire au sein du dossier. Pour le widget Dummy, il contient les informations suivantes :

::

	<configs>
      <title>Dummy</title>
      <author>Centreon</author>
      <email>contact@centreon.com</email>
      <website>http://www.centreon.com</website>
      <description>Dummy widget</description>
      <version>1.0.3</version>
      <keywords>dummy, widget, centreon</keywords>
      <screenshot></screenshot>
      <thumbnail>./widgets/dummy/resources/logoCentreon.png</thumbnail>
      <url>./widgets/dummy/index.php</url>
      <autoRefresh></autoRefresh>
      <preferences>
              <preference label="text preference" name="text preference" defaultValue="default value" type="text"/>
              <preference label="boolean preference" name="boolean preference" defaultValue="1" type="boolean"/>
              <preference label="date" name="date" defaultValue="" type="date"/>
              <preference label="host preference" name="host preference" defaultValue="" type="host"/>
              <preference label="list preference" name="list preference" defaultValue="none" type="list">
                      <option value="all" label="all"/>
                      <option value="none" label="none"/>
              </preference>
              <preference label="range preference" name="range preference" defaultValue="5" type="range" min="0" max="50" step="5"/>
              <preference label="host search" name="host search" defaultValue="notlike _Module_%" type="compare"/>
      </preferences>
	</configs>

Tags basiques
-------------

Le tableau ci-dessous résume l'ensemble des tags qui peuvent être présents au sein du fichier **configs.xml** :

* \* : Champ obligatoire*

+--------------------------+-------------------------------------------------------------------------------------------------------------------------------------------+
|   Nom du tag             |   Description                                                                                                                             | 
+==========================+===========================================================================================================================================+
| title \*                 | Titre du widget                                                                                                                           |
+--------------------------+-------------------------------------------------------------------------------------------------------------------------------------------+
| author \*                | Auteur du widget                                                                                                                          |
+--------------------------+-------------------------------------------------------------------------------------------------------------------------------------------+
| email                    | Adresse email de l'auteur                                                                                                                 |
+--------------------------+-------------------------------------------------------------------------------------------------------------------------------------------+
| website                  | Site internet du projet                                                                                                                   |
+--------------------------+-------------------------------------------------------------------------------------------------------------------------------------------+
| description \*           | Description du widget                                                                                                                     |
+--------------------------+-------------------------------------------------------------------------------------------------------------------------------------------+
| version \*               | Version du widget                                                                                                                         |
+--------------------------+-------------------------------------------------------------------------------------------------------------------------------------------+
| keyword   	           | Liste de mots pour décrire le widget                                                                                                      |
+--------------------------+-------------------------------------------------------------------------------------------------------------------------------------------+
| screenshot               | Screenshot du plugin (à mettre dans le dossier du widget)                                                                                 |
+--------------------------+-------------------------------------------------------------------------------------------------------------------------------------------+
| thumbnail                | Logo du projet - la taille conseillée est 100px x 25px (à mettre dans le dossier du widget)                                               |
+--------------------------+-------------------------------------------------------------------------------------------------------------------------------------------+
| url \*                   | Chemin vers la page principale du widget                                                                                                  |
+--------------------------+-------------------------------------------------------------------------------------------------------------------------------------------+

Les attributs des paramètres
----------------------------

Les paramètres contiennent les champs qui seront affichés lors de la configuration du plugin au sein de Centreon (tag <preferences>).
Le tableau ci-dessous résume les attributs pour un paramètre définit :

* \* : Champ obligatoire*

+--------------------------+-------------------------------------------------------------------------------------------------------------------------------------------+
|   Nom du tag             |   Description                                                                                                                             | 
+==========================+===========================================================================================================================================+
| label \*                 | Label du paramètre (affiché avant le champ)                                                                                               |
+--------------------------+-------------------------------------------------------------------------------------------------------------------------------------------+
| name \*                  | Nom du paramètre : utilisé pour récuperer sa valeur                                                                                       |
+--------------------------+-------------------------------------------------------------------------------------------------------------------------------------------+
| defaultvalue \*          | Valeur par défaut du paramètre                                                                                                            |
+--------------------------+-------------------------------------------------------------------------------------------------------------------------------------------+
| requirePermission        | Booléen (0 ou 1) [ TODO besoin de précisions]                                                                                             |
+--------------------------+-------------------------------------------------------------------------------------------------------------------------------------------+
| type \*                  | Type de paramètres (voir tableau ci-dessous)                                                                                              |
+--------------------------+-------------------------------------------------------------------------------------------------------------------------------------------+

Pour le type range, les tags suivants sont obligatoires :

+--------------------------+-------------------------------------------------------------------------------------------------------------------------------------------+
|   Nom du tag             |   Description                                                                                                                             | 
+==========================+===========================================================================================================================================+
| min \*                   | Valeur minimum du paramètre                                                                                                               |
+--------------------------+-------------------------------------------------------------------------------------------------------------------------------------------+
| max \*     	           | Valeur maximum du paramètre                                                                                                               |
+--------------------------+-------------------------------------------------------------------------------------------------------------------------------------------+
| step \*                  | Pas entre le minimum et le maximum                                                                                                        |
+--------------------------+-------------------------------------------------------------------------------------------------------------------------------------------+

Les types de paramètres
-----------------------

Il existe plusieurs types de paramètres. Chaque type de paramètres permet d'afficher un élément précis :

+--------------------------+-----------------------------------------------------------------------------------------------------------------------------+
|   Nom du type            |   Description                                                                                                               |
+==========================+=============================================================================================================================+
| text                     | Affiche un champ texte                                                                                                      |
+--------------------------+-----------------------------------------------------------------------------------------------------------------------------+
| boolean                  | Affiche une checkbox                                                                                                        |
+--------------------------+-----------------------------------------------------------------------------------------------------------------------------+
| date                     | Affiche un champ de date (2 éléments)                                                                                       |
+--------------------------+-----------------------------------------------------------------------------------------------------------------------------+
| list                     | Affiche une liste (à remplir avec le tag **option** : voir exemple plus haut)                                               |
+--------------------------+-----------------------------------------------------------------------------------------------------------------------------+
| range                    | Affiche une liste avec un minimum, un maximum séparé avec un pas                                                            |
+--------------------------+-----------------------------------------------------------------------------------------------------------------------------+
| compare                  | Affiche une liste contenant des opérateurs SQL et un texte à associer à l'opérateur                                         |
+--------------------------+-----------------------------------------------------------------------------------------------------------------------------+
| host                     | Liste contenant tous les hôtes                                                                                              |
+--------------------------+-----------------------------------------------------------------------------------------------------------------------------+
| hostgroup                | Liste contenant tous les groupes d'hôtes                                                                                    |
+--------------------------+-----------------------------------------------------------------------------------------------------------------------------+
| hostTemplate             | Liste contenant tous les modèles d'hôtes                                                                                    |
+--------------------------+-----------------------------------------------------------------------------------------------------------------------------+
| servicegroup             | Liste contenant tous les groupes de services                                                                                |
+--------------------------+-----------------------------------------------------------------------------------------------------------------------------+
| serviceTemplate          | Liste contenant tous les modèles de services                                                                                |
+--------------------------+-----------------------------------------------------------------------------------------------------------------------------+

Le fichier **configs.xml** plus haut affiche la fenêtre de configuration suivante :

[ TODO Mettre l'image]

Code Source
-----------

Dans le fichier PHP, nous pouvons récupérer l'ensemble des paramètres grâce au code suivant :

::

	<?php
	// required classes
	require_once "/etc/centreon/centreon.conf.php";
	require_once $centreon_path . "www/class/centreon.class.php";
	require_once $centreon_path . "www/class/centreonSession.class.php";
	require_once $centreon_path . "www/class/centreonDB.class.php";
	require_once $centreon_path . "www/class/centreonWidget.class.php";

	// check if session is alive
	session_start();
	if (!isset($_SESSION['centreon'])) {
	   echo "Session expired";
	   exit;
	}
	$centreon = $_SESSION['centreon'];

	// variable initialization
	$db = new CentreonDB();
	$widget = new CentreonWidget($centreon, $db);

	// retrieve widget preferences
	$preferences = $widget->getWidgetPreferences($_GET['widgetId']);
	// print the retrieved preferences
	print_r($preferences);
	?>
