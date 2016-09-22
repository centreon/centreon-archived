Bonnes pratiques de déploiement
===============================

Pour déployer Centreon KnowledgeBase de la meilleure manière, nous vous 
recommandons fortement d'utiliser les différents niveaux du système d'héritage.

La bonne pratique est donc de définir les bases de connaissances au niveau des modèles.

Voici un exemple d'une configuration pour un modèle d'hôte :

- Linux > Generic-hosts
- Windows > Generic-hosts
- RedHat > Linux
- Debian > Linux
- Active-Directory > Windows
- LDAP > Linux

Pour configurer Centreon KnowledgeBase pour le modèle d'hôte **RedHat**, vous pouvez 
suivre la démarche décrite dans :ref:`wiki-page-link`. 

Dans l'arbre des modèles, il apparaît que le modèle *RedHat* hérite de deux autres modèles : *Linux* et *Generic-hosts*.

Dans cet exemple tous les hôtes utilisant le modèle d'hôte *RedHat* auront une nouvelle base de connaissances disponible.

Vous pouvez configurer une procédure dans un niveau plus élevé dans l'arbre des modèles.

Par exemple si vous définissez une procédure pour le modèle d'hôte *Linux*, tous les hôtes utilisant les modèles d'hôtes *RedHat*, *Debian* et *LDAP* 
hériteront de cette procédure par héritage.

Le comportement est le même pour les modèles de services.

 .. warning::

       Pour suprimmer le lien d'une base de connaissance pour un hôte / service / modèle 
       spécifique, editer l'objet et supprimer le contenu du champ **URL** dans l'onglet **Informations détaillées**. 
       
       Si l'objet hérite d'une base de connaissance d'un modèle, la suppression du contenu du champ surchargera 
       la valeur du modèle et supprimera le lien de la base de connaissance.
