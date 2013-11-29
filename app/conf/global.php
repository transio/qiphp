<?php

$config = new \Qi\Config();

$config->write("app.debug", true);

$config->write("path.base", BASE_PATH);
$config->write("path.log", BASE_PATH . DS . "log");
$config->write("path.app", BASE_PATH . DS . "app");

$config->write("database.dsn", "sss");
$config->write("database.username", "root");
$config->write("database.password", "");

