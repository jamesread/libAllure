<?php

date_default_timezone_set('Europe/London');

set_include_path(get_include_path() . PATH_SEPARATOR . '../../src/main/php/' . PATH_SEPARATOR . '../../vendor/');

require_once 'autoload.php';

use \libAllure\Template;

$tpl = new Template('testingTemplates');
$tpl->assign('yourName', 'Joe Bloggs');
$tpl->display('message.tpl');

?>
