<?php
class DefaultDatabaseConnectionTest extends PHPUnit_Framework_TestCase
{
    protected $db;

    protected function setUp()
    {
        $this->db = new PDO('sqlite::memory:');
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->db->exec('CREATE TABLE test (field1 VARCHAR(100))');
    }

    public function testRowCountForEmptyTableReturnsZero()
    {
        $conn = new PHPUnit_Extensions_Database_DB_DefaultDatabaseConnection($this->db);
        $this->assertEquals(0, $conn->getRowCount('test'));
    }

    public function testRowCountForTableWithTwoRowsReturnsTwo()
    {
        $this->db->exec('INSERT INTO test (field1) VALUES (\'foobar\')');
        $this->db->exec('INSERT INTO test (field1) VALUES (\'foobarbaz\')');

        $conn = new PHPUnit_Extensions_Database_DB_DefaultDatabaseConnection($this->db);
        $this->assertEquals(2, $conn->getRowCount('test'));
    }
}
