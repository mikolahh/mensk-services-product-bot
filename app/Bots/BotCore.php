<?php
namespace App\Bots;
use App\Bots\BotApi;
use App\Bots\UpdateException;

abstract class BotCore extends BotApi
{
  protected $my_bot;
  protected $data;   
  protected $action_data;  
  protected $builder_priv_sessions;

 
  public function __construct($token, $host_bot, $data = [], $builder_priv_sessions)
  {    
    $this->builder_priv_sessions = $builder_priv_sessions;           
    parent::__construct($token, $host_bot, $data);
    $this->data = $data;     
  }
  protected function preSelector()
  {    
    $this->action_data['user_id'] = $this->update_obj->userId() ?? '';
    $this->action_data['user_name'] = $this->update_obj->userName() ?? '';                
    return true;
  }
  protected function getSessionData()
  {    
    $session_isset = !empty($this->builder_priv_sessions->where(['user_id' => $this->action_data['user_id']])->countAllResults());    
    if ($session_isset) {
      $res = $this->builder_priv_sessions->select('screen_name, screen_messages_id')->where(['user_id' => $this->action_data['user_id']])->get()->getResultArray();      
      $screen_messages_id = json_decode($res[0]['screen_messages_id']); 
      $current_screen_name = $res[0]['screen_name'];      
    } else {
      $screen_messages_id = [];
      $current_screen_name = '';      
    }
    $this->action_data['session_isset'] = $session_isset;    
    $this->action_data['screen_messages_id'] = $screen_messages_id;    
    $this->action_data['current_screen_name'] = $current_screen_name;       
  }
  protected function setSessionData()
  {
    $user_id = $this->action_data['user_id'];
    $next_screen_messages_id = $this->action_data['next_screen_messages_id'];
    $next_screen_name = $this->action_data['next_screen_name'];
    $name = $this->action_data['user_name'];
    $session_isset = $this->action_data['session_isset'];

    if ($session_isset) {
      $res = $this->builder_priv_sessions->where(['user_id' => $user_id])->set(['screen_messages_id' => json_encode($next_screen_messages_id), 'screen_name' => $next_screen_name])->update();
    } else {
      $res = $this->builder_priv_sessions->insert(['user_id' => $user_id, 'user_name' => $name, 'screen_name' => $next_screen_name, 'screen_messages_id' => json_encode($next_screen_messages_id)]);
    }
    return $res;
  }
 
  protected function delSessionData()
  {
    $user_id = $this->action_data['user_id'];    
    $session_isset = $this->action_data['session_isset'];
    if ($session_isset) {
      $res = $this->builder_priv_sessions->where(['user_id' => $user_id])->delete();
    } else {
      $res = true;
    }
    return $res;
  }
  protected function delPrevScreen()
  { 
    $screen_messages_id = $this->action_data['screen_messages_id'];   
    $user_id = $this->action_data['user_id'];     
    foreach ($screen_messages_id as $key => $item) {
      $item_message_id = $item;      
      $res = $this->delAnyMessage($user_id, $item_message_id); 
      writeLogFile($res);      
    }
    $res = true;
    return $res;
  }
  protected function delCurrentUserMessage()
  { 
    $res = $this->delAnyMessage($this->update_obj->chatId(), $this->update_obj->messageId());            
    return $res;    
  }
  protected function getOutMessageIdHelper($res)
  {
    $out_message_id = $res['result']['message_id'];
    return $out_message_id;
  }
  protected function saveUserFileHelper()
  {
    $user_name = $this->action_data['user_name'];
    $user_id = $this->action_data['user_id'];
    $document_file_id = $this->update_obj->documentFileID();       
    $document_file_name = $this->update_obj->documentFileName();
    $res = $this->getFile($document_file_id);    
    $file_path = $res['result']['file_path'];            
    $file_link = self::API_FILE_URL . $this->token . '/' . $file_path;
    $dest = ROOTPATH . "/public/userFiles";          
      if (!file_exists($dest)) {
    mkdir($dest);
    }       
    $dest = $dest . "/$user_id - $user_name";
    if (!file_exists($dest)) {
    mkdir($dest);
    }
    $date = date("Y-m-d H-i-s");
    $dest = $dest . "/$date";
    if (!file_exists($dest)) {
    mkdir($dest);
    }    
    $dest = $dest . "/$document_file_name"; 
    $ch = curl_init($file_link); 
    $fp = fopen($dest, 'wb');    
    curl_setopt($ch, CURLOPT_FILE, $fp);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_exec($ch);
    curl_close($ch);
    fclose($fp);
    return $dest;      
  }
  public function actionErrorMessageUser()
  {     
    $user_id = $this->action_data['user_id'];
    $user_name = $this->action_data['user_name'];
    $text = "<b>{$user_name}</b>, что-то пошло не так и произошла непредвиденная ошибка, очистите историю сообщений и запустите бота заново, выполнив команду  \"Старт\" в меню";     
    $query_data = [
      'text' => $text,
      'chat_id' => $user_id,
      'parse_mode' => 'html',      
    ]; 
    $res = $this->sendMessage($query_data);   
  }
  
 
  
  
 
 
  
 
  
  
  

 
}