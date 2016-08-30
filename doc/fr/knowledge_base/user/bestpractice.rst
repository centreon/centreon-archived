Pratique d'excellence pour le déploiement
=========================================

Pour déploiement Centreon KnowledgeBase de la meilleure manière, nous vous 
recommandons fortement d'utiliser les différents niveaux du système d'héritage.
La pratique d'excellence est donc de définir les bases de connaissances au niveau des modèles.

Voici un exemple d'une configuration pour un modèle d'hôte :

- Linux > Generic-hosts
- Windows > Generic-hosts
- RedHat > Linux
- Debian > Linux
- Active-Directory > Windows
- LDAP > Linux

Pour configurer Centreon KnowledgeBase pour le modèle d'hôte *RedHat*, vous pouvez 
suivre la démarche décrite dans :ref:`wiki-page-link`. Dans l'arbre des modèles, il apparaît 
que le modèle *RedHat* hérite de deux autres modèles : *Linux* et *Generic-hosts*.
Dans cet exemple tous les hôtes utulisant le modèle d'hôte *RedHat* auront une nouvelle base de connaissances 
qui leur seront attachées.

Vous pouvez configurer une base de connaissance dans un niveau plus élevé dans l'arbre des modèles, 
ce qui aura pour effet d'impacter plus d'hôtes. Par exemple si vous définissez une base de connaissances 
pour le modèle d'hôte *Linux*, tous les hôtes utilisant les modèles d'hôtes *RedHat*, *Debian* et *LDAP* 
auront une base de connaissances attachées par héritage. Tout cela parce que *Linux* est le modèle parent. 
Le comportement est le même pour les modèles de services.

Dans tous les cas, vous pouvez surcharger les bases de connaissances créées par héritage 
pour un hôte / service / modèle en créant une base de connaissance spécifique 
pour cet hôte / service / modèle en particulier.


 .. warning::

       Pour suprimmer le lien d'une base de connaissance pour un hôte / service / modèle 
       spécifique, editer l'objet et supprimer le contenu du champ *URL* dans l'onglet *Informations détaillées*. 
       Si l'objet hérite d'une base de connaissance d'un modèle, la suppression du contenu du champ surchargera 
       la valeur du modèle et supprimera le lien de la base de connaissance.
