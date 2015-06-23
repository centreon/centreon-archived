Form Components
==============

.. NOTE::
   Centreon use **Bootsrap 3**, a front-end framework.

Input Text
^^^^^^^^^^

.. raw:: html

   <iframe seamless src="../../_static/front/input.html" style="border: 0; width: 100%; height: 60px"></iframe>

- HTML

.. code-block:: html

   <div class="form-group">
        <div class="col-md-6">
           <label class="label-controller floatLabel" for="input_label">Name</label>
           <input class="form-control" type="text" id="input_label" placeholder="Name">
           <cite style="display:none">Help sentence</cite>
           <div class="inheritance">value</div>
        </div>
    </div>

.. NOTE::
   << style="display:none">> must be removed because the help is dynamically generated.

Textarea
^^^^^^^^
.. raw:: html

   <iframe seamless src="../../_static/front/textarea.html" style="border: 0; width: 100%; height: 105px"></iframe>

.. code-block:: html

   <div class="form-group">
       <label class="label-controller floatLabel" for="textarea">Textarea</label>
       <textarea class="form-control" id="textarea" placeholder="Textarea"></textarea>
       <cite style="display:none">Help sentence</cite>
   </div>

.. NOTE::
   << style="display:none">> must be removed because the help is dynamically generated.

Radiobox
^^^^^^^^
.. raw:: html

   <iframe seamless src="../../_static/front/radiobox.html" style="border: 0; width: 100%; height: 105px"></iframe>

.. code-block:: html

   <div class="form-group">
       <label class="label-controller floatLabel">Radiobox</label>

       <div class="choiceGroup">
           <label class="label-controller radio-styled" for="host_activate1">
               <input id="host_activate1" type="radio" name="host_activate" value="1" checked="checked" required="">
               <span></span>
               Choice 1
           </label>
           <label class="label-controller radio-styled" for="host_activate2">
               <input id="host_activate2" type="radio" name="host_activate" value="0" required="">
               <span></span>
               Choice 2
           </label>
       </div>

       <cite style="display:none">Help sentence</cite>
   </div>

.. NOTE::
   << style="display:none">> must be removed because the help is dynamically generated.

Checkbox
^^^^^^^^
.. raw:: html

   <iframe seamless src="../../_static/front/checkbox.html" style="border: 0; width: 100%; height: 105px"></iframe>

.. code-block:: html

   <div class="form-group">
       <label class="label-controller floatLabel">Checkbox</label>

        <div class="choiceGroup">
           <label class="label-controller checkbox-styled" for="host_activate1">
               <input id="host_activate1" type="checkbox" name="host_activate" value="1" checked="checked" required="">
               <span></span>
               Choice 1
           </label>
           <label class="label-controller checkbox-styled" for="host_activate2">
               <input id="host_activate2" type="checkbox" name="host_activate" value="0" required="">
               <span></span>
               Choice 2
           </label>
        </div>

       <cite style="display:none">Help sentence</cite>
   </div>

.. NOTE::
   << style="display:none">> must be removed because the help is dynamically generated.