==============
Les catégories
==============

Les catégories sont utilisées afin de pouvoir définir des ACLs sur les hôtes et les services. Le but est de pouvoir classer les hôtes ou les services au sein d'une même catégorie.

Centeon 2.4 avait intégré une nouvelle fonctionnalité appelée "Criticité". A parti de la version 2.5, les niveaux de criticité sont liés à une catégorie, ils sont devenus un type de catégorie.
Un niveau de criticité est un indicateur permettant de définir la criticité d'un hôte ou d'un service. Le but est de pouvoir traiter les problèmes des hôtes ou des services par ordre de priorité.
Grâce à ce système, il est ainsi possible de filtrer les objets dans les vues "Supervision" par criticité.

**********************
Les catégories d'hôtes
**********************

Pour ajouter une catégorie d'hôtes :

#.	Rendez-vous dans le menu **Configuration** ==> **Hôtes**
#.	Dans le menu de gauche, cliquez sur **Catégories**
#.	Cliquez sur **Ajouter**
 
[ TODO METTRE UNE CAPTURE D'ECRAN]

*	Les champs **Nom de la catégorie d'hôtes** et **Alias** contiennent respectivement le nom et l'alias de la catégorie d'hôte.
*	La liste **Hôtes liés** permet d'ajouter des hôtes à la catégorie.
*	Si un modèle d'hôte est ajouté à **Lié au modèle d'hôte** alors tous les hôtes qui héritent de ce modèle appartiennent à cette catégorie.
*	La case **Est de type criticité** signifie que la catégorie d'hôtes a un niveau de criticité.
*	Les champs **Niveau** et **Icônes** définissent respectivement un niveau de criticité et une icône associée.
*	Les champs **Statuts** et **Commentaires** permettent d'activer ou de désactiver la catégorie d'hôte et de commenter celle-ci.

**************************
Les catégories de services
**************************

Pour ajouter une catégorie de services :

#.	Rendez-vous dans le menu **Configuration** ==> **Services**
#.	Dans le menu de gauche, cliquez sur **Catégories**
#.	Cliquez sur **Ajouter**
 
[ TODO METTRE UNE CAPTURE D'ECRAN]

*	Les champs **Nom** et **Description** définissent le nom et la description de la catégorie de service.
*	Si un modèle de service appartient à **Liée aux modèles de services** alors tous les services appartenant à ce modèle de services font partie de cette catégorie.
*	La case **Est de type criticité** signifie que la catégorie de service a un niveau de criticité.
*	Les champs **Niveau** et **Icônes** définissent respectivement un niveau de criticité et une icône associée.
*	Le champ **Statuts** permet d'activer ou de désactiver la catégorie de services.
 
