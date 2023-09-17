<?php
namespace App\Bots;

use SebastianBergmann\CodeCoverage\Driver\WriteOperationFailedException;

class UpdateClass
{
  public $data;
  public function __construct($data)
  {
    $this->data = $data;
  }
  public function messageIsset()
  {
    return isset($this->data['message']) ?? false;
  }  
  public function callbackIsset()
  {
    return isset($this->data['callback_query']) ?? false;
  }
  public function myChatMemberIsset()
  {
    return isset($this->data['my_chat_member']) ?? false;
  }
  public function updateType()
  {
    switch (true) {
      case $this->messageIsset():
        return $this->data['message']['chat']['type'] ?? '';
        break;
      case $this->callbackIsset():
        return  $this->data['callback_query']['message']['chat']['type'] ?? '';
        break;      
      default:
        return '';
        break;
    }
  }
  public function groupIsset()
  {
    if (($this->updateType() == 'group') || ($this->updateType() == 'supergroup')) {
      return true;
    } else {
      return false;
    }
  }
 /*  public function otherMessageIsset()
  {
    $other_message_keys = ['new_chat_member' => '', 'new_chat_members' => '', 'new_chat_participant' => '', 'message_auto_delete_timer_changed' => '', 'left_chat_participant' => '', 'left_chat_member' => '', 'pinned_message' => '' ];
    // $service_message_keys = [];
    if ($this->messageIsset()) {
      $intersect = array_intersect_key($this->data['message'], $other_message_keys);
      if (empty($intersect)) {
        return false;
      } else {
        return true;
      }
    } else {
      return false;
    }   
  } */
  public function otherUpdateTypesIsset()
  {
    $other_update_types = ['edited_message', 'channel_post', 'edited_channel_post', 'inline_query', 'chosen_inline_result', 'shipping_query', 'pre_checkout_query', 'poll', 'poll_answer', 'my_chat_member', 'chat_member','chat_join_request'];
    $other_update_types_keys = array_flip($other_update_types);
    $intersect = array_intersect_key($this->data, $other_update_types_keys);
      if (empty($intersect)) {
        return false;
      } else {
        return true;
      }
  }
  public function textIsset()
  {
    return isset($this->data['message']['text']);
  }
  public function photoIsset()
  {
    return isset($this->data['message']['photo']);
  }
  public function videoIsset()
  {
    return isset($this->data['message']['video']);
  }
  public function videoNoteIsset()
  {
    return isset($this->data['message']['video_note']);
  }
  public function stickerIsset()
  {
    return isset($this->data['message']['sticker']);
  }
  public function animationIsset()
  {
    return isset($this->data['message']['animation']);
  }
  public function voiceIsset()
  {
    return isset($this->data['message']['voice']);
  }
  public function documentIsset()
  {
    return isset($this->data['message']['document']);
  }
  public function viaBotIsset()
  {
    return isset($this->data['message']['via_bot']);
  }
  // Эти 2 функции мы будем использовать для работы с веб-приложениями, вызванными обычной кнопкой
  public function webAppSendDataIsset()
  {
    return isset($this->data['message']['web_app_data']);
  }
  public function getWebAppButtonText()
  {
    return $this->data['message']['web_app_data']['button_text'];
  }
  // Эту функцию мы будем использовать для работы с веб-приложениями, вызванными обычной кнопкой и инлайн кнопкой
  public function getWebAppData()
  {
    if ($this->webAppSendDataIsset()) {
      return $this->data['message']['web_app_data']['data'];
    }
    if ($this->webAppQueryIsset()) {
      return $this->data['web_app_query']['data'];
    }
    
  }
  // Эти 2 функции мы будем использовать для работы с веб-приложениями, вызванными инлайн - кнопкой
  public function webAppQueryIsset()
  {
    return isset($this->data['web_app_query']);
  }
  public function webAppQueryId()
  {
    return $this->data['web_app_query']['init_data']['query_id'];
  } 
  public function captionIsset()
  {
    return isset($this->data['message']['caption']);
  }
 
  public function caption()
  {
    if ($this->messageIsset()) {
      return $this->data['message']['caption'] ?? '';
    } else {
      return false;
    }    
  }
  public function photoFileId()
  {
    if ($this->messageIsset()) {
      return $photo_id = $this->data['message']['photo'][0]['file_id'] ?? false;
    } else {
      return false;
    }    
  }
  public function videoFileId()
  {
    if ($this->messageIsset()) {
      return $this->data['message']['video']['file_id'] ?? false;
    } else {
      return false;
    }    
  }
  public function videoNoteFileId()
  {
    if ($this->messageIsset()) {
      return $this->data['message']['video_note']['file_id'] ?? false;
    } else {
      return false;
    }    
  }
  public function stickerFileId()
  {
    if ($this->messageIsset()) {
      return $this->data['message']['sticker']['file_id'] ?? false;
    } else {
      return false;
    }    
  }
  public function animationFileId()
  {
    if ($this->messageIsset()) {
      return $this->data['message']['animation']['file_id'] ?? false;
    } else {
      return false;
    }    
  }
  public function voiceFileId()
  {
    if ($this->messageIsset()) {
      return $this->data['message']['voice']['file_id'] ?? false;
    } else {
      return false;
    }    
  }
  public function documentFileID()
  {
    if ($this->messageIsset()) {
      return $this->data['message']['document']['file_id'] ?? false;
    } else {
      return false;
    }    
  }
  public function documentFileName()
  {
    if ($this->messageIsset()) {
      return $this->data['message']['document']['file_name'] ?? false;
    } else {
      return false;
    }    
  }
  public function documentMimeType()
  {
    if ($this->messageIsset()) {
      return $this->data['message']['document']['mime_type'] ?? false;
    } else {
      return false;
    }    
  }
  public function chatId()
  {
    switch (true) {
      case $this->messageIsset():
        return $this->data['message']['chat']['id'] ?? false;
        break;
      case $this->callbackIsset():
        return $this->data['callback_query']['message']['chat']['id'] ?? false;
        break;      
      default:
        return false;
        break;
    }
  }
  public function messageId()
  {
    if ($this->messageIsset()) {
      return $this->data['message']['message_id'] ?? false;
    } else {
      return false;
    }
  }
  public function callbackMessageID()
  {
    if ($this->callbackIsset()) {
      return $this->data['callback_query']['message']['message_id'] ?? false;
    } else {
      return false;
    }
  }
  public function callbackQueryId()
  {
    if ($this->callbackIsset()) {
      return $this->data['callback_query']['id'] ?? false;
    } else {
      return false;
    }
  }
  public function userId()
  {    
    switch (true) {
      case $this->messageIsset():
        return $this->data['message']['from']['id'] ?? false;
        break;
      case $this->callbackIsset():
        return $this->data['callback_query']['from']['id'] ?? false;
        break;
      case $this->webAppQueryIsset():
        return $this->data['web_app_query']['init_data']['user']['id'] ?? false;
        break;      
      default:
        return false;
        break;
    }
  }
  public function text()
  {
    if ($this->messageIsset()) {
      return $this->data['message']['text'] ?? '';
    } else {
      return false;
    }    
  }
  public function callbackData()
  {
    if ($this->callbackIsset()) {
      $callback_data = $this->data['callback_query']['data'] ?? '';
      return explode('_', $callback_data, 2)[0] ?? '';
    } else {
      return false;
    }
  }
  public function marker()
  {
    if ($this->callbackIsset()) {
      $callback_data = $this->data['callback_query']['data'] ?? '';
      return explode('_', $callback_data, 2)[1] ?? '';
    } else {
      return false;
    }
  }
  public function userName()
  {
    switch (true) {
      case $this->messageIsset():
        $username = $this->data['message']['from']['username'] ?? '';        
        $first_name = $this->data['message']['from']['first_name'] ?? '';
        break;
      case $this->callbackIsset():
        $username = $this->data['callback_query']['from']['username'] ?? '';
        $first_name = $this->data['callback_query']['from']['first_name'] ?? '';
        break;
      case $this->webAppQueryIsset():
        $username = $this->data['web_app_query']['init_data']['user']['username'] ?? '';
        $first_name = $this->data['web_app_query']['init_data']['user']['first_name'] ?? '';
        break;      
      default:
        return false;
        break;
    }
    $name =  $first_name ?? $username;
    return $name;
  }
  public function userLink()
  {
    switch (true) {
      case $this->messageIsset():
        $username = $this->data['message']['from']['username'] ?? '';        
        break;
      case $this->callbackIsset():
        $username = $this->data['callback_query']['from']['username'] ?? '';        
        break;
      case $this->webAppQueryIsset():
        $username = $this->data['web_app_query']['init_data']['user']['username'] ?? '';        
        break;      
      default:
        return false;
        break;
    }
    $user_link = "@$username";
    return $user_link;
  }
  public function messageThreadIsset()
  {
    return isset($this->data['message']['reply_to_message']);
  }
  public function messageThreadId()
  {
    if ($this->messageThreadIsset()) {
      return $this->data['message']['message_thread_id'] ?? false;
    } else {
      return false;
    }
  }
  public function forwardIsset()
  {
    return isset($this->data['message']['forward_date']) ?? false;
  }
  public function handleMessageData()
  {    
    $caption_isset = $this->captionIsset();
    $caption = $this->caption();        
    switch (true) {
      case $this->photoIsset():
        $tg_method = 'sendPhoto';
        $photo_id = $this->photoFileId();
        if ($caption_isset) {          
          $query_basic = [
            'photo' => $photo_id,
            'caption' => $caption,
          ];
        } else {
          $query_basic = [
            'photo' => $photo_id,
          ];
        }
        break;
      case $this->videoIsset():       
        $tg_method = 'sendVideo';
        $video_id = $this->videoFileId();
        if ($caption_isset) {          
          $query_basic = [
            'video' => $video_id,
            'caption' => $caption,
          ];
        } else {
          $query_basic = [
            'video' => $video_id,
          ];
        }
        break;
      case $this->videoNoteIsset():       
        $tg_method = 'sendVideo';
        $video_id = $this->videoNoteFileId();
        if ($caption_isset) {          
          $query_basic = [
            'video' => $video_id,
            'caption' => $caption,
          ];
        } else {
          $query_basic = [
            'video' => $video_id,
          ];
        }
        break;
      case $this->stickerIsset():       
        $tg_method = 'sendAnimation';        
        $sticker_id = $this->stickerFileId();
        if ($caption_isset) {          
          $query_basic = [            
            'animation' => $sticker_id,
            'caption' => $caption,
          ];
        } else {
          $query_basic = [            
            'animation' => $sticker_id,
          ];
        }
        break;
      case $this->animationIsset():       
        $tg_method = 'sendAnimation';        
        $animation_id = $this->animationFileId();
        if ($caption_isset) {          
          $query_basic = [            
            'animation' => $animation_id,
            'caption' => $caption,
          ];
        } else {
          $query_basic = [            
            'animation' => $animation_id,
          ];
        }
        break;
      case $this->voiceIsset():       
        $tg_method = 'sendVoice';        
        $voice_id = $this->voiceFileId();
        if ($caption_isset) {          
          $query_basic = [            
            'voice' => $voice_id,
            'caption' => $caption,
          ];
        } else {
          $query_basic = [            
            'voice' => $voice_id,
          ];
        }
        break;
      case $this->documentIsset():       
        $tg_method = 'sendDocument';        
        $document_file_id = $this->documentFileID();
        if ($caption_isset) {          
          $query_basic = [            
            'document' => $document_file_id,
            'caption' => $caption,
          ];
        } else {
          $query_basic = [            
            'document' => $document_file_id,
          ];
        }
        break; 
      case $this->textIsset():
        $text = $this->text();
        $tg_method = 'sendMessage';                 
        $query_basic = [
          'text' => $text,
        ];
        break;
      default: 
        writeLogFile($this->data);    
         throw new UpdateException("Ошибка метода handleMessageData");       
        break;
    } 
    $res = [];
    array_push($res, $tg_method, $query_basic);
    return $res;   
  }
  public function messageAutoDeleteTimerChangedIsset()
  {
    return isset($data['message']['message_auto_delete_timer_changed']);
  }
}