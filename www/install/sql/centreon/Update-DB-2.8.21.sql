-- Temporary fix for limit realtime and configuration Rest API
ALTER TABLE `contact` ADD COLUMN `reach_api_rt` int(1) DEFAULT 0 AFTER `reach_api`;
-- Update users with right to reach api
UPDATE contact SET reach_api_rt = "1" WHERE reach_api = "1";
