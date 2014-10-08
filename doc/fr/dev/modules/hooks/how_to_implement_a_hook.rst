Comment implémenter un hook
---------------------------

Afin d'implémenter un hook, il faut tout d'abord l'enregistrer lors de l'installation de votre module via
le fichier **modules/YourModule/install/hooks.json**. Les noms de hooks doivent toujours commencer par le
préfix **display**.

Exemple de fichier **hooks.json**::
   [
      {
         "name": "displayDummy",
         "description" : "Displays elements somewhere on the interface"
      }
   ]

Les hooks pour l'affichage sont appelés avec une fonction smarty qui a pour argument un *container*.
Le paramètre *container* est une chaîne HTML avec un macro **[hook]** qui sera remplacé par le retour du hook.
Vous pouvez également passer des paramètres sous forme d'un tableau aux hooks.

Un exemple de votre fichier de template::
   <div>
      {hook name='displayDummy' container='<div>[hook]</div>' params=$anArray}
   </div>
