=========
Les hôtes
=========

Un hôte est une entité (la plupart du temps identifié par son adresse IP) qui correspond à une ressource du système d'informations.
Exemples : Un serveur, une imprimante réseau, un serveur NAS...

***********************
Configuration de l'hôte
***********************

Tous les ajouts d'hôtes se font dans **Configuration** ==> **Hôtes** ==> **Ajouter**.

Informations générales
----------------------

[METTRE UNE CAPTURE D'ECRAN]

 *	**Nom de l'hôte** : Définit le nom d'hôte qui sera utilisé par le moteur de supervision
 *	**Alias** : Alias pour l'hôte
 *	**Adresse IP/DNS** : Adresse IP ou nom DNS de l'hôte. Le bouton **Resolve** permet de résoudre le nom de domaine en interrogeant le serveur DNS du serveur central.
 *	**Communauté SNMP & Version** : Nom de la communauté SNMP et version de SNMP
 *	**Surveillé depuis le collecteur** : Serveur de supervision chargé de superviser cet hôte
 *	**Modèles d'hôte** : Permet d'associer à l'hôte un ou plusieurs modèles d'hôtes.
 
 En cas de conflits de paramètres, le modèle d'hôte au dessus écrase les modèles d'hôtes en dessous.
 Le bouton [METTRE UNE IMAGE] permet de déplacer le modèle d'hôte. Le bouton [METTRE UNE IMAGE] permet de supprimer le modèle d'hôte.
 
 *	**Créer aussi les services liés au modèle** : Si définit à **Oui**, Centreon génère automatiquement les services en se basant sur les modèles de services liés aux modèles d'hôtes définis au dessus.

Propriétés du contrôle de l'hôte
--------------------------------



Macros
------

Notification
------------



****************
Onglet Relations
****************


**********************
Traitement des données
**********************



****************************
Informations complémentaires
****************************

