<?php
namespace App\Controllers;

class TestController extends \Qi\Controller\Controller
{
public function __construct($a, $b) {
parent::__construct($a, $b);
}
public function hi()
{
$this->x = "test";
$this->render();
}
}
