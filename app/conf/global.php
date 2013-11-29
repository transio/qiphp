<?php

$config = new \Qi\Config();

$config->write("app.debug", true);

$config->write("default.controller", "TestController");
$config->write("default.action", "index");

$config->write("path.base", BASE_PATH);
$config->write("path.log", BASE_PATH . "log" . DS);

$config->write("database.dsn", "sss");
$config->write("database.username", "root");
$config->write("database.password", "");

