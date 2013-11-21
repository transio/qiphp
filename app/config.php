<?php

$config = new \Qi\Config();

$config->write("base_path", dirname(dirname(__FILE__)));

$config->write("database.dsn", "sss");
$config->write("database.username", "root");
$config->write("database.password", "");