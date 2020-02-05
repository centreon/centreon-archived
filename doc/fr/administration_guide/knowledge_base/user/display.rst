Algorithme d'affichage : Héritage et surcharge
==============================================

Pour éviter une trop grande charge de travail sur les procédures de déploiement,
cette fonctionnalité permet à l'administrateur de configurer une procédure simple pour les hôtes/services.

Ainsi une procédure peut être spécifiée pour un hôte/service donné mais aussi pour un modèle 
d'hôte et de service.

Si une procédure est définie au niveau d'un modèle, tous les enfants du modèle parent bénéficieront 
de cette procédure sauf en cas de surcharge.
Ce système est identique au système de modèle par héritage présent dans Centreon Web.

La fonctionnalité **Centreon Knowledge Base** est conçu pour éviter d'ajouter ou de mettre à jour manuellement plusieurs fois la même procédure dans la base de connaissances.

Quand un utilisateur clique sur la procédure d'un hôte :

- si une procédure spécifique est définie pour cet hôte, la page wiki de l'hôte s'affichera
- si aucune procédure spécifique n'est définie mais que le modèle d'hôte a une procédure alors c'est la page wiki de cette dernière qui s'affichera
- si le modèle d'hôte n'a pas de procédure de définie, alors les modèles d'hôte parents seront parcourus pour vérifier la présence d'une procédure
- finalement si aucune procédure n'est définie dans l'arbre des modèles, aucune procédure ne sera accessible.

Il en est de même pour les services.
