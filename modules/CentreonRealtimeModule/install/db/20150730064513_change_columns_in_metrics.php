<?php

use Phinx\Migration\AbstractMigration;

class ChangeColumnsInMetrics extends AbstractMigration
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
        $this->execute("SET FOREIGN_KEY_CHECKS=0");
        
        /* Change metrics */
        $rtMetrics = $this->table('rt_metrics');
        $rtMetrics->changeColumn('warn_threshold_mode', 'boolean', array('null' => false, 'default' => 0))
            ->save();
        
        $this->execute("SET FOREIGN_KEY_CHECKS=1");
    }
}
