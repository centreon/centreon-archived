Displayed procedure: template and overload
==========================================

To avoid too much workload on the procedure deployment, the functionality
allows administrator to setup a single procedure for hosts/services. 

So a procedure can be specified for a given host/service but can be specified as well for a host/service template. 

If a procedure is defined at template level, all children of the template will have the procedure attached as well unless overloaded by a specific one. The mechanism is identical to template
system in Centreon with inheritance.

**Centreon Knowledge Base** function is designed to avoid adding or updating manualy several times the same procedure in knowledge base.

When a user clicks on a host procedure:

- if a specific procedure  is defined for this host, its wiki page is displayed
- if no specific procedure is defined bu the host template has a procedure, the host template wiki page is displayed
- if host template has no procedure defined, parents template will be checked for a defined procedure
- finally if no procedure is defined in the template tree, a message will warn that there is no procedure defined for this host

It's the same for services.
