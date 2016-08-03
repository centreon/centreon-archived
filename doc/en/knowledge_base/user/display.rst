Displayed procedure: template and overload
==========================================

To avoid too much workload on the procedure deployment, the module
allows administrator to setup a single procedure for
hosts/services. So a procedure can be specified for a given
host/service but can be specified as well for a host/service
template. If a procedure is defined at template level, all children of
the template will have the procedure attached as well unless
overloaded by a specific one. The mechanism is identical to template
system in Centreon with inheritance.

*Centreon Knowledge Base* module is designed to:

- avoid to input or update several times the same procedure in the knowledge base
- be close to Centreon template system with inheritance, overload for quick deployment and maintenance

When a user clicks on a host procedure:

- if a specific procedure  is defined for this host, its wiki page is displayed
- if no specific procedure is defined bu the host template has a procedure, the host template wiki page is displayed
- if host template has no procedure defined, parents template will be checked for a defined procedure
- finally if no procedure is defined in the template tree, a message will warn that there is no procedure defined for this host

When a user click on a service procedure:

- if a specific procedure is defined for this host, its wiki page is displayed.
- if no specific procedure is defined but the host template, the service template wiki pasge is displayed
- if the service template has no procedure defined, parents template will be checked for a defined procedure
- if no procedure is defined is the template tree, then the module will check if any procedure is defined for the host attached to the service as previously describes


