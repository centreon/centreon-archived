.. _install:

############
Installation
############

Ce chapitre décrit les différentes étapes de mise en place d'une plate-forme
de supervision basée sur Centreon.

La plate-forme de supervision peut-être installée de plusieurs manières. Cependant,
**nous vous recommandons vivement d'utiliser Centreon ISO pour installer votre
plate-forme**. Profitez ainsi de nos travaux d'industrialisation de l'installation
et de la mise à jour de l'environnement. Profitez également des optimisations
installées en standard par le système.

L'installation de Centreon peut être effectuée à partir des sources (tar.gz)
mais le travail est plus complexe. De plus l'installeur ne sera supporté que
par la communauté.

Avant toute installation, veillez à bien suivre les pré-requis d'installation
et de dimensionnement (ressources CPU, mémoire, disques, partitionnement, etc...).
Prenez également soin de bien choisir le type d'architecture qu'il convient de
pour vos besoins.

Enfin, vous pourrez procéder à l'installation de la plate-forme.

.. toctree::
   :maxdepth: 1

   prerequisites
   architecture/index
   downloads
   from_iso
   from_packages
   from_VM
   from_sources

Pour tester rapidement Centreon à partir d'un serveur CentOS ou Red Hat en version 7.x, vous pouvez
exécuter la commande suivante en **root** : ::

    # curl -L https://raw.githubusercontent.com/centreon/centreon/master/unattended.sh | sh
