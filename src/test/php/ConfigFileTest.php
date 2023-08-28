<?php

require_once 'common.php';

require_once 'libAllure/ConfigFile.php';

use \libAllure\ConfigFile;
use \PHPUnit\Framework\TestCase;

class ConfigFileTest extends TestCase {
    public function setUp(): void
    {
        if (!file_exists('/tmp/libAllure')) {
            mkdir('/tmp/libAllure/');
        }

        chmod('/tmp/libAllure', 0777);
    }
    public function testDefaultKeys() 
    {
        $cfg = new ConfigFile();

        $this->assertEquals('localhost', $cfg->get('DB_HOST'));
    }

    public function testAdditionalKeys()
    {
        $cfg = new ConfigFile([
            'blat' => 'hi',
        ]);

        $this->assertEquals('hi', $cfg->get('blat'));
        $this->assertEquals('localhost', $cfg->get('DB_HOST'));
    }

    public function testNoAdditionalKeys()
    {
        $cfg = new ConfigFile(null, false);

        $this->assertSame(null, $cfg->get('DB_HOST'));
    }

    public function testLoad()
    {
        $cfg = new ConfigFile();
        $cfg->tryLoad([
            '/tmp/libAllure/',
        ]);

        $this->assertSame('localhost', $cfg->get('DB_HOST'));
    }
}
