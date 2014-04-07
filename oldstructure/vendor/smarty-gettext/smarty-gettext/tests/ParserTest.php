<?php

class TestParser extends PHPUnit_Framework_TestCase {
	// path to data dir
	private static $datadir;

	// path to the tool
	private static $tsmarty2c;

	public static function setUpBeforeClass() {
		self::$datadir = dirname(__FILE__) . '/data';
		self::$tsmarty2c = dirname(__FILE__) . '/../tsmarty2c.php';
	}

	/**
	 * @dataProvider testData
	 * @test
	 */
	public function testParse($input, $output) {
		$res = $this->tsmarty2c($input, $output);
		$res = $this->stripPaths($res);
		$this->assertEquals($output, $res);
	}

	private function tsmarty2c($input) {
		$cmd = array();
		$cmd[] = escapeshellcmd(self::$tsmarty2c);
		$cmd[] = escapeshellarg($input);

		exec(join(' ', $cmd), $lines, $rc);
		$this->assertEquals(0, $rc, "command ran okay");
		$this->assertNotEmpty($lines);

		$res = join("\n", $lines);
		$this->assertNotEmpty($res);

		return $res;
	}

	public function testData() {
		// $input, $output
		return array(
			$this->getFiles(1),
			$this->getFiles(2),
			$this->getFiles(3),
		);
	}

	private function stripPaths($content) {
		$content = str_replace(self::$datadir, '<DATADIR>', $content);

		return $content;
	}

	private function getFiles($number) {
		return array($this->getFileName($number, "html"), $this->stripPaths($this->getFile($number, "pot")));
	}

	private function getFileName($number, $ext) {
		$datadir = dirname(__FILE__) . '/data';
		$file = $datadir . "/$number.$ext";

		return $file;
	}

	private function getFile($number, $ext) {
		$file = $this->getFileName($number, $ext);
		$this->assertFileExists($file);
		$content = file_get_contents($file);
		$this->assertNotEmpty($content);
		$content = trim($content);
		$this->assertNotEmpty($content);

		return $content;
	}
}
