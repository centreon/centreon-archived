Les hooks pour l'affichage
--------------------------

Les hooks pour l'affichage sont appelé avec un fonction smarty qui a pour argument un *container*.
Le paramètre *container* est un chaîne HTML avec un macro **[hook]** qui sera remplacé par le retour du hook.

displayLeftMenu
~~~~~~~~~~~~~~~

Description
###########

Permet d'ajouter des informations dans le menu latéral gauche.

Paramètres
##########

Aucun

Exemple
#######

.. highlight:: html
  
   <div>
     {hook name='displayLeftMenu' container='<ul>[hook]</ul>'}
   </div>
