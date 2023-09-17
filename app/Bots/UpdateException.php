<?php
namespace App\Bots;

class UpdateException extends \Exception
{
  public function __construct($text)
  {   
    $msg = "Ошибка типа \"UpdateError\""  . PHP_EOL . $text;
    parent::__construct($msg);     
  }
}