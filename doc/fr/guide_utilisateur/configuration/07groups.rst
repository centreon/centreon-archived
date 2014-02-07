===========
Les groupes
===========

Un groupe permet de regrouper un ou plusieurs objets. Il existe trois types de groupes : hôtes, services et les contacts.

Les groupes d'hôtes et de services servent principalement lors de la visualisation des graphiques ou pour regrouper les objets.
Les groupes de contacts sont utilisés principalement pour la mise en place d'ACLs.

*******************
Les groupes d'hôtes
*******************

Pour ajouter un groupe d'hôtes :

 #.	Rendez-vous dans **Configuration** ==> **Hôtes**
 #. Dans le menu de gauche, cliquez sur **Groupes d'hôtes**
 #. Cliquez sur **Ajouter**
 
[METTRE UNE CAPTURE D'ECRAN]

 *	Les champs **Nom du groupe d'hôtes** et **Alias** regroupent le nom et l'alias du groupe d'hôte
 *	La liste **Hôtes liés** permet d'ajouter des hôtes au sein du nouveau groupe d'hôtes
 *	Le champ **Notes** permet d'ajouter des notes optionnelles concernant le groupe d'hôtes
 *	Le champ **URL** définit une URL qui peut être utilisée pour donner davantage d'informations sur le groupe d'hôtes
 *	Le champ **URL d'action** définit une URL habituellement utilisée pour donner des informations d'actions sur le groupe d'hôtes (maintenance...)
 *	Le champ **Icône** indique l'icône à utiliser pour le groupe d'hôtes
 *	Le champ **Icône pour la carte** est l'icône utilisée pour la cartographie
 *	Le champ **RRD retention** [PAS DE TRADUCTION DISPO] est exprimé en jours, il permet de définir la durée de rétention des services appartenant à ce groupe d'hôte au sein de la base de données RRD [QUESTION : Si vide, quelles valeurs est prise ? La valeur de configuration par défaut ?]
 *	Les champs **Statuts** et **Commentaires** permettent d'activer ou de désactiver le groupe d'hôtes et de commenter celui-ci.

***********************
Les groupes de services
***********************

Pour ajouter un groupe de services :

 #.	Rendez-vous dans **Configuration** ==> **Services**
 #. Dans le menu de gauche, cliquez sur **Groupes de services**
 #. Cliquez sur **Ajouter**
 
[METTRE UNE CAPTURE D'ECRAN]

 *	Les champs **Nom du groupe de services** et **Description** regroupent le nom et la description du groupe de services.
 *	La liste **Services d'hôtes liés** permet de choisir les différents services qui feront partie de ce groupe
 *	La liste **Services liés au groupe d'hôtes** permet de choisir les services liés à un groupe d'hôtes qui feront partie de ce groupe
 *	Si un modèle de service appartient à la liste **Modèles de service liés** alors tous les services qui héritent de ce modèle appartiennent à ce groupe de services
 *	Les champs **Statuts** et **Commentaires** permettent d'activer ou de désactiver le groupe de services et de commenter celui-ci.

***********************
Les groupes de contacts
***********************

Pour ajouter un groupe de contacts :

 #.	Rendez-vous dans **Configuration** ==> **Utilisateurs**
 #. Dans le menu de gauche, cliquez sur **Groupes de contacts**
 #. Cliquez sur **Ajouter**
 
[METTRE UNE CAPTURE D'ECRAN]

 *	Les champs **Nom du groupe de contacts** et **Alias** définissent le nom et l'alias du groupe de contacts
 *	La liste **Contacts liés** permet d'ajouter les contacts au groupe de contacts
 *	Les champs **Statuts** et **Commentaires** permettent d'activer ou de désactiver le groupe de contacts et de commenter celui-ci.