<?php

use Phinx\Migration\AbstractMigration;

class ForeingnKeyMetrics extends AbstractMigration
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
        
        /* Change log_data_bin */
        $log_data_bin = $this->table('log_data_bin');
        $log_data_bin->changeColumn('ctime', 'integer', array('signed' => false, 'null' => false))
            ->addForeignKey('metric_id', 'rt_metrics', 'metric_id', array('delete'=> 'CASCADE'))
            ->save();
        
        /* Change rt_index_data */
        $rt_index_data = $this->table('rt_index_data');
        $rt_index_data->changeColumn('host_id','integer', array('signed' => false, 'null' => false))
            ->save();
    }
}
