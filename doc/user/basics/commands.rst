********
Commands
********

Check Commands
==============

Check commands are used for checking hardware and/or application
statuses of your Hosts/Services.

Command Creation
----------------

.. image:: /_static/images/user/add_command_1.png
   :align: center



.. image:: /_static/images/user/check_arg_2.png
   :align: center

========================  ==============================================================================
 Field name                Description
========================  ==============================================================================
 Command Name              Name which will be used for identifying the command

 Command Type              Select the *Check* type

 Command Line              This will be executed by the scheduler, note that this line 
                           contains macros that will be replaced before execution. Always 
                           possible macros when possible. e.g: ``$USER1$/check_centreon_dummy``

 Argument example          This will provide argument example to the end users. The example 
                           apply to ``$ARGn$`` macros only and the expression is separated by the ``!``
                           character. In our case, *Hello world* will match ``$ARG1$`` and *0* will 
                           match ``$ARG2$``

========================  ==============================================================================

End users may not know the meaning of the arguments even though you
provided an example. You can hit the *Describe argument* button and
give a description to each of your ``$ARGn$`` macros.

.. image:: /_static/images/user/check_arg_3.png
   :align: center

Hit the *Save* button of the modal box to apply the descriptions, then
hit the *Save* button of the form to save your check command.
