<?php

use Phinx\Migration\AbstractMigration;

class MassiveChange extends AbstractMigration
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
        // Updating of table cfg_forms_massive_change        
        $this->execute("ALTER TABLE cfg_forms_massive_change MODIFY COLUMN massive_change_id integer UNSIGNED NOT NULL AUTO_INCREMENT");
        $this->execute("ALTER TABLE cfg_forms_massive_change MODIFY COLUMN name VARCHAR(100) NOT NULL");
        $this->execute("ALTER TABLE cfg_forms_massive_change MODIFY COLUMN route VARCHAR(100) NOT NULL");
    }
}
