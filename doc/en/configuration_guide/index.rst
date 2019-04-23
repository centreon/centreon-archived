=============
Configuration
=============

This chapter will allow you to know all the configuration mechanisms of your supervision system. This stage of implementation of the supervision must be reflected to set up a supervision deployment strategy. Remember, the goal is to have a scalable and maintainable system.

Do forget to think about setting up a global strategy of the configuration in order to make able to have global actions configurations. For that, mechanisms are in place in Centreon to simplify your life such as :ref:`guest models and services <hosttemplates>`.

.. toctree::
   :maxdepth: 1

   actions
   hosts
   services
   commands
   timeperiod
   contact
   groups
   category
   models
   advanced_configuration/index
   process_description/index
   deploy

Once setup is finished, supervision will allow you to get informations on the health status of your IT systems. For having more information regarding the operation interface, please refer to the exploitation guide.
