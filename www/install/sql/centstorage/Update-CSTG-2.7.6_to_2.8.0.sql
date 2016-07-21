-- OPTIMIZE Monitoring
ALTER TABLE services ADD INDEX last_hard_state_change (last_hard_state_change);
