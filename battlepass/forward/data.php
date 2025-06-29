<?php

require_once __DIR__ . '/../ext/CoreController.php';


define('PLAYERS_ON_PAGE', 10);


$Controller = new CoreController($Db, $General, $Translate, $Modules);


$pageData = $Controller->getPageData($page_num, PLAYERS_ON_PAGE);

extract($pageData);