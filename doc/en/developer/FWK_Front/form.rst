Components
==========

Input Text
^^^^^^^^^^

.. raw:: html

   <iframe seamless src="../../_static/FWK-Front/input.html" style="border: 0; width: 100%; height: 60px"></iframe>

- HTML

.. code-block:: html

   <div class="form-group">
       <label class="label-controller floatLabel" for="input_label">Name</label>
       <input class="form-control" type="text" id="input_label" placeholder="Name">
       <cite style="display:none">Help sentence</cite>
       <div class="inheritance">value</div>
    </div>

.. NOTE::
   The help is dynamically generated so 'style="display:none" must be removed'

Textarea
^^^^^^^^^^
.. raw:: html

   <iframe seamless src="../../_static/FWK-Front/textarea.html" style="border: 0; width: 100%; height: 105px"></iframe>

.. code-block:: html

   <div class="form-group">
       <label class="label-controller floatLabel" for="textarea">Textarea</label>
       <textarea class="form-control" id="textarea" placeholder="Textarea"></textarea>
       <cite style="display:none">Help sentence</cite>
   </div>

.. NOTE::
   The help is dynamically generated so 'style="display:none" must be removed'

Radiobox
^^^^^^^^^^
.. raw:: html

   <iframe seamless src="../../_static/FWK-Front/radiobox.html" style="border: 0; width: 100%; height: 105px"></iframe>

.. code-block:: html

   <div class="form-group">
       <label class="label-controller floatLabel">Radiobox</label>

       <div class="choiceGroup">
           <label class="label-controller" for="host_activate1">
               <input id="host_activate1" type="radio" name="host_activate" value="1" checked="checked" required="">
               Choice 1
           </label>
           <label class="label-controller" for="host_activate2">
               <input id="host_activate2" type="radio" name="host_activate" value="0" required="">
               Choice 2
           </label>
       </div>

       <cite style="display:none">Help sentence</cite>
   </div>

.. NOTE::
   The help is dynamically generated so 'style="display:none" must be removed'