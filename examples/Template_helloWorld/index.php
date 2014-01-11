<?php

date_default_timezone_set('Europe/London');

require_once 'libAllure/Template.php';

use \libAllure\Template;

$tpl = new Template('testingTemplates');
$tpl->assign('yourName', 'Joe Bloggs');
$tpl->display('message.tpl');

?>
