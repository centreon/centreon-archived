-- add default ldap connection timeout value
INSERT INTO auth_ressource_info (ar_id, ari_name, ari_value) (SELECT ar_id, 'ldap_connect_timeout', '5' FROM auth_ressource);