.. _impconfiguration:

========================================
Configuration simplifiée de Centreon IMP
========================================

Qu'est-ce que IMP ? 
-------------------

Centreon IMP pour Instant Monitoring Platform, est une solution basée sur 
l'association de Centreon 3.4 et de la nouvelle génération de Plugin Packs
accessible en ligne (acquisition et installation).

L'objectif de Centreon IMP est de vous faire gagner beaucoup de temps lors de
la mise en place de votre supervision, de simplifier la mise en place de vos
points de contrôle et de réduire le temps de maintenance de votre plate-forme.
Grâce à ses Plugin Packs, plugins packagés avec des modèles prêts à l'emploi
couplés à une documentation de déploiement (monitoring procedure), Centreon IMP
doit vous permettre de démarrer votre supervision au maximum 30 minutes après
l'installation du système Centreon 3.4. Tous les Plugin Packs sont maintenus par
nos équipes et font l'objet de mises à jour ou ajouts réguliers, vous permettant
d'améliorer de façon continue vos contrôles.

Les Plugin Packs (pack de configuration), développés par Centreon, reposent sur
les Centreon Plugins (sondes de supervision) dont l'éventail fonctionnel est un 
des plus riches du marché : plus de 170 domaines IT sont déjà couverts en 2017, 
représentant plus de 2000 indicateurs de supervision. Ils vous permettent de 
déployer votre supervision rapidement et simplement. 

Vous souhaitez utiliser Centreon IMP ? Rien de plus simple. Suivez les instructions
ci-dessous.

Prérequis
---------

**1. Avoir installé Centreon 3.4**

Pour pouvoir utiliser Centreon IMP, vous devez absolument installer la dernière
version de Centreon open source, la version 3.4. Cette version intègre la dernière
version de Centreon Web c'est à dire la version 2.8.x. 

Centreon 3.4 est installable soit en "fresh install" avec `l'ISO d'installation de Centreon <https://download.centreon.com/>`_
soit en mettant à jour votre plateforme Centreon déjà installée. Pour ces deux étapes, 
rendez-vous dans les rubriques appropriées de la :ref:`documentation<install_from_packages>`

**2. Avoir une connexion internet**

Pour accéder à Centreon IMP, votre serveur de supervision Centreon central,
doit absolument avoir une connexion à internet. Cette dernière permettra de
procéder à la récupération des Plugin Packs de Centreon IMP via internet puis
leur installation.

.. note::
    Si votre serveur Centreon n'a pas accès directement à internet, pensez à 
    configurer un :ref:`proxy<impproxy>`.

Accédez aux Plugin Packs de Centreon IMP
----------------------------------------

Premiers Plugin Packs gratuits : avec Centreon 3.4, vous pouvez déjà récupérer
les 6 Plugin Packs délivrés gratuitement.

Pour récupérer les Plugin Packs de Centreon IMP, votre plate-forme Centreon open
source a besoin d’être connectée à votre compte Centreon IMP créé sur le site web
Centreon.

En créant votre compte Centreon IMP sur le site web de Centreon, vous pouvez :

* soit accéder à 5 Plugin Packs additionnels gratuitement (essai gratuit de Centreon IMP)
* soit souscrire à l’offre Centreon IMP et accéder à son catalogue de Plugin Packs prêts à l’emploi couvrant à ce jour plus de 170 domaines IT.

+--------------------------------+------------------------+----------------------+---------------------+
|                                | Centreon open source   | Centreon IMP Free    | Centreon IMP 1/6/12 |
|                                | (verison 3.4)          | Trial with online    | month online        |
|                                |                        | account              | subscription        |
+================================+========================+======================+=====================+
| 6 Free Plugin Pack(*)          |           x            |          x           |          x          |
+--------------------------------+------------------------+----------------------+---------------------+
| 5 additional Free Plugin Packs |                        |          x           |          x          |
+--------------------------------+------------------------+----------------------+---------------------+
| +160 Plugin Packs              |                        |                      |          x          |
+--------------------------------+------------------------+----------------------+---------------------+
|                          total | 6 Free Plugin Packs(*) | 11 Free Plugin Packs |  +170 Plugin Packs  |
+--------------------------------+------------------------+----------------------+---------------------+

Quels Plugin Packs sont disponibles à chaque niveau ?

- Centreon open source 3.4 - 6 Plugins packs gratuits : 
    - Cisco standard (SNMP)
    - Linux (SNMP)
    - MySQL
    - Printer standard (SNMP)
    - UPS Standard (SNMP)
    - Windows (SNMP)
    - Centreon (central)
    - Centreon DB
    - Centreon Poller
    - Centreon Map
    - Centreon MBI
- Essai gratuit de Centreon IMP avec un compte en ligne - 5 Plugin Packs additionnals : 
    - DHCP
    - DNS
    - FTP
    - HTTP
    - LDAP
- Centreon IMP avec une souscription de 1, 6, 12 mois : `Accès au catalogue de Plugin Packs <https://documentation-fr.centreon.com/docs/plugins-packs/en/latest/catalog.html>`_.

.. note::
    (*) Nous savons tous que 6 != 11
    Aux 6 Plugin Packs livrés gratuitement avec Centreon 3.4, s’ajoutent
    5 Plugin Packs Centreon (Central, DB, Poller, MAP et MBI). Comme ils
    servent à superviser la plateforme de supervision, nous préférons ne pas
    les compter, ces packs servant à superviser votre plateforme de supervision.

=============================================================
Guide d’accès via l’exemple de l’essai gratuit à Centreon IMP
=============================================================

Nous allons maintenant vous guider dans l’accès aux 5 Plugin Packs de
l’essai gratuit à Centreon IMP.

Pour cela il vous suffit de créer votre compte Centreon IMP sur le site web de Centreon.

Suivez les étapes décrites ci-dessous :

1. Créez votre compte Centreon IMP sur le site web de Centreon
--------------------------------------------------------------

La création de ce compte est gratuite et ne prend que quelques minutes.

Allez sur le site web de Centreon et rendez-vous sur la page de souscription à
`Centreon IMP <https://store.centreon.com/>`_. Cliquez
sur "Try it" dans la première colonne du tableau comparatif des offres.

.. image:: /_static/images/configuration/website/create_account_03.png
    :width: 1000 px
    :align: center

Créez maintenant votre compte Centreon IMP (rubrique New customer) et conservez
précieusement votre login et password. Ils vous seront nécessaire pour activer votre
compte Centreon IMP dans Centreon et ainsi avoir accès aux 5 Plugin Packs
additionnels gratuits.

.. image:: /_static/images/configuration/website/create_account_04.png
    :width: 1000 px
    :align: center

Après avoir complété tous les champs, validez en cliquant sur le bouton
**subscribe**. Votre compte est créé. Vous allez maintenant activer votre
compte Centreon IMP dans Centreon.

.. note::
    Veillez à indiquer une adresse email valide car la création d’un compte
    puis l’accès à l’essai gratuit de Centreon IMP intègre l’envoi automatisé
    de mails de confirmations/informations. 

.. image:: /_static/images/configuration/website/create_account_05.png
    :width: 1000 px
    :align: center

2. Activer son compte Centreon IMP dans Centreon
------------------------------------------------

Sur votre plate-forme Centreon, allez dans le menu **Administration ->
Extensions -> Subscription** et connectez-vous avec votre login et password de
votre compte Centreon IMP.

.. note::
    Il s’agit du compte que vous avez créé sur le site web de Centreon (étape précédente).

.. image:: /_static/images/configuration/imp3.png
   :align: center

.. note::
    Si le menu **Administration -> Extensions -> Subscription** n'est pas disponible sur votre Centreon,
    rendez-vous dans le menu **Administration -> Extensions -> Modules** et installez les modules
    suivants en cliquant sur l'icône d'installation situé à droite :

    * centreon-pp-manager
    * centreon-license-manager

En cliquant sur la flèche à côté du champ "Password", vous allez activer
votre compte Centreon IMP.

.. image:: /_static/images/configuration/imp4.png
   :align: center

Vous êtes maintenant connecté ! En cliquant sur le bouton **Setup** vous allez
maintenant accéder à l’installation de vos 5 Plugin Packs additionnels gratuits.

.. note::
    Avec ce compte vous pouvez connecter gratuitement plusieurs plate-forme à la fois pour faire 
    vos tests.

3. Installez vos 5 Plugin Packs additionnels gratuits
-----------------------------------------------------

Pour installer vos 5 Plugin Packs et bénéficier de l’essai gratuit Centreon
IMP, cliquez sur le bouton **Setup**. Vous accédez alors au Plugin Pack Manager.

Le Plugin Pack Manager vous permet de visualiser les Plugins Packs et de les
installer très facilement.

.. image:: /_static/images/configuration/imp1.png
   :align: center

Dans l’essai gratuit de Centreon IMP (notre exemple présent), seule une partie
du catalogue des Plugin Packs est accessible. Les Plugin Packs non disponibles
au téléchargement s’affichent en grisé et les boutons d’installation ou de mise
à jour seront alors indisponibles au passage de la souris (les 3 Plugin Packs
en bas à droite de notre capture).

Pour installer un Plugin Pack, il suffit de passer votre souris dessus puis de
cliquer sur l’icône d’installation (symbole + en vert).

+---------------------------------------------------+------------------------------------------------------+
|              **avant installation**               |               **après installation**                 |
+---------------------------------------------------+------------------------------------------------------+
| .. image:: /_static/images/configuration/imp5.png |  .. image:: /_static/images/configuration/imp6.png   |
+---------------------------------------------------+------------------------------------------------------+

N'oubliez pas que chaque pack nécessite également le déploiement de plugins.
Dans chaque monitoring procédure mise à disposition avec votre pack, vous
avez la liste des dépendances sous forme de rpm à installer pour que votre
supervision puisse démarrer.

.. note::
    Pensez à déployer tous les rpms sur chaque poller qui le nécessitent.
    Sinon votre supervision ne pourra pas fonctionner. En effet, seuls les
    Plugins Centreon des 11 premiers Plugin Packs ont été installés par défaut
    sur vos serveurs Centreon. Il est indispensable de suivre la procédure de
    déploiement de chaque Plugin Pack en cliquant sur l'icône "?".

Le Plugin Pack Manager dispose de filtres pour faciliter votre recherche d’un
Plugin Pack. Vous pouvez effectuer des recherches en fonction de :

  * Mot clé (Keyword)
  * Catégorie (Category)
  * Statut (Status)
  * Date de mise à jour du pack (Last update)

Pour accéder à la description d’un Plugin Pack il vous suffit de cliquer sur son icône.

.. image:: /_static/images/configuration/imp2.png
   :align: center

4. L’essayer c’est l’adopter !
------------------------------

Pour accéder à l’ensemble des Plugin Packs de Centreon IMP, vous pouvez
souscrire à l’Offre suivant différentes durées : 1 mois, 6 mois ou 1 an.
Bien sûr, le tarif est plus attractif pour les durées d’engagements les plus
longues.

Pour souscrire, rendez-vous sur notre site et sélectionnez `une souscription 
<https://store.centreon.com/>`_ 

Après l’achat de votre souscription Centreon IMP, vous allez activer votre
compte Centreon IMP dans Centreon. Pour cela, cliquez sur le bouton **Link**.

.. image:: /_static/images/configuration/website/link_01.png
    :width: 1000 px
    :align: center

Confirmez que c'est réellement ce que vous souhaitez faire.

.. image:: /_static/images/configuration/website/link_02.png
    :width: 1000 px
    :align: center

Vous avez maintenant accès à tous les Plugin Packs de Centreon IMP. Cliquez sur
le bouton **install** pour accédez au Plugin Pack Manager et procéder à
l’installation des Plugin Packs de votre choix. 

.. image:: /_static/images/configuration/website/link_03.png
    :width: 1000 px
    :align: center

Votre abonnement Centreon IMP intègre l’accès aux Plugin Packs, à leur
installation, à leur mise à jour, l’accès aux nouveaux Plugin Packs
développés par Centreon et une assistance en ligne. 

Si vous décidez d’arrêter votre abonnement Centreon IMP, vous ne perdrez pas
le bénéfice des Plugin Packs installés. Les hôtes et les points de contrôles
déployés grâce aux Plugin Packs resteront inchangés.

En revanche, vous perdrez l’accès aux mises à jour des Plugin Packs installés,
l’accès aux nouveaux Plugin Packs ainsi que l’accès à tous les Plugin Packs non
installés et vous n’aurez plus aucune assistance en ligne. 

.. note::
    Si vous décidez de changer de serveur et donc de migrer votre souscription sur un nouveau serveur, 
    vous serez obligé de passer par le service client. Nous n'avons pas encore intégré à nos outils 
    en ligne la possibilité de le faire. Pour cela, contactez imp at centreon dot com. 

.. note:: 
    Si vous avez des questions ou des problèmes relatifs à l'installation de IMP, vous pouvez contacter
    nos équipes techniques via l'adresse email suivant : imp at centreon dot com.

Si vous avez des questions n’hésitez pas à consulter notre `FAQ Centreon IMP <https://www.centreon.com/faq/faq-centreon-imp/>`_

