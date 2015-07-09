<?php

use Phinx\Migration\AbstractMigration;

class FreshInstall extends AbstractMigration
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
        // Creation of table cfg_sessions
        $cfg_modules = $this->table('cfg_modules', array('id' => false, 'primary_key' => array('id')));
        $cfg_modules->addColumn('id', 'integer', array('signed' => false, 'identity' => true, 'null' => false))
                ->addColumn('name', 'string', array('limit' => 255, 'null' => false))
                ->addColumn('alias', 'string', array('limit' => 255, 'null' => true))
                ->addColumn('description', 'string', array('limit' => 255, 'null' => true))
                ->addColumn('version', 'string', array('limit' => 45, 'null' => true))
                ->addColumn('author', 'string', array('limit' => 255, 'null' => true))
                ->addColumn('isactivated', 'string', array('limit' => 2, 'null' => true, 'default' => '0'))
                ->addColumn('isinstalled', 'string', array('limit' => 2, 'null' => true, 'default' => '0'))
                ->addIndex(array('id'), array('unique' => true))
                ->create();

        // Creation of table cfg_modules_dependencies
        $cfg_modules_dependencies = $this->table('cfg_modules_dependencies', array('id' => false, 'primary_key' => 'id'));
        $cfg_modules_dependencies->addColumn('id', 'integer', array('signed' => false, 'identity' => true, 'null' => false))
                ->addColumn('parent_id', 'integer', array('signed' => false, 'null' => false))
                ->addColumn('child_id', 'integer', array('signed' => false, 'null' => false))
                ->addColumn('optionnal', 'boolean', array('null' => false, 'default' => 0))
                ->addForeignKey('parent_id', 'cfg_modules', 'id', array('delete'=> 'CASCADE', 'update'=> 'CASCADE'))
                ->addForeignKey('child_id', 'cfg_modules', 'id', array('delete'=> 'CASCADE', 'update'=> 'CASCADE'))
                ->create();
    }
}
