<?php

$config = new \Qi\Config();

$config->write("path.base", dirname(dirname(__FILE__)));
$config->write("path.controllers", dirname(dirname(__FILE__)));
$config->write("database.dsn", "sss");
$config->write("database.username", "root");
$config->write("database.password", "");