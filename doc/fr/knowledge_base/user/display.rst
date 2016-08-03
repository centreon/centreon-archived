Algorithme d'affichage : Héritage et surcharge
==============================================

Pour éviter une trop grande charge de travail sur les procédures de déploiement,
le module permet à l'administrateur de configurer une procédure simple pour les hôtes/services.
Ainsi une procédure peut être spécifiée pour un hôte/service donné mais aussi pour un modèle 
d'hôte et de service.
Si une procédure est définie au niveau d'un modèle, tous les enfants du modèle parent bénéficieront 
également de cette procédure sauf si elle est surchargée par une autre procédure spécifique à l'enfant.
Ce système est identique au système de modèle par héritage présent dans Centreon.

Le module *Centreon Knowledge Base* est conçu pour :

- éviter d'ajouter ou de mettre à jour manuellement plusieurs fois la même procédure dans la base de connaissances.
- être proche du système de modèle par héritage de Centreon en surchargeant pour un déploiement et une maintenance plus rapide.

Quand un utilisateur clique sur la procédure d'un hôte :

- si une procédure spécifique est définie pour cet hôte, la page wiki de l'hôte s'affichera
- si aucune procédure spécifique n'est définie mais que le modèle d'hôte a une procédure alors c'est la page wiki de cette dernière qui s'affichera
- si le modèle d'hôte n'a pas de procédure de définie, alors les modèles d'hôte parents seront parcourus pour vérifier la présence d'une procédure
- finalement si aucune procédure n'est définie dans l'arbre des modèles, un message d'avertissement indiquera qu'il n'y a pas de procédure pour cet hôte

Quand un utilisateur clique sur la procédure d'un service :


- si une procédure spécifique est définie pour ce service, la page wiki de ce service s'affichera
- si aucune procédure spécifique n'est définie mais que le modèle de service a une procédure alors c'est la page wiki de cette dernière qui s'affichera
- si le modèle de service n'a pas de procédure de définie, alors les modèles de service parents seront parcourus pour vérifier la présence d'une procédure
- si aucune procédure n'est définie dans l'arbre des modèles, alors le module vérifiera si une procédure est définie pour l'hôte attaché au service comme décrit précédemment
