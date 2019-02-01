====================
Centreon Web 18.10.3
====================

Enhancements
------------

* [Configuration] Avoid huge memory consumption when generating configuration (PR/#7072)
* [Remote Server] Add one-peer retention (Issues/#6910,#6978,#6987 - PR/#6959)
* [UI] Menus of banner can be opened/closed by clicking on icon (PR/#7127)
* [UI] Improve tooltip positionning in monitoring listing (PR/#7140)

Bug fixes
---------

* [Backup] Configuration backup correctly done using scp (PR/#7112)
* [Configuration] Unset service/contact relations if SETCONTACT clapi method used (PR/#7115)
* [Configuration] Include check_centreon_dummy during installation process (Issue/#7019)
* [UI] Date picker failed when no language selected (PR/#7046)
* [UI] Manage pagination in all custom select components (PR/#7102)
* [UI] Avoid duplicated en_US language selection in user settings (PR/#7094)
* [UI] Fix issue with shared views and multi widgets (PR/#7126)
* [UI] Display configuration has changed for all pollers (PR/#7107)
* [Remote Server] Replace special characters when setting up a remote server (Issue/#6979 - PR/#7133)
* [Remote Server] Prevent access to ressources configuration not defined on remote (PR/#7136)
* [Widget/host-monitoring] Issue with sorting options fixed (PR/#59)
