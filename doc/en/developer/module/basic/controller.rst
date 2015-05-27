Controller
^^^^^^^^^^

Routing
"""""""

**[base url] / [slug module name] / [target controler/action with annotation]**

Example : [base url]/centreon-configuration/host/1/hosttemplate target controlers in the module named 'CentreonConfigurationModule' 

and call the action that have this following **@route** annotation specified : /host/[i:id]/hosttemplate


.. code-block:: php

   <?php
   class HostController extends FormController
   {
        
       /**
         * Get host template for a specific host
         *
         * @method get
         * @route /host/[i:id]/hosttemplate
         */
        public function hostTemplateForHostAction()
        {
           //code here
        }
        
    }
    
    
Note : The only convention here is on the function name that is required to be suffixed with the word **Action**. The url : /hostfoo/1/hosttemplatefoo will work with the following **@route** annotation : 

.. code-block:: php

   <?php
   class HostController extends FormController
   {
        
       /**
         * Get host template for a specific host
         *
         * @method get
         * @route /hostfoo/[i:id]/hosttemplatefoo
         */
        public function hostTemplateForHostAction()
        {
           //code here
        }
        
    }


For parameters, you have to use the nonenclature specified here : https://github.com/chriso/klein.php#routing

To get the paramters in the controler you have to use the function **$this->getParams($type = "")** inherited from HttpCore class. 

=======  ================= 
$type    return     
=======  =================  
""       all params 

"get"    only get params

"post"   only post params

"named"  only named params

=======  =================

Display a page
""""""""""""""

.. code-block:: php

   <?php
   class TestController extends FormController
   {
        
       /**
         * Get host template for a specific host
         *
         * @method get
         * @route /host/[i:id]/hosttemplate
         */
        public function hostTemplateForHostAction()
        {
           //code here
        }
        
    }

