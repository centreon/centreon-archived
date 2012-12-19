.. _commands:

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

.. _command_args_ref:

========================  ==============================================================================
 Field name                Description
========================  ==============================================================================
 Command Name              Name which will be used for identifying the command

 Command Type              Select the *Check* type

 Command Line              This will be executed by the scheduler, note that this line 
                           contains macros that will be replaced before execution. Always 
                           possible macros when possible. e.g: ``$USER1$/check_centreon_dummy``

 Enable shell              If your command requires shell features like pipes, redirections, globbing 
                           etc. check this box. If you are using Monitoring Engine this option cannot 
                           be disabled. Note that commands that require shell are slowing down the 
                           poller server

 Argument example          This will provide argument example to the end users. The example 
                           apply to ``$ARGn$`` macros only and the expression is separated by the ``!``
                           character. In our case, *Hello world* will match ``$ARG1$`` and *0* will 
                           match ``$ARG2$``

 Argument Descriptions     The argument description provided here will be displayed instead of the 
                           technical names like ``$ARGn$``

 Connectors                Connectors are run in background and execute specific commands without the 
                           need to execute a binary, thus enhancing performance. This feature is 
                           available in Centreon Engine (**>= 1.3**)

 Graph template            The optional definition of a graph template will be used as default graph 
                           template, when no other is specified

 Comment                   Comments regarding the command

========================  ==============================================================================

End users may not know the meaning of the arguments even though you
provided an example. You can hit the *Describe argument* button and
give a description to each of your ``$ARGn$`` macros.

.. image:: /_static/images/user/check_arg_3.png
   :align: center

Hit the *Save* button of the modal box to apply the descriptions, then
hit the *Save* button of the form to save your check command.

Notification Commands
=====================

Notification commands work pretty much like check commands but they
are used for notifying users and ``$ARGn$`` are not supported here.

.. image:: /_static/images/user/notif_check_1.png
   :align: center

Select the *Notification* type. The following command line will send
an email to the contact with the **mail** binary:

.. image:: /_static/images/user/notif_cmd_2.png
   :align: center

See the definition of the available options :ref:`upper <command_args_ref>`.
