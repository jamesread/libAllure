<?php

require_once 'common.php';

use \libAllure\Database;
use \PHPUnit\Framework\TestCase;

/**
 * @group database
 */
class DatabaseTest extends TestCase {
    public function testConstruct() {
        $db = new Database('mysql:host=' . getenv('DB_HOST') . ';dbname=' . getenv('DB_NAME'), getenv('DB_USER'), getenv('DB_PASS'));

        $sql = 'SELECT version();';
        $res = $db->query($sql);

        var_dump($res);

        $this->assertNotNull($res);
    }
}

?>
