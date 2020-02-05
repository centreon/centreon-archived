.. _upgrade:

###########
Mise à jour
###########

Ce chapitre décrit le processus de mise à jour d'une plate-forme Centreon.

La procédure dépend de la méthode d'installation de votre plate-forme Centreon.
Sélectionner **Mise à jour RPM** si vous avez installé Centreon à partir de l'ISO
ou des RPMS, sinon sélectionner **A partir des sources**. Avant de mettre à jour
réaliser une sauvegarde.

.. warning::
    En cas de migration d'une plate-forme disposant du module **Centreon Poller
    Display 1.6.x**, référez-vous à la :ref:`procédure de migration suivante
    <migratefrompollerdisplay>`.

.. warning::
   Le processus de mise à jour ne peut démarrer qu'à partir des versions **2.8.0**
   et ultérieures. Si vous avez une version précédente, veuillez d'abord mettre à
   jour vers la dernière version *2.8.x*.

.. toctree::
    :maxdepth: 2

    from_packages_1904
    from_packages_1810
    from_packages_34
    from_sources
