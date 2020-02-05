=========================================
A propos du nouveau nommage des solutions
=========================================

**Pourquoi cette version s’appelle-t-elle 18.10 plutôt que 2.9 ?**

Il y a deux raisons. Pour faciliter la maintenance d’une plateforme Centreon,
tous ses composants logiciels et modules possèdent maintenant le même numéro
de version que la solution elle-même. Et parce que nous produirons désormais
une nouvelle solution tous les six mois, ce numéro de version prend la forme
AA.MM où AA désigne l’année de sortie et MM le mois. Vous trouverez plus de
détails dans `cet article de blog. <https://www.centreon.com/blog/centreon-18-10-approche-nouveau-nommage-versions/>`_

**Pendant combien de temps Centreon 3.4.6 / Centreon Web 2.8 sera-t-elle
supportée ?**

Nous corrigerons les problèmes critiques de Centreon 3.4.6 et tous ses
composants logiciels, comme Centreon Web 2.8, jusqu’en octobre 2019.

**Pendant combien de temps Centreon 18.10 sera-t-elle supportée ?**

Nous corrigerons les problèmes logiciels de Centreon 18.10 jusqu’en avril 2020.

**Quand sera disponible la version suivante de Centreon ?**

La version suivante sera disponible en avril 2019 et s’appellera Centreon 19.04.

**Quel est le plan prévisionnel de disponibilité des versions Centreon ?**

Centreon produira une nouvelle version tous les six mois.
:ref:`Référez-vous au chapitre.<life_cycle>`

=================================
Mettre à niveau en Centreon 18.10
=================================

**Quelles versions de Centreon puis-je mettre à niveau en Centreon 18.10 ?**

Les plateformes Centreon 2.6, 2.7 ou 2.8 peuvent être facilement mises à niveau
vers 18.10. Pour les plateformes plus anciennes, il est recommandé de mettre à
jour en 2.6 dans un premier temps, puis en 18.10.

**J’utilise Centreon open source version 2.x, puis-je mettre à niveau gratuitement
vers 18.10 ?**

Oui, vous pouvez mettre à niveau vers Centreon open source 18.10, qui est gratuit.

**J’utilise Centreon EPP, MAP, BAM ou MBI, puis-je mettre à niveau en 18.10 ?**

Si votre contrat de support est à jour, vous avez le droit de mettre à niveau
votre plateforme en Centreon 18.10. Vous devez contacter l’équipe support pour
obtenir l’accès aux nouveaux dépôts. Vous aurez aussi besoin de nouvelles clés
de licence.

**J’utilise Centreon EPP, MAP, BAM ou MBI, la version courante de ces modules
est-elle compatible avec Centreon 18.10 ?**

Non, vous devez mettre à niveau la plateforme complète, et donc mettre à niveau
ces modules dans leur version 18.10.

**J’ai souscrit en ligne à un abonnement IMP, puis-je mettre à niveau ma plateforme
en 18.10 ?**

Oui, si votre souscription IMP est valide, vous avez le droit de mettre à niveau
votre plateforme en 18.10.

**Sur quel système d’exploitation s’appuie Centreon 18.10 ?**

Centreon 18.10 s’appuie sur CentOS 7 est n’est pas compatible avec les versions
précédentes de CentOS.

**Ma plateforme Centreon s’appuie sur CentOS 6, puis-je mettre à niveau vers
18.10 ?**

Oui, vous pouvez mettre en oeuvre la procédure de migration d’une plateforme
Centreon depuis CentOS 6 vers CentOS 7.
:ref:`Référez-vous au chapitre.<upgradecentreon1904>`

**Quelle est la différence entre une migration et une mise à jour ?**

Si votre plateforme s’appuie déjà sur CentOS 7, une simple mise à jour logicielle
suffit à mettre à niveau en 18.10. Si votre plateforme s’appuie encore sur CentOS
6, une procédure de migration est nécessaire pour mettre à niveau en 18.10. 

:ref:`Référez-vous au chapitre.<upgradecentreon1904>`

**Où puis-je trouver la procédure de mise à jour d’un serveur Centreon ?**

:ref:`Référez-vous au chapitre.<upgrade>`

**Où puis-je trouver la procédure de migration d’un serveur Centreon ?**

:ref:`Référez-vous au chapitre.<upgradecentreon1904>`

**Lors de la migration de CentOS 6 vers CentOS 7, dois-je migrer les Pollers en
même temps que le serveur Central ?**

Il est possible de migrer les Pollers un par un, après le serveur Central. Le
serveur Central 18.10 est compatible avec la version précédente de Poller.

**J’utilise le module optionnel Poller Display sur l’un de mes Pollers. Lors de
la mise à niveau en 18.10, ce Poller doit-il être transformé en Remote Server ?**

Oui, car le module Poller Display n’est pas compatible avec Centreon 18.10. Ceci
est détaillé plus bas dans cette FAQ.

===========================================================
Clés de licence logicielle pour Centreon EPP, MAP, BAM, MBI
===========================================================

**J’utilise Centreon EPP, MAP, BAM et/ou MBI, pourquoi dois-je changer les clés
de licence logicielle lors de la mise à niveau en 18.10 ?**

Nous avons changé de technologie et le format des clés de licence a été modifié
avec Centreon 18.10. Les anciennes clés de licence ne sont pas compatible avec
Centreon 18.10.

**Comment puis-je obtenir mes nouvelles clés de licence ?**

Veuillez contacter l’équipe support. Vous devrez vous munir du fingerprint de
votre serveur.

**Comment puis-je trouver le fingerprint de mon serveur Centreon ?**

Celui-ci est accessible depuis l’IHM Centreon, menu **Administration > Extensions
> Subscription**

======================
Centreon Remote Server
======================

**La fonctionnalité Remote Server est-elle incluse dans la solution open source
de Centreon ?**

Oui, la nouvelle fonctionnalité Centreon Remote Server est incluse dans la
solution gratuite Centreon 18.10 open source.

**Est-ce que Remote Server est un complément à Poller Display, ou bien un
remplacement ?**

La fonctionnalité Centreon Remote Server remplace le module Poller Display.
Le module Poller Display n’est pas compatible avec Centreon 18.10. La
fonctionnalité Centreon Remote Server est intégrée à Centreon 18.10 est ne
nécessite pas de module additionnel.

**Quel est la différence entre Poller Display et Remote Server ?**

Poller Display est une module additionnel de Centreon, alors que Remote Server
est une fonctionnalité intégrée à la solution. L’ajout et la configuration d’un
Remote Server se fait en quatre étapes simples depuis l’IHM Centreon. Centreon
Remote Server combine des fonctionnalités disponibles dans les versions 1.5 et
1.6 de Poller Display, le tout de façon robuste et intégrée à Centreon.

**Le module Poller Display est-il compatible avec Centreon 18.10 ?**

Non, le module Poller Display n’est pas compatible avec Centreon 18.10.

**Comment puis-je mettre à niveau un Poller Display en Remote Server ?**

:ref:`Référez-vous au chapitre.<migratefrompollerdisplay>`

==============================================
Customer Experience Improvement Program (CEIP)
==============================================

Où puis-je trouver de l’information sur le programme CEIP d’amélioration de
l’expérience utilisateur Centreon ?

Une FAQ dédiée au programme CEIP est disponible dans :ref:`la documentation<ceip>`.

