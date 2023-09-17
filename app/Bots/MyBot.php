<?php 
namespace App\Bots;
use App\Bots\BotCore;
use DateTimeZone;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Utils;

class MyBot extends BotCore
{   
  private $builder_users;  
  private $builder_bot_group_adts;
  private $builder_users_for_admin;  
  private $location = 'Europe/Minsk';
  private $date_format = 'Y-m-d H:i:s';
  private $date_time_zone_obj;

  //private $group_id = -1001487936960;//rabotaesttyt
  // private $group_id = -924698584;// NikobarGroup
   private $group_id = -1001473929594;// servis_produkt
   
  public $group_admin_id = 439967664;
  private $group_admin_name = 'Татьяна';
  private $group_admin_link = '@Solnce999';

  // public $group_admin_id = 902636138;

   //public $group_admin_id = 5343585393;
   //private $group_admin_name = 'SunshineDesire';
   //private $group_admin_link = '@SunshineDesire777';

  //public $group_admin_id = 1551080903;
  //private $group_admin_name = 'NIKOBAR';
  //private $group_admin_link = '@Nikobar71';

  private $web_app_url = 'https://product-bot.mikalay.tech/bot/users';

  public function __construct($token, $host_bot, $data = [])
  {     
    $db =  \Config\Database::connect();           
    $builder_priv_sessions =  $db->table('private_sessions');    
    $this->builder_users = $db->table('users');
    $this->builder_bot_group_adts = $db->table('bot_group_adts');
    $this->builder_users_for_admin = $db->table('users_for_admin');
    $this->date_time_zone_obj = new DateTimeZone($this->location); 
    parent::__construct($token, $host_bot, $data,  $builder_priv_sessions);       
  }
  public function inputData()
  {     
    $this->preSelector(); 
    $this->getSessionData();           
    $this->getAdtsData();
    $this->actionSelect();    
  }
  private function actionSelect()
  {          
    try {
      switch (true) {
        case $this->update_obj->messageIsset():
          if ($this->update_obj->updateType() == 'private') {
            switch (true) {
              case $this->update_obj->text() == '/start':               
                $this->actionStart();                
                try {                
                  $this->delCurrentUserMessage();               
                } catch (\Throwable $e) {                                 
                  $this->actionErrorLogDev($e);                  
                }                                           
                break;
              case $this->update_obj->text() == '/exit':            
                $this->delSessionData();
                $this->delPrevScreen();
                try {
                  $this->delCurrentUserMessage();
                } catch (\Throwable $e) {                  
                  $this->actionErrorLogDev($e);
                }
                die;
                break;
              case $this->action_data['current_screen_name'] == 'pay-conf-start':
                $this->actionPayConfRes();
                try {
                  $this->delCurrentUserMessage();
                } catch (\Throwable $e) {                  
                  $this->actionErrorLogDev($e);
                }
                break;
               case ($this->action_data['current_screen_name'] == 'admin-start') && ($this->update_obj->viaBotIsset()):                                
                  $screen_messages_id = [];
                  $message_id = $this->update_obj->messageId();                  
                  array_push($screen_messages_id, $message_id);
                  $this->action_data['next_screen_messages_id'] = $screen_messages_id;
                  $this->action_data['next_screen_name'] = 'web-app-finish';                  
                break; 
              default: 
                $this->handleUserAdt();                         
                $this->actionStart();
                try {
                  $this->delCurrentUserMessage();
                } catch (\Throwable $e) {                  
                  $this->actionErrorLogDev($e);
                }
                break;
            }
          } elseif ($this->update_obj->groupIsset()) {
            if(!$this->action_data['access_isset'] && ($this->action_data['user_id'] != $this->group_admin_id)) {              
              try {
                $this->delCurrentUserMessage();
              } catch (ClientException $e) {                
                $chat_id = $this->update_obj->chatId();
                $message_id = $this->update_obj->messageId();
                $from_user_id = $this->update_obj->userId();
                $from_user_name = $this->update_obj->userName();                
                $comment_text = "Ошибка удаления сообщения(message_id: $message_id) пользователя((user_id: $from_user_id, user_name: $from_user_name)) без доступа из группы";              
                $this->actionErrorLogDev($e, $comment_text);              
              } 
              $this->sendBotMessageInGroupAndSave();              
              die;
            } else {
              die;
            }
          } else {
            writeLogFile($this->data);
            throw new UpdateException('Неизвестный тип сообщения: не private, не group or supergroup');
          }
          break;         
        case $this->update_obj->callbackIsset():
          switch (true) {
            case $this->update_obj->callbackData() == 'update':                 
              $this->actionStart();
              break;
            case $this->update_obj->callbackData() == 'pay-confirm':              
              $this->actionPayConfStart();                
              break;
            case ($this->action_data['current_screen_name'] == '/start'):
              switch (true) {            
                case $this->update_obj->callbackData() == 'instruction':
                  $this->actionInstruction();
                  break;
                case $this->update_obj->callbackData() == 'admin-start':
                  $this->actionAdminStart();
                  break;
              }
              break;
            case ($this->action_data['current_screen_name'] == '/instruction'):         
              if ($this->update_obj->callbackData() == 'return') {                      
                $this->actionStart();
              }
              break;
            case ($this->action_data['current_screen_name'] == 'pay-conf-start'):
              if ($this->update_obj->callbackData() == 'return') {
                $this->actionStart();
              }
              break;
            case ($this->action_data['current_screen_name'] == 'admin-pay-conf-res'):
              switch (true) {           
                case $this->update_obj->callbackData() == 'admin-fast-pay-denied':
                  $this->actionFastPayDenRes();
                  break;
                case $this->update_obj->callbackData() == 'user-for-pay-conf-start':              
                  $this->actionUserForPayConfStart();
                break;             
              }          
              break;
            case ($this->action_data['current_screen_name'] == 'admin-fast-pay-den-res'):
              if ($this->update_obj->callbackData() == 'return') {
                $this->actionStart();                
              }
              break;
            case ($this->action_data['current_screen_name'] == 'admin-start'):
              switch (true) {
                case $this->update_obj->callbackData() == 'return':
                  $this->actionStart();
                  break;
                case $this->update_obj->callbackData() == 'users-for-pay-conf-start':
                  $this->actionUsersForPayConfStart();
                  break;                           
              }
              break;
            case ($this->action_data['current_screen_name'] == 'web-app-finish'):
              switch (true) {          
                case $this->update_obj->callbackData() == 'continue':              
                  $this->actionShowUser();
                  break;
                case $this->update_obj->callbackData() == 'return':
                  $this->actionAdminStart();
                  break;
              }
              break;
            case ($this->action_data['current_screen_name'] == 'show-user'):
              switch (true) {
                case $this->update_obj->callbackData() == 'del-user':
                  $this->actionDelUser();             
                  $this->actionAdminStart();
                  break;
                case $this->update_obj->callbackData() == 'return':
                  $this->actionAdminStart();
                  break;            
              }
              break;            
            case ($this->action_data['current_screen_name'] == 'users-for-pay-conf-start'):
              switch (true) {
                case $this->update_obj->callbackData() == 'return':
                  $this->actionAdminStart();
                  break;
                case $this->update_obj->callbackData() == 'user-for-pay-conf-start':             
                  $this->actionUserForPayConfStart();
                  break;          
              }
              break;
            case ($this->action_data['current_screen_name'] == 'user-for-pay-conf-start'):          
              switch (true) {
                case $this->update_obj->callbackData() == 'return':
                  $this->actionUsersForPayConfStart();
                  break;
                case $this->update_obj->callbackData() == 'pay-conf':
                  $this->actionUserForPayConfRes();
                  break;
                case $this->update_obj->callbackData() == 'pay-den':
                  $this->actionUserForPayDenRes();
                  break;
                default:  
                  $this->actionStart();            
                  break;
              }         
              break;
            case ($this->action_data['current_screen_name'] == 'user-for-pay-conf-res'):
              switch (true) {
                case $this->update_obj->callbackData() == 'repeat':
                  $this->actionUserForPayConfStart();
                  break;
                case $this->update_obj->callbackData() == 'fix-pay-conf-res':             
                  $this->actionUserForPayConfFin();
                  break;          
              }
              break;
            case ($this->action_data['current_screen_name'] == 'user-for-pay-conf-fin'):
              switch (true) {
                case $this->update_obj->callbackData() == 'admin-start':
                  $this->actionAdminStart();
                  break;
                case $this->update_obj->callbackData() == 'start':
                  $this->actionStart();
                  break;           
              }
              break;
            case ($this->action_data['current_screen_name'] == 'user-for-pay-den-res'):
              switch (true) {
                case $this->update_obj->callbackData() == 'return':
                  $this->actionUsersForPayConfStart();
                  break;
                case $this->update_obj->callbackData() == 'del-user-for-admin':
                  $this->actionUserForPayDenFin();
                  break;                
                default:
                  $this->actionErrorMessageUser();
                  break;
              }
              break;
            case ($this->action_data['current_screen_name'] == 'user-for-pay-den-fin'):
              switch (true) {
                case $this->update_obj->callbackData() == 'admin-start':
                  $this->actionAdminStart();
                  break;
                case $this->update_obj->callbackData() == 'start':
                  $this->actionStart();
                  break;
                default:
                 $this->actionErrorMessageUser();
                 break;           
              }
              break;
            default:
            $this->actionErrorMessageUser();           
            if ($this->action_data['session_isset']) {
              $current_screen_name = $this->action_data['current_screen_name'];                 
              $message = "Current screen name - $current_screen_name" . PHP_EOL . 'Какой-то неизвестный каллбек, я где-то ошибся ??';                 
            } else {
              $message = 'Прилетел каллбек при отсутствии сессии';
            }
            throw new UpdateException($message);
              break;
          }
          break;
        case $this->update_obj->webAppQueryIsset():
          if ($this->action_data['current_screen_name'] == 'admin-start') {
            $this->actionShowAllUsersResult();
            die;                                   
          } else {
            die;
          }
          break;
        default:          
          if ($this->update_obj->otherUpdateTypesIsset()) {
            die;
          } else {
            writeLogFile($this->data);
            throw new UpdateException('Неизвестный тип апдейта: не входит в other_update_types');
          }          
          break;
      }
    } catch (UpdateException $e) {
      $this->actionErrorMessageDev($e);
      die;
    }
    if ($this->action_data['session_isset']) {       
      try {        
        $this->delPrevScreen();        
      } catch (ClientException $e) {         
        $this->actionErrorLogDev($e);                
      }     
    }
    $this->setSessionData();   
  }
  private function getAdtsData()
  {    
    $date_current_obj = new \DateTime('now', $this->date_time_zone_obj);      
    $user_id = $this->action_data['user_id'];
    $user_name = $this->action_data['user_name'];
    $user_isset = (bool)($this->builder_users->where(['user_id' => $user_id])->countAllResults());         
    if ($user_isset) {
    $user_data_in = $this->builder_users->select('date_pay, temp_access')->where(['user_id' => $user_id])->get()->getResultArray();           
    $date_pay = $user_data_in[0]['date_pay'];   
    $temp_access = (bool)$user_data_in[0]['temp_access'];
    $user_start = false;
    } else {            
      $this->builder_users->insert(['user_id' => $user_id, 'user_name' => $user_name,]);     
      $user_start = true;
      $date_pay = '';
      $temp_access = false;
    }        
    $date_pay_obj = new \DateTime($date_pay, $this->date_time_zone_obj);
    $interval = $date_pay_obj->diff($date_current_obj); 
    if (!empty($date_pay)) {
      $pay_isset = (bool) $interval->invert;
    } else {
      $pay_isset = false;
    }     
    $access_isset = $pay_isset || $temp_access;   
    $this->action_data['access_isset'] = $access_isset;
    $this->action_data['user_start'] = $user_start ?? false;    
    $this->action_data['date_pay'] = $date_pay ?? '';
    $this->action_data['pay_isset'] = $pay_isset ?? false;
    $this->action_data['temp_access'] = $temp_access ?? false;         
  }
  private function actionStart()
  {       
    $screen_messages_id = $this->action_data['screen_messages_id'];    
    $access_isset = $this->action_data['access_isset'];
    $temp_access = $this->action_data['temp_access'];    
    $user_id = $this->action_data['user_id'];
    $date_pay = $this->action_data['date_pay'];
    // $user_start = $this->action_data['user_start'];

    $screen_messages_id = [];
    if ($user_id == $this->group_admin_id) {
      $keyboard = [[$this->callbackButton('Админпанель', 'admin-start')], [$this->callbackButton('Инструкция по пользованию и оплате', 'instruction')]];
      $query_data = [
        'chat_id' => $user_id,
        'text' => "<b>Вход в админку</b>",
        'parse_mode' => 'html',
        'reply_markup' => json_encode(['inline_keyboard' => $keyboard]),
      ];
      try {
        $res = $this->sendMessage($query_data);
      } catch (ClientException $e) {
        $this->actionErrorMessageDev($e);
        $this->actionErrorMessageUser();
      }                                       
      $out_message_id = $this->getOutMessageIdHelper($res);              
      array_push($screen_messages_id, $out_message_id);
    } else {
      if ($access_isset) {
        switch (true) {
          case $temp_access:
            $text_access = "<b>Идет проверка платежа. Объявления показываются по временному доступу до окончания проверки</b>";
            break;               
          default:
            $text_access = "<b>Объявления показываются до $date_pay</b>";
            break;
        }
      } else {
        $text_access_sec = "<b>В данный момент показ объявлений не оплачeн.</b>" . PHP_EOL . "<b>Объявления не публикуются.</b>" . PHP_EOL . PHP_EOL .  "Пожалуйста, внесите оплату или получите временный доступ." . PHP_EOL . "Подробнее в <b>\"Инструкции по пользованию и оплате\".</b>";
        $text_access_first = "<b>Теперь доступ к размещению объявлений в группе минимально платный.</b>" . PHP_EOL . "Плата берется с целью поддержания порядка в чате и  защиты от мошенничества." . PHP_EOL . "<i>Если вы оплатили доступ и при этом шлете объявления сомнительного характера (поиск курьеров для торговли наркотиками или откровенный скам (связки, арбитраж), то ваш аккаунт блокируется без возврата денежных средств.</i>" . PHP_EOL . "Стоимость доступа:" . PHP_EOL . "<b>1 месяц: 3 рубля.</b>" . PHP_EOL . "Плата символическая, но позволит избежать многих проблем и как-то поддерживать проект.";               
      
        $text_access = $text_access_first . PHP_EOL . PHP_EOL . $text_access_sec; 
      }
      $keyboard = [[$this->callbackButton('Подтвердить оплату', 'pay-confirm'),], [$this->callbackButton('Инструкция по пользованию и оплате', 'instruction')], [$this->callbackButton('Обновить', 'update')]];           
        $query_data = [
        'chat_id' => $user_id,      
        'text' => $text_access,
        'parse_mode' => 'html',
        'reply_markup' => json_encode(['inline_keyboard' => $keyboard]),      
        ];         
        try {
          $res = $this->sendMessage($query_data);
        } catch (ClientException $e) {
          $this->actionErrorMessageDev($e);
          $this->actionErrorMessageUser();
        }                                 
        $out_message_id = $this->getOutMessageIdHelper($res);              
        array_push($screen_messages_id, $out_message_id);
    } 
    $this->action_data['next_screen_messages_id'] = $screen_messages_id;
    $this->action_data['next_screen_name'] = '/start';      
  } 
  private function actionInstruction()
  {   
    $user_id = $this->action_data['user_id'];
    $user_name = $this->action_data['user_name'];
    $group_admin_link = $this->group_admin_link;
    $text = "Уважаемый(ая) $user_name, размещение объявлений в группе теперь платное." . PHP_EOL . "Плата минимально возможная и берется с целью поддержания порядка в чате и  защиты от мошенничества." . PHP_EOL . "Это единственная возможность и в дальнейшем поддерживать проект." . PHP_EOL . "Добавляйте посты прямо в этот чат или в группу, если есть доступ. Объявления пересылаются в группу автоматически при наличии доступа. Стоимость доступа: " . PHP_EOL . "<b>1 месяц - 3 рубля.</b>" . PHP_EOL . "✅ Совершайте оплату через систему ЕРИП:" . PHP_EOL . "2. 📁 Банковские финансовые услуги" . PHP_EOL . "3. 📁 Банки, НКФО" . PHP_EOL . "4. 📁 Банк БелВЭБ" . PHP_EOL . "5. 📁 Пополнение счета" . PHP_EOL . "7. 🔘 375256097504" . PHP_EOL . " ℹ️ Проверочная информация:" . PHP_EOL . "(Tатьяна Анатольевна)" . PHP_EOL . "Далее скопируйте или скачайте чек, в личном кабинете нажмите кнопку <b>\"Подтвердить оплату\"</b> и отправьте в виде текста или файла." . PHP_EOL . "Вам сразу будет дан временный доступ до окончания проверки." . PHP_EOL . "При возникновении затруднений просто отправьте произвольный текст, и с вами свяжется администратор группы: $group_admin_link" . PHP_EOL . "<i>Если вы оплатили доступ и при этом шлете объявления сомнительного характера (поиск курьеров для торговли наркотиками или откровенный спам (связки, арбитраж), то ваш аккаунт блокируется без возврата денежных средств.</i>";
    $keyboard = [[$this->button_return, $this->callbackButton('Подтвердить оплату', 'pay-confirm')],];
    $query_data = [
    'chat_id' => $user_id,      
    'text' => $text,
    'parse_mode' => 'html',
    'reply_markup' => json_encode(['inline_keyboard' => $keyboard]),      
    ];
    try {
      $res = $this->sendMessage($query_data);
    } catch (ClientException $e) {
      $this->actionErrorMessageDev($e);
      $this->actionErrorMessageUser();
    }     
    $screen_messages_id = [];                                 
    $out_message_id = $this->getOutMessageIdHelper($res);              
    array_push($screen_messages_id, $out_message_id);  
    $this->action_data['next_screen_messages_id'] = $screen_messages_id;
    $this->action_data['next_screen_name'] = '/instruction';   
  }
  private function actionPayConfStart()
  {
    $user_id = $this->action_data['user_id'];
    $group_admin_link = $this->group_admin_link;
    $text = "<b>Отправьте квитанцию ЕРИП об оплате в виде текста, pdf-файла или в любой другой удобной форме</b>" . PHP_EOL . "Доступ к показу объявлений будет дан сразу, а затем подтвержден после проверки платежа"  . PHP_EOL . "При возникновении затруднений просто отправьте произвольный текст, и с вами свяжется администратор группы: $group_admin_link";
    $keyboard = [[$this->button_return]];
    $query_data = [
      'chat_id' => $user_id,
      'text' => $text,
      'reply_markup' => json_encode(['inline_keyboard' => $keyboard]),
      'parse_mode' => 'html',
    ];    
    try {
      $res = $this->sendMessage($query_data);
    } catch (ClientException $e) {
      $this->actionErrorMessageDev($e);
      $this->actionErrorMessageUser();
    }        
    $out_message_id = $this->getOutMessageIdHelper($res);
    $screen_messages_id = [];
    array_push($screen_messages_id, $out_message_id);
    $this->action_data['next_screen_messages_id'] = $screen_messages_id;
    $this->action_data['next_screen_name'] = 'pay-conf-start';
  }
  private function actionPayConfRes()
  {             
    $user_id = $this->action_data['user_id'];    
    $user_name = $this->action_data['user_name'];
    $user_link = $this->update_obj->userLink() ?? '';    
    try {
      $tg_method = $this->update_obj->handleMessageData()[0];           
      $query_basic = $this->update_obj->handleMessageData()[1];      
    } catch (UpdateException $e) {
      $text = 'Ошибка метода actionPayConfRes' . PHP_EOL . $e;
      $this->actionErrorMessageDev($text);
      $this->actionErrorMessageUser();
    }   
    $pay_message = [];    
    array_push($pay_message, $tg_method, $query_basic);    
    $user_for_admin_isset = (bool)$this->builder_users_for_admin->where(['user_id' => $user_id])->countAllResults();   
    if ($user_for_admin_isset) {
      $this->builder_users_for_admin->where(['user_id' => $user_id])->set(['pay_message' => json_encode($pay_message)])->update();
    } else {
      $this->builder_users_for_admin->insert(['user_id' => $user_id, 'user_name' => $user_name, 'user_link' => $user_link, 'pay_message' => json_encode($pay_message)]);
    }  
    $this->builder_users->where(['user_id' => $user_id])->set(['temp_access' => 1])->update();       
    $text = "Информация о платеже принята и направлена администратору группы" . PHP_EOL . "До проверки платежа объявления будут показываться." . PHP_EOL . "Для вступления изменений в силу нажмите \"Перезапуск\"";      
    $keyboard = [[$this->callbackButton('Перезапуск', 'update')]];
    $query_data = [
      'chat_id' => $this->update_obj->userId(),
      'text' => $text,
      'reply_markup' => json_encode(['inline_keyboard' => $keyboard]),
      'parse_mode' => 'html',
    ];        
    try {
      $res = $this->sendMessage($query_data);      
    } catch (ClientException $e) {
      $this->actionErrorMessageDev($e);
      $this->actionErrorMessageUser();
    }         
    $out_message_id = $this->getOutMessageIdHelper($res);
    $screen_messages_id = [];
    array_push($screen_messages_id, $out_message_id);
    $this->action_data['next_screen_messages_id'] = $screen_messages_id;
    $this->action_data['next_screen_name'] = 'pay-conf-res';
    if ($this->action_data['session_isset']) {
      $this->delPrevScreen();
    }
    $this->setSessionData();  
      //  Это мы только-что занесли сообщение пользователя об оплате и  отправили ему сообщение о принятии данных об оплате. А теперь отправим уведомление администратору группы       
    $this->action_data['user_id'] = $this->group_admin_id;
    $this->action_data['user_name'] = $this->group_admin_name;
    $this->getSessionData();    
      // Сначала перешлем сообщение пользователя с подтверждением оплаты      
    $query_data = $query_basic + ['chat_id' => $this->group_admin_id];     
    try {
      $res = $this->$tg_method($query_data);      
    } catch (ClientException $e) {
      $this->actionErrorMessageDev($e);
      $this->actionErrorMessageUser();
    }            
    $out_message_id = $this->getOutMessageIdHelper($res);
    $screen_messages_id = [];
    array_push($screen_messages_id, $out_message_id);

    // Теперь добавим пояснительное сообщение с предложением действий
    $text = "Поступил платеж от пользователя:" . PHP_EOL . "ID: $user_id" . PHP_EOL . "Имя: $user_name" . PHP_EOL . "Ссылка: $user_link";
    $keyboard = [[$this->callbackButton('Подтвердить', "user-for-pay-conf-start_$user_id"), $this->callbackButton('Отклонить', "admin-fast-pay-denied_$user_id")]];
    $query_data = [
      'chat_id' => $this->group_admin_id,
      'text' => $text,
      'reply_markup' => json_encode(['inline_keyboard' => $keyboard])
    ];    
    try {
      $res = $this->sendMessage($query_data);
    } catch (ClientException $e) {
      $this->actionErrorMessageDev($e);
      $this->actionErrorMessageUser();
    }       
    $out_message_id = $this->getOutMessageIdHelper($res);    
    array_push($screen_messages_id, $out_message_id);
    $this->action_data['next_screen_messages_id'] = $screen_messages_id;
    $this->action_data['next_screen_name'] = 'admin-pay-conf-res';
  }
  private function actionFastPayDenRes()
  {
    $user_id_for_admin = $this->update_obj->marker();   
    $user_data_for_admin = $this->getUserDataForAdminHelper($user_id_for_admin);
    $user_name_for_admin =  $user_data_for_admin['user_name'];
    $user_link_for_admin = $user_data_for_admin['user_link'];

    $this->builder_users->where(['user_id' => $user_id_for_admin])->set(['temp_access' => 0])->update();
    $text = "Временный доступ для пользователя:" . PHP_EOL . "Имя: $user_name_for_admin" . PHP_EOL . "Ссылка: $user_link_for_admin" . PHP_EOL . "отменен";
    $keyboard = [[$this->button_return,]];
    $query_data = [
      'chat_id' => $this->group_admin_id,
      'text' => $text,
      'reply_markup' => json_encode(['inline_keyboard' => $keyboard]),
      'parse_mode' => 'html',
    ];     
    try {
      $res = $this->sendMessage($query_data);
    } catch (ClientException $e) {
      $this->actionErrorMessageDev($e);
      $this->actionErrorMessageUser();
    }       
    $out_message_id = $this->getOutMessageIdHelper($res);
    $screen_messages_id = [];    
    array_push($screen_messages_id, $out_message_id);
    $this->action_data['next_screen_messages_id'] = $screen_messages_id;
    $this->action_data['next_screen_name'] = 'admin-fast-pay-den-res';
  }  
  private function actionAdminStart()
  {
    $screen_messages_id = [];
    $text = "<b>Просмотр пользователей, которые выслали подтверждение платежа и находятся в состоянии временного доступа</b>";
    $keyboard = [[$this->callbackButton('Выбрать', 'users-for-pay-conf-start')], [$this->button_return]];
    $query_data = [
      'chat_id' => $this->group_admin_id,
      'text' => $text,
      'parse_mode' => 'html',
      'reply_markup' => json_encode(['inline_keyboard' => $keyboard]),
    ];    
    try {
      $res = $this->sendMessage($query_data);
    } catch (ClientException $e) {
      $this->actionErrorMessageDev($e);
      $this->actionErrorMessageUser();
    }        
    $out_message_id = $this->getOutMessageIdHelper($res);        
    array_push($screen_messages_id, $out_message_id);

    $text = "<b>Просмотр списка всех пользователей, получивших доступ</b>";
    $keyboard = [[$this->webButton('Просмотреть пользователей', $this->web_app_url)]];
    $query_data = [
      'chat_id' => $this->group_admin_id,
      'text' => $text,
      'parse_mode' => 'html',      
      'reply_markup' => json_encode([
        'inline_keyboard' => $keyboard,
        // 'keyboard' => $keyboard,
        // 'input_field_placeholder' => 'Нажимай ниже',
        // 'is_persistent' => true,
        // 'resize_keyboard' => true,        
      ]),
    ];    
    try {
      $res = $this->sendMessage($query_data);
    } catch (ClientException $e) {
      $this->actionErrorMessageDev($e);
      $this->actionErrorMessageUser();
    }        
    $out_message_id = $this->getOutMessageIdHelper($res);        
    array_push($screen_messages_id, $out_message_id);
    $this->action_data['next_screen_messages_id'] = $screen_messages_id;
    $this->action_data['next_screen_name'] = 'admin-start';
  }
  private function actionUsersForPayConfStart()
  {
    $keyboard = [[$this->button_return]];
    $users_for_pay_conf_isset = (bool) $this->builder_users_for_admin->countAllResults();
    $screen_messages_id = [];
    if ($users_for_pay_conf_isset) {
      $users_data = $this->builder_users_for_admin->select('user_id, user_name, user_link')->get()->getResultArray();
      foreach ($users_data as &$item) {
        $user_id = $item['user_id'];
        $user_name = $item['user_name'];
        $user_link = $item['user_link'];
        $text = "ID: <b>$user_id</b>" . PHP_EOL . "Имя: <b>$user_name</b>" . PHP_EOL . "Ссылка: <b>$user_link</b>";
        $keyboard_item = [[$this->callbackButton('Выбрать', "user-for-pay-conf-start_$user_id")]];
        $query_data = [
          'chat_id' => $this->group_admin_id,
          'text' => $text,
          'parse_mode' => 'html',
          'reply_markup' => json_encode(['inline_keyboard' => $keyboard_item]),
        ];        
        try {
          $res = $this->sendMessage($query_data);
        } catch (ClientException $e) {
          $this->actionErrorMessageDev($e);
          $this->actionErrorMessageUser();
        }             
        $out_message_id = $this->getOutMessageIdHelper($res);           
        array_push($screen_messages_id, $out_message_id);
      }
      unset($item); 
      $text = "<b>Выберите варианты действий</b>";
      $query_data = [
        'chat_id' => $this->group_admin_id,
        'text' => $text,
        'parse_mode' => 'html',
        'reply_markup' => json_encode(['inline_keyboard' => $keyboard]),
      ];      
      try {
        $res = $this->sendMessage($query_data);
      } catch (ClientException $e) {
        $this->actionErrorMessageDev($e);
        $this->actionErrorMessageUser();
      }          
      $out_message_id = $this->getOutMessageIdHelper($res);           
      array_push($screen_messages_id, $out_message_id);

    } else {
      $text = "<b>Пользователи, которым необходимо подтвердить платеж, отсутствуют</b>"; 
      $query_data = [
        'chat_id' => $this->group_admin_id,
        'text' => $text,
        'parse_mode' => 'html',
        'reply_markup' => json_encode(['inline_keyboard' => $keyboard]),
      ];      
      try {
        $res = $this->sendMessage($query_data);
      } catch (ClientException $e) {
        $this->actionErrorMessageDev($e);
        $this->actionErrorMessageUser();
      }          
      $out_message_id = $this->getOutMessageIdHelper($res);           
      array_push($screen_messages_id, $out_message_id);     
    }
    $this->action_data['next_screen_messages_id'] = $screen_messages_id;
    $this->action_data['next_screen_name'] = 'users-for-pay-conf-start';
  }
  private function actionUserForPayConfStart()
  {
    $user_id_for_admin = $this->update_obj->marker();    
    $user_data_for_admin = $this->getUserDataForAdminHelper($user_id_for_admin);
    $user_name_for_admin = $user_data_for_admin['user_name'];
    $user_link_for_admin = $user_data_for_admin['user_link'];
    $pay_message_data = json_decode($user_data_for_admin['pay_message'], true);
    $tg_method = $pay_message_data[0];
    $query_basic = $pay_message_data[1];
    $screen_messages_id = [];
      // Отправляем pay_message
    $query_data = $query_basic + ['chat_id' => $this->group_admin_id];     
    try {
      $res = $this->$tg_method($query_data);
    } catch (ClientException $e) {
      $this->actionErrorMessageDev($e);
      $this->actionErrorMessageUser();
    }       
    $out_message_id = $this->getOutMessageIdHelper($res);        
    array_push($screen_messages_id, $out_message_id);
    // Отправляем сообщение админу с предложением действий
    $text = "Открываем доступ для пользователя:" . PHP_EOL . "ID: <b>$user_id_for_admin</b>" . PHP_EOL . "Имя: <b>$user_name_for_admin</b>" . PHP_EOL . "Ссылка: <b>$user_link_for_admin</b>";
    $keyboard = [[$this->callbackButton('Подтвердить', "pay-conf_$user_id_for_admin")], [$this->callbackButton('Отказать', "pay-den_$user_id_for_admin")], [$this->button_return]];     
    $query_data = [
      'chat_id' => $this->group_admin_id,
      'text' => $text,
      'parse_mode' => 'html', 
      'reply_markup' => json_encode(['inline_keyboard' => $keyboard]),     
    ];    
    try {
      $res = $this->sendMessage($query_data);
    } catch (ClientException $e) {
      $this->actionErrorMessageDev($e);
      $this->actionErrorMessageUser();
    }        
    $out_message_id = $this->getOutMessageIdHelper($res);        
    array_push($screen_messages_id, $out_message_id);
    $this->action_data['next_screen_messages_id'] = $screen_messages_id;
    $this->action_data['next_screen_name'] = 'user-for-pay-conf-start';
  }
  private function actionUserForPayConfRes()
  {    
    $user_id_for_admin = $this->update_obj->marker();   
    $user_data_for_admin = $this->getUserDataForAdminHelper($user_id_for_admin);
    $user_name_for_admin =  $user_data_for_admin['user_name'];
    $user_link_for_admin = $user_data_for_admin['user_link'];

    $date_pay_first = $this->builder_users->select('date_pay')->where(['user_id' => $user_id_for_admin])->get()->getResultArray()[0]['date_pay'];
    $date_pay_first_obj = new \DateTime($date_pay_first, $this->date_time_zone_obj);
    $date_current_obj = new \DateTime('now', $this->date_time_zone_obj);    
    $date_current = $date_current_obj->format($this->date_format);
    $res1 = $date_current;
    $interval = $date_current_obj->diff($date_pay_first_obj);
     
    $date_pay_sec_obj = new \DateTime('1month', $this->date_time_zone_obj);

    $date_pay_obj = $date_pay_sec_obj->add($interval);
      $date_pay = $date_pay_obj->format($this->date_format);
    $res2 = $date_pay;
    $this->builder_users->where(['user_id' => $user_id_for_admin])->set(['date_pay' => $date_pay, 'temp_access' => 0])->update();    
    $text = "Результат обработки платежа для пользователя:" . PHP_EOL . "Имя: $user_name_for_admin" . PHP_EOL . "Ссылка: $user_link_for_admin" . PHP_EOL . "Текущая дата: <b>$res1</b>" . PHP_EOL . "Дата окончания платежа: <b>$res2</b>" . PHP_EOL . "Если хотите изменить решение, нажмите <b>\"Повторить</b>\""  . PHP_EOL . "Если хотите зафиксировать выбор, нажмите <b>\"Завершить</b>\", в этом случае пользователь получит постоянный доступ на один год, а данные его платежа будут удалены.";
    $keyboard = [[$this->callbackButton('Повторить', "repeat_$user_id_for_admin"), $this->callbackButton('Завершить', "fix-pay-conf-res_$user_id_for_admin")]];
    $query_data = [
      'chat_id' => $this->group_admin_id,
      'text' => $text,
      'parse_mode' => 'html',
      'reply_markup' => json_encode(['inline_keyboard' => $keyboard])
    ];     
    try {
      $res = $this->sendMessage($query_data);
    } catch (ClientException $e) {
      $this->actionErrorMessageDev($e);
      $this->actionErrorMessageUser();
    }       
    $out_message_id = $this->getOutMessageIdHelper($res);
    $screen_messages_id = [];    
    array_push($screen_messages_id, $out_message_id);
    $this->action_data['next_screen_messages_id'] = $screen_messages_id;
    $this->action_data['next_screen_name'] = 'user-for-pay-conf-res';
  }
  private function actionUserForPayConfFin()
  {
    $user_id_for_admin = $this->update_obj->marker();    
    $user_data_for_admin = $this->getUserDataForAdminHelper($user_id_for_admin);   
    $user_name_for_admin =  $user_data_for_admin['user_name'];
    $user_link_for_admin = $user_data_for_admin['user_link'];

    $user_data = $this->builder_users->select('date_pay, temp_access')->where(['user_id' => $user_id_for_admin])->get()->getResultArray()[0];
    $date_pay = $user_data['date_pay'];

    $text = "Результат обработки платежа для пользователя:" . PHP_EOL . "Имя: $user_name_for_admin" . PHP_EOL . "Ссылка: $user_link_for_admin" . PHP_EOL . "Дата окончания платежа: <b>$date_pay</b>" . PHP_EOL . "Временный допуск заменен на постоянный" . PHP_EOL . "Данные о платеже удалены из базы данных";
    $keyboard = [[$this->callbackButton('В админку', 'admin-start'), $this->callbackButton('На старт', 'start')],];
    $query_data = [
      'chat_id' => $this->group_admin_id,
      'text' => $text,
      'parse_mode' => 'html',
      'reply_markup' => json_encode(['inline_keyboard' => $keyboard])
    ];    
    try {
      $res = $this->sendMessage($query_data);
    } catch (ClientException $e) {
      $this->actionErrorMessageDev($e);
      $this->actionErrorMessageUser();
    }        
    $out_message_id = $this->getOutMessageIdHelper($res);
    $screen_messages_id = [];    
    array_push($screen_messages_id, $out_message_id);
    $this->action_data['next_screen_messages_id'] = $screen_messages_id;
    $this->action_data['next_screen_name'] = 'user-for-pay-conf-fin'; 
    $this->builder_users_for_admin->where(['user_id' => $user_id_for_admin])->delete(); 
  } 
  private function actionUserForPayDenRes()
  {
    $screen_messages_id = [];
    $user_id_for_admin = $this->update_obj->marker();   
    $user_data_for_admin = $this->getUserDataForAdminHelper($user_id_for_admin);
    $user_name_for_admin =  $user_data_for_admin['user_name'];
    $text = "Вы хотите удалить пользователя $user_name_for_admin ($user_id_for_admin) из списка подавших заявки на подтверждение платежа ?";
    $keyboard = [[$this->callbackButton('Удаляем', "del-user-for-admin_$user_id_for_admin")], [$this->button_return]];
    $query_data = [
      'chat_id' => $this->group_admin_id,
      'text' => $text,
      'reply_markup' => json_encode([
        'inline_keyboard' => $keyboard
      ])
    ];
    try {
      $res = $this->sendMessage($query_data);
    } catch (\Throwable $e) {
      $this->actionErrorMessageDev($e);
      $this->actionErrorMessageUser();
      die;
    }
    $out_message_id = $this->getOutMessageIdHelper($res);
    array_push($screen_messages_id, $out_message_id);
    $this->action_data['next_screen_name'] = 'user-for-pay-den-res';
    $this->action_data['next_screen_messages_id'] = $screen_messages_id;
  }

  private function actionUserForPayDenFin()
  {
    $screen_messages_id = [];
    $user_id_for_admin = $this->update_obj->marker();   
    $user_data_for_admin = $this->getUserDataForAdminHelper($user_id_for_admin);
    $user_name_for_admin =  $user_data_for_admin['user_name'];

    $this->builder_users_for_admin->where(['user_id' => $user_id_for_admin])->delete();
    $this->builder_users->where(['user_id' => $user_id_for_admin])->delete();

    $text = "Вы успешно удалили пользователя $user_name_for_admin ($user_id_for_admin) из списка подавших заявки на подтверждение платежа ?";
    $keyboard = [[$this->callbackButton('В админку', 'admin-start'), $this->callbackButton('На старт', 'start')],];
    $query_data = [
      'chat_id' => $this->group_admin_id,
      'text' => $text,
      'reply_markup' => json_encode([
        'inline_keyboard' => $keyboard
      ])
    ];
    try {
      $res = $this->sendMessage($query_data);
    } catch (\Throwable $e) {
      $this->actionErrorMessageDev($e);
      $this->actionErrorMessageUser();
      die;
    }
    $out_message_id = $this->getOutMessageIdHelper($res);
    array_push($screen_messages_id, $out_message_id);
    $this->action_data['next_screen_name'] = 'user-for-pay-den-fin';
    $this->action_data['next_screen_messages_id'] = $screen_messages_id;
  }


  private function actionShowAllUsersResult()
  { 
    $screen_messages_id = []; 
    // Сначала мы отработаем webAppQuery
    // Для этого мы отправим пользователю сообщение методом answerWebAppQuery, спользуя 'result' ->  	InlineQueryResult -> InlineQueryResultArticle
    $query_id = $this->update_obj->webAppQueryId();// ключевой параметр для   answerWebAppQuery  
    $client_id = $this->update_obj->getWebAppData()['client_id'];  
    try {
      $client_name = $this->getUserName($client_id);
    } catch (ClientException $e) {
      $this->actionErrorMessageDev($e);
      $client_name = 'No name';
    }    
    try {
      if (!empty($query_id)) {
        $keyboard = [[$this->callbackButton('Продолжить', "continue_$client_id")], [$this->button_return]];
        $query_data = [
          'web_app_query_id' => $query_id,
          'result' => json_encode([
            'type' => 'article',
            'id' => $query_id,
            'title' => 'Выбор пользователя',
            'input_message_content' => [
            'parse_mode' => 'html',
            'message_text' => "Пользователь $client_name ID: <b>$client_id</b>  успешно выбран",
            ],
            'reply_markup' => [
              'inline_keyboard' => $keyboard,
            ],
          ]),
        ];        
        try {
          $res = $this->answerWebAppQuery($query_data);
        } catch (ClientException $e) {
          $this->actionErrorMessageDev($e);
          $this->actionErrorMessageUser();
        }
        // $this->action_data['next_screen_name'] = 'web-app-start';
        // $this->action_data['next_screen_messages_id'] = $screen_messages_id;
        // writeLogFile($res, true);
        // $out_message_id = $this->getOutMessageIdHelper($res);
        // array_push($screen_messages_id, $out_message_id);
        
      } else {
        throw new UpdateException('Отсутствует $query_id для answerWebAppQuery');
      }
    } catch (UpdateException $e) {
      $this->actionErrorMessageDev($e);
      $this->actionErrorMessageUser(); 
    } 
  }
  private function actionShowUser()
  {
    $screen_messages_id = [];
    $client_id = $this->update_obj->marker();     
      
    $client_data = $this->builder_users->select('user_name')->where(['user_id' => $client_id])->get()->getResultArray();       
    
    $client_name = $client_data[0]['user_name'];
    $client_link = $this->getUserLink($client_id);    
    $text = "Пользователь: " . PHP_EOL . "ID: <b>$client_id </b>" . "Имя: <b>$client_name</b>" . PHP_EOL . "Ссылка: <b>$client_link</b>";
    $keyboard = [[$this->callbackButton('Удалить пользователя', "del-user_$client_id")], [$this->button_return]];   
    $query_data = [
      'chat_id' => $this->group_admin_id,
      'text' => $text,
      'reply_markup' => json_encode(['inline_keyboard' => $keyboard]),
      'parse_mode' => 'html',
    ];    
    try {
      $res = $this->sendMessage($query_data);
    } catch (ClientException $e) {
      $this->actionErrorMessageDev($e);
      $this->actionErrorMessageUser();
    }    
    $out_message_id = $this->getOutMessageIdHelper($res);
    array_push($screen_messages_id, $out_message_id);
    $this->action_data['next_screen_name'] = 'show-user';
    $this->action_data['next_screen_messages_id'] = $screen_messages_id;
  }    
  private function actionDelUser()
  {
    $user_id = $this->update_obj->marker();           
    $res = $this->builder_users->where(['user_id' => $user_id])->delete();// удаляем из памяти упоминание о юзере
    $res = $this->builder_users_for_admin->where(['user_id' => $user_id])->delete();// удаляем из подавших заявки
    
    // Удаляем сообщения в личном кабинете юзера
    $res = $screen_messages_id_in = $this->builder_priv_sessions->select('screen_messages_id')->where(['user_id' => $user_id])->get()->getResultArray();    
    if (!empty($screen_messages_id_in)) {
      $screen_messages_id = json_decode($screen_messages_id_in[0]['screen_messages_id'], true);
    } else {
      $screen_messages_id = [];
    } 
    if (!empty($screen_messages_id)) {
      foreach ($screen_messages_id as $item) {
        $this->delAnyMessage($user_id, $item);
      }
    }    
    $this->builder_priv_sessions->where(['user_id' => $user_id])->delete();// удаляем из памяти сессию юзера 
    $this->action_data['next_screen_messages_id'] = $this->action_data['screen_messages_id'];
    $this->action_data['next_screen_name'] = 'del-user';   
  }  
  private function getUserDataForAdminHelper($user_id_for_admin)
  {
    return $this->builder_users_for_admin->select('user_name, user_link, pay_message')->where(['user_id' => $user_id_for_admin])->get()->getResultArray()[0];
  } 
  private function sendBotMessageInGroupAndSave()
  {
    $bot_messages_count = $this->builder_bot_group_adts->countAllResults();
    $limit = 4;
    if ($bot_messages_count > $limit) {
      die;
    } else {
      $bot_link = $this->getBotLink();        
      $chat_id = $this->update_obj->chatId();
      $user_name = $this->update_obj->userName();
      $user_id = $this->update_obj->userId();
      if ($bot_messages_count == $limit) {        
        $text = "<b>Здравствуйте, очередной посетитель!</b>" . PHP_EOL . "<b>Для подачи объявления в группу переходите по ссылке:</b>" . PHP_EOL . "<b>$bot_link</b>";
      } else {
        $text = "<b>Здравствуйте, $user_name !</b>" . PHP_EOL . "<b>Для подачи объявления в группу переходите по ссылке:</b>" . PHP_EOL . "<b>$bot_link</b>";
      }
    }        
    $query_data = [
      'chat_id' => $chat_id,
      'text' => $text,
      'parse_mode' => 'html',                 
    ];    
    try {
      $res = $this->sendMessage($query_data);
    } catch (ClientException $e) {
      $this->actionErrorMessageDev($e);      
    }        
    $out_message_id = $this->getOutMessageIdHelper($res);        
    $res = $this->builder_bot_group_adts->insert(['message_id' => $out_message_id, 'from_user_id' => $user_id, 'from_user_name' => $user_name ]);   
    return $res;         
  }
  protected function handleUserAdt()
  {
    $access_isset = $this->action_data['access_isset']; 
    if ($access_isset) {
      if (!($this->update_obj->groupIsset())) {
        $query_data = [
          'chat_id' => $this->group_id,
          'from_chat_id' => $this->update_obj->userId(),
          'message_id' => $this->update_obj->messageId(),
        ];
        $res = $this->forwardMessage($query_data);
      }
    }            
  }
  public function delBotMessAction()
  {
    $this->delBotMessages();    
    return true;    
  }
  private function delBotMessages()
  {     
    $date_current_obj = new \DateTime('now', $this->date_time_zone_obj);       
    $bot_messages_id = $this->builder_bot_group_adts->get()->getResultArray();   
    $deleted_messages = [];    
    foreach ($bot_messages_id as &$item) {      
      $date_created = $item['created_at'];      
      $date_created_obj = new \DateTime($date_created, $this->date_time_zone_obj);
      $interval = $date_current_obj->diff($date_created_obj);
      $minutes = $interval->i;
      $seconds = $interval->s;
      $time_sec = $minutes*60 + $seconds;      
      try {
        if ($time_sec > 40) {
          $this->delAnyMessage($this->group_id, $item['message_id']);        
          array_push($deleted_messages, $item['message_id']);
        }        
      } catch (\Throwable $e) {
        if ($time_sec > 240) {
          array_push($deleted_messages, $item['message_id']);
        }
        $comment_text = "Ошибка удаления сообщения {$item['message_id']} бота";        
        $this->actionErrorLogDev($e, $comment_text);
      }                       
    }
    unset($item); 
    if (!empty($deleted_messages)) {
      $this->builder_bot_group_adts->whereIn('message_id', $deleted_messages)->delete(); 
    }                 
    return $deleted_messages;
  }
 
  public function delUsersAction()
  {
    $date_current_obj = new \DateTime('now', $this->date_time_zone_obj);    
      $users_no_pay =  $this->builder_users->where(['date_pay' => '', 'temp_access !=' => 1])->get()->getResultArray();       
      $users_to_delete = [];
      foreach ($users_no_pay as &$item) {
          $data_created_at = $item['created_at'];
          $date_created_at_obj = new \DateTime($data_created_at, $this->date_time_zone_obj);
          $interval = $date_current_obj->diff($date_created_at_obj);
          $days = $interval->days;
          $hours = $interval->h;
          $time_hours = $days*24 + $hours;                   
          if ($time_hours > 6) {
              $user_id = $item['user_id'];
              array_push($users_to_delete, $user_id);
          }
        }
        unset($item);               
        if (!empty($users_to_delete)) {
            $this->builder_users->whereIn('user_id', $users_to_delete)->delete();           
        } 
        return $users_to_delete;
  }

  public function delSessionsAction()
  {    
    $users_to_delete = [];
    $date_current_obj = new \DateTime('now', $this->date_time_zone_obj);       
    $user_sessions = $this->builder_priv_sessions->select('user_id, created_at, screen_messages_id')->get()->getResultArray();          
    foreach ($user_sessions as &$item) { 
      $user_id = $item['user_id'];         
      $date_created = $item['created_at'];      
      $date_created_obj = new \DateTime($date_created, $this->date_time_zone_obj);
      $interval = $date_current_obj->diff($date_created_obj);     
      $days = $interval->days;
      $hours = $interval->h;
      $time_hours = $days*24 + $hours;           
      try {
        if ($time_hours > 6) {
           $screen_messages_id = json_decode($item['screen_messages_id'], true); 
           foreach ($screen_messages_id as $item) {            
            $res = $this->delAnyMessage($user_id, $item);            
           }
           array_push($users_to_delete, $user_id);         
        }        
      } catch (\Throwable $e) { 
        $comment_text = 'Удаление сессии';        
        $this->actionErrorLogDev($e, $comment_text);
      }                       
    }
    unset($item);
    if (!empty($users_to_delete)) {
      $this->builder_priv_sessions->whereIn('user_id', $users_to_delete)->delete();    
    }
    return $users_to_delete;    
  }

 
 
 
 


}