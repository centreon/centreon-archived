<?php

use Phinx\Migration\AbstractMigration;

class BamPollerRelation extends AbstractMigration
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
        // Creation of table cfg_bam_poller_relations
        $cfg_bam_poller_relations = $this->table('cfg_bam_poller_relations', array('id' => false, 'primary_key' => array('ba_id', 'poller_id')));
        $cfg_bam_poller_relations->addColumn('ba_id', 'integer', array('signed' => false, 'null' => false))
            ->addColumn('poller_id', 'integer', array('signed' => false, 'null' => false))
            ->addForeignKey('ba_id', 'cfg_bam', 'ba_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
            ->addForeignKey('poller_id', 'cfg_pollers', 'poller_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
            ->create();
    }
}
