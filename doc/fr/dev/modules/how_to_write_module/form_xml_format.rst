XML schema pour les formulaires
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Cette partie documente le format du fichier XML pour insérer des formulaires en base de données. Les fichiers XML peuvent être à l'aide du fichier XSD.

Structure générale
##################

Voici la structure générale du fichier XML.

.. highlight:: xml

   <?xml version="1.0" encoding="UTF-8"?>
   <forms>
     <form>
       <route/>
       <redirect/>
       <redirect_route/>
       <section>
         <block>
           <field>
             <help/>
             <attributes/>
             <versions/>
           </field>
         </block>
       </section>
     </form>
     <wizard>
       <route/>
       <step>
         <field/>
       </step>
     </wizard>
   </form>

Vision détaillée
################

La balise **forms** peut contenir un ou plusieurs forms et un ou plusieurs wizards.

L'élément form
^^^^^^^^^^^^^^

.. highlight:: xml

   <form
     name="FormName"
   >
     <route/>
     <redirect/>
     <redirect_route/>
     <section/>
   </form>

Attributs
*********

name::
  Le nom du formulaire. Ce nom doit être composé de caractères alphanumérique, underscore, et sans espace.

Éléments
********

route::
  L'URI de la route qui accueil le formulaire.

redirect::
  S'il y a une redirection après la validation du formulaire.

  Valeurs : 0 ou 1

redirect_route::
  L'URI de la redirection.

section::
  Une section du formulaire. Il peut avoir plusieurs sections pour un formulaire.

  Voir l'élément section pour la configuration.

L'élément section
^^^^^^^^^^^^^^^^^

.. highlight:: xml

   <section
     name="SectionName"
   >
     <block/>
   </section>

Attributs
*********

name::
  Le nom de la section.

Éléments
********

block::
  Un block de la section. Il peut avoir plusieurs blocs pour une section.

  Voir l'élément block pour la configuration.

L'élément section
^^^^^^^^^^^^^^^^^

.. highlight:: xml

   <block
     name="BlockName"
   >
     <field/>
   </block>

Attributs
*********

name::
  Le nom du block.

Éléments
********

field::
  Un champs du formulaire. Il peut avoir plusieurs champs dans un block.

  Voir l'élément field pour la configuration.

L'élément field
^^^^^^^^^^^^^^^

.. highlight:: xml

   <field
     name="FieldName"
     label="Label"
     default_value=""
     advanced="0"
     type="checkbox"
     parent_field=""
     mandatory="0"
   >
     <help/>
     <attributes/>
     <versions/>
   </field>

Attributs
*********

name::
  Le nom du champs. Ce nom doit être composé de caractères alphanumérique, underscore, et sans espace.

label::
  Le label du champs. Ce label sera affiché pour nommer le champs dans la page du formulaire.

default_value::
  La valeur par défaut du champs.

advanced::
  Si le champs est visible dans la version simplifié du formulaire.

  Valeurs : 0 ou 1

type::
  Le type du champs.

  Les types sont: checkbox, email, file, integer, ipaddress, password, radio, select, selectimage, text, textarea. Des types particuliers peut être utilisés.

parent_field::
  Le champs parent.

mandatory::
  Si le champs est obligatoire ou non.

  Valeurs : 0 ou 1

Éléments
********

help::
  L'aide du champs. C'est une chaîne de caractères qui sera soumise à la traduction.

attributes::
  Les attributs liés au champs.

  Voir l'élément attributes pour la configuration.


versions::
  La liste des versions quand le formulaire peut gérer plusieurs versions différentes suivant les versions de programmes.

  Exemple avoir plusieurs versions du moteur de supervision suivant les pollers (Centreon Engine 1.3 et 1.4...)

  Voir l'élément versions pour la configuration.

L'élément attributes
^^^^^^^^^^^^^^^^^^^^

.. highlight:: xml

   <attributes>
     <choices/>
     <object_type/>
     <defaultValuesRoute/>
     <listValuesRoute/>
     <multiple/>
   </attributes>

Attributs
*********

Éléments
********

choices::
  Un ensemble de clés/valeurs sous la forme elementName/test.

  Exemple:

.. highlight:: xml
 
   <choices>
     <key1>value1</key1>
     <key2>value1</key2>
   </choices>

object_type::
  Le type de l'objet pour la récupération des informations à travers une requête AJAX.

defaultValuesRoute::
  L'URI pour récupérer la liste des éléments associés à ce champs.

listValuesRoute::
  L'URI pour récupérer la liste des éléments déjà sélectionnés.

multiple::
  Si la sélection peut être multiple pour un champs de type select.

L'élément versions
^^^^^^^^^^^^^^^^^^

.. highlight:: xml

   <versions>
     <version/>
   </versions>

Attributs
*********

Éléments
********

version::
  La version où le champs est lié au block.

L'élément wizard
^^^^^^^^^^^^^^^^

.. highlight:: xml

  <wizard
    name="WizardName"
  >
    <step/>
  </wizard>

Attributs
*********

name::
  Le nom de l'assistant. Ce nom doit être composé de caractères alphanumérique, underscore, et sans espace.

Éléments
********

step::
  Une étape de l'assistant. Il peut avoir plusieurs étapes dans un assistant.

  Voir l'élément step pour la configuration.

L'élément step
^^^^^^^^^^^^^^

.. highlight:: xml

   <step
     name="StepName">
     <field/>
   </step>

Attributs
*********

name::
  Le nom de l'étape.

Éléments
********

field::
  Le champs affiché dans l'assistant. Ce champs doit exister dans le formulaire.

  Voir l'élément field pour la configuration.

L'élément field
^^^^^^^^^^^^^^^

.. highlight:: xml

  <field
    name="FieldName"
    mandatory="0"
  />

Attributs
*********

name::
  Le nom du champs. Il doit être identique au champs du formulaire auquel il est lié.

mandatory::
  Si le champs est obligatoire ou non.

  Valeurs : 0 ou 1

Éléments
********
