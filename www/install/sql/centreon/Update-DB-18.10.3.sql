-- Remove old progressbar lib
DELETE FROM topology_JS WHERE PathName_js LIKE '%aculous%';

-- Changing contact_lang default value to 'browser' and each of its value from NULL to 'browser'
ALTER TABLE contact MODIFY `contact_lang` varchar(255) DEFAULT 'browser';
UPDATE contact SET `contact_lang` = 'browser' WHERE `contact_lang` IS NULL;
