###################
Centreon Web 2.8.37
###################

Bug fixes
=========

- [Core] Update centreon copyright dates
- [Lib] moment-timezone 0.3.0 does manage anymore timezone

Security
========

- Missing access control mechanism in rest API v1
- Predictable anti-CSRF token
- SQL injection in additional information in contact form
- SQL Injection on graph periods
- SQL Injection on graph split