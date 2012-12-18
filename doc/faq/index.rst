==========================
Frequently Asked Questions
==========================

**************
Administration
**************

How does the *Empty all services data* action work?
===================================================

In order to preserve global performance, this action won't remove all
data from the database right after you launched it. Entries will be
removed from ``index_data`` and ``metrics`` tables but not from
``data_bin``.

The main reason for that is ``data_bin`` quickly stores a huge amount
of data and uses the ``MyISAM`` engine which doesn't support per-row
locking. If you try to remove too many entries simultaneously, you
could block all your database for several hours.

Anyway, it doesn't mean the data will stay into your database
indefinitely. It will be removed in the future, depending on you data
retention policy.
