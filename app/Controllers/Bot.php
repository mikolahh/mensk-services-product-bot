<?php

namespace App\Controllers;
use App\Controllers\BaseController;
use App\Bots\MyBot;
class Bot extends BaseController
{
  private $token;
  private $host_bot; 
  private $builder_users;

  public function __construct()
  {
    $this->token = botIni()['token_bot'];
    $this->host_bot = botIni()['host_bot'];    
    $db = \Config\Database::connect();    
    $this->builder_users = $db->table('users');  
  }   
  public function index()
  {
      $token = $this->token;
      $host_bot = $this->host_bot;  
      // $my_bot = new MyBot($token, $host_bot);   
      // $file_id = 'BQACAgIAAxkDAAI3FGTP4RcXLZVfYk9T5GB5-WSWNsZTAAIkNQACDnKBSvQKFg1MlC5aMAQ';        
      /*  $query_data = [
        'chat_id' => 902636138,
        'caption' => 'Hello Mikola',
        'document' => ROOTPATH . 'sftp.txt'
        // 'document' => $file_id,
      ]; */
      // $res = $my_bot->sendDocument($query_data, 'document');
      
      // $res = $my_bot->getMe();
      // outArray($res);
      // $res = $my_bot->getMyName();
      // $chat_id = 902636138; //Mikola
      //$res = $my_bot->getUserLink($chat_id);
      // outArray($res); 
      // die;   
      
      // $chat_id = 6374826978;//MenskPratsaBot
        
        //  $query_data = ['chat_id' => 6374826978,];     
      // $res = $my_bot->getChat($query_data);
      // $res = $my_bot->exportChatInviteLink($chat_id);

      // $res = $my_bot->getChatMember($query_data); 
      // $res = $my_bot->setMyDescription($query_data);
      // $res = $my_bot->getMyShortDescription();
      // $res = $my_bot->getUserLink(1551080903);   
          
          // $res = $my_bot->setWebHook();
          // outArray($res);
            // $res = $my_bot->getWebhookInfo();                       
            // $res = $my_bot->deleteWebHook();
           //$bot_commands = [['command' => '/start', 'description' => 'Старт'], ['command' => '/exit', 'description' => 'Выход']];        
            //$res = $my_bot->setMyCommands($bot_commands);
           //$res = $my_bot->getMyCommands();
          //  $res = $my_bot->deleteMyCommands();
        // $res =  $my_bot->getUpdates();

          // outArray($res); 
          // die;   
          // outArray(json_decode($res, true));            
          //  $data = tgTestData()['Mikalai-start'];
          //  $data = tgTestData()['NIKOBAR-start'];          
          
          // $data = tgTestData()['tanya-start'];
          
          // $data = tgTestData()['Mikalai-only-photo'];
          // $data = tgTestData()['Mikalai-pay-conf-voice'];
          // $data = tgTestData()['NIKOBAR-webapp'];          
          //  $my_bot = new MyBot($token, $host_bot, $data);
          // $my_bot->inputData();
          if ($this->request->is('post')) {
            $data = json_decode(file_get_contents('php://input'), true);                       
            if (isset($data)) {                                     
              $my_bot = new MyBot($token, $host_bot, $data);                                                                                       
                $my_bot->inputData();                                               
            }           
        }
  }
  public function delBotMess()
    {
      $token = botIni()['token_bot'];
      $host_bot = botIni()['host_bot'];                      
      $my_bot = new MyBot($token, $host_bot);
      $res = $my_bot->delBotMessAction();    
    }
    public function delUsers()
    {
      $token = botIni()['token_bot'];
      $host_bot = botIni()['host_bot'];                      
      $my_bot = new MyBot($token, $host_bot);
      $res = $my_bot->delUsersAction();    
    }
    public function delSessions()
    {     
      $token = botIni()['token_bot'];
      $host_bot = botIni()['host_bot'];                      
      $my_bot = new MyBot($token, $host_bot);      
      $my_bot->delSessionsAction();         
    }
    public function getUsers()
    {    
      $my_bot = new MyBot($this->token, $this->host_bot);
      $group_admin_id = $my_bot->group_admin_id;   
      try {
        $users_data = $this->builder_users->where(['date_pay !=' => '', 'user_id !=' => $group_admin_id])->orWhere(['temp_access' => 1, 'user_id !=' => $group_admin_id])->get()->getResultArray();
      } catch (\Throwable $e) {
        $message = $e->getMessage();    
        $file = $e->getFile();
        $line = $e->getLine();
        $trace = $e->getTraceAsString();
        $bot_name = $my_bot->getMyName()['result']['name'];         
        $text = "<b>$bot_name</b>" . PHP_EOL . $message . PHP_EOL . $file . PHP_EOL . $line . PHP_EOL . $trace;   
        $query_data = [
          'chat_id' => $my_bot->develop_id,
          'text' => $text,
          'parse_mode' => 'html',      
        ];       
        $my_bot->sendMessage($query_data);     
      }
      $users_count = count($users_data);
      if ($users_count != 0) {
        foreach ($users_data as &$item) {
          $user_id = $item['user_id'];
          $user_link = $my_bot->getUserLink($user_id);
          $item['user_link'] = $user_link;
        }
        unset($item);      
        $users_info = "Пользователей, имеющих доступ - $users_count";      
      } else {
        $users_data = [];
        $users_info = "Пользователи, имеющие доступ, отсутствуют";
      }
      $data['users_data'] = $users_data;   
      $data['users_info'] = $users_info; 
      return view('bot/users', $data);  
    }
    public function getWebAppData()
    {
      if ($this->request->is('post')) {
        $data = json_decode(file_get_contents('php://input'), true); 
        writeLogFile($data, true);      
      } 
      die;
    
      $user_id = $this->request->getPost('user_id');
      $user_name = $this->request->getPost('user_name');
      $query_id = $this->request->getPost('query_id');
      $resp =  $user_id . PHP_EOL . $user_name . PHP_EOL . $query_id;
      writeLogFile($resp);

      $my_bot = new MyBot($this->token, $this->host_bot);
      $query_data = [
        'web_app_query_id' => $query_id,
        'result' => json_encode([
          'type' => 'article',
          'id' => $query_id,
          'title' => 'Выбор пользователя',
          'input_message_content' => [
            'message_text' => 'Пользователь успешно выбран',
          ],
        ]),
      ];
      $res = $my_bot->answerWebAppQuery($query_data);
      writeLogFile($res);


      
      
        
      /* $version = $this->request->getPost('version');
      $platform = $this->request->getPost('platform');
      $initData = $this->request->getPost('initData');    
      $initDataUnsafe = $this->request->getPost('initDataUnsafe');
      $initDataUnsafe = getVarDump($initDataUnsafe);
      $colorScheme = $this->request->getPost('colorScheme');
      $themeParams = $this->request->getPost('themeParams');
      $themeParams = getVarDump($themeParams);
      $isExpanded = $this->request->getPost('isExpanded');
      $viewportHeight = $this->request->getPost('viewportHeight'); */
      
    
    /*  writeLogFile("version: $version", true);
      writeLogFile("platform: $platform");
      writeLogFile("initData: $initData");
      writeLogFile("initDataUnsafe: $initDataUnsafe");
      writeLogFile("colorScheme: $colorScheme");
      writeLogFile("themeParams: $themeParams");
      writeLogFile("isExpanded: $isExpanded");
      writeLogFile("viewportHeight: $viewportHeight"); */
    }
    public function test()
    {
      $token = botIni()['token_bot'];
      $host_bot = botIni()['host_bot'];                      
      $my_bot = new MyBot($token, $host_bot);
     
      
    }
   
    
}
