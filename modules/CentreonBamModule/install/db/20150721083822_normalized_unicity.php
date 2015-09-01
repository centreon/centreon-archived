<?php
/*
 * Copyright 2015 Centreon (http://www.centreon.com/)
 * 
 * Centreon is a full-fledged industry-strength solution that meets 
 * the needs in IT infrastructure and application monitoring for 
 * service performance.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *    http://www.apache.org/licenses/LICENSE-2.0  
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * For more information : contact@centreon.com
 */

use Phinx\Migration\AbstractMigration;

class NormalizedUnicity extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     */
    public function change()
    {
        // Removing Unnecessary Unique Index
        $this->execute('ALTER TABLE cfg_tags_bas DROP INDEX tag_id');
        $this->execute('ALTER TABLE cfg_bam_bagroup_ba_relation DROP INDEX id_ba_2');
        $this->execute('ALTER TABLE cfg_bam_relations_ba_timeperiods DROP INDEX ba_id_2');
        $this->execute('ALTER TABLE cfg_meta_services_relations DROP INDEX meta_id');
        $this->execute('ALTER TABLE mod_bam_reporting_relations_ba_bv DROP INDEX bv_id_2');
        $this->execute('ALTER TABLE mod_bam_reporting_relations_ba_kpi_events DROP INDEX ba_event_id_2');
        $this->execute('ALTER TABLE mod_bam_reporting_timeperiods_exclusions DROP INDEX timeperiod_id_2');
    }
}