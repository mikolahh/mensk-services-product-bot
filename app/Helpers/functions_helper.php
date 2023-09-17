<?php

// Функция для раздельного вывода ошибок валидации для кажлого поля
function display_error($validation, $field)
{
    if ($validation->hasError($field)) {
        return $validation->getError($field);
    } else {
        return false;
    }
}
// Красивый вывод массивов и объектов
function outArray($arr)
{
    echo '<pre>' . print_r($arr, true) . '</pre>';
    echo '<br>';
}
// Перехватываем информацию var_dump
function getVarDump ($val) {
  ob_start();
  var_dump($val);
  $output = ob_get_clean();
  return $output;
}
// Получаем рандомное имя
function randomName()
{
    return md5(microtime() . rand(0, 9999));
}
// Запмсываем данные в лог-файл
function writeLogFile ($string, $clear = false)
{    
  $log_file_name = 'bot_logs.txt';    
  $now = date("Y-m-d H:i:s");        
  if ($clear == false) {
    $res =  file_put_contents($log_file_name, "\n" . $now . "\n" . print_r($string, true), FILE_APPEND);
  } else {
      file_put_contents($log_file_name, '');
      file_put_contents($log_file_name, $now . "\n" . print_r($string, true), FILE_APPEND);
  }    
}
