<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use DateTimeZone;

class Main extends BaseController
{
    private $location = 'Europe/Minsk';
    private $date_format = 'Y-m-d H:i:s';
    private $date_time_zone_obj;
    private $builder_users;
    private $builder_priv_sessions;

    public function __construct()
    {
        $db =  \Config\Database::connect();
        $this->builder_users = $db->table('users');
        $this->builder_priv_sessions =  $db->table('private_sessions');
        $this->date_time_zone_obj = new DateTimeZone($this->location);
    }
    public function index()
    {

        
        
        $date_current_obj = new \DateTime('now', $this->date_time_zone_obj);
        $users_no_pay =  $this->builder_users->where(['date_pay' => ''])->get()->getResultArray();       
        $users_to_delete = [];
        foreach ($users_no_pay as &$item) {
            $data_created_at = $item['created_at'];
            $date_created_at_obj = new \DateTime($data_created_at, $this->date_time_zone_obj);
            $interval = $date_current_obj->diff($date_created_at_obj);
            $days = $interval->days;
            $hours = $interval->h;                   
            if ($hours >= 2) {
                $user_id = $item['user_id'];
                array_push($users_to_delete, $user_id);
            }
        }
        unset($item);         
        if (!empty($users_to_delete)) {
            $this->builder_users->whereIn('user_id', $users_to_delete)->delete();            
            $this->builder_priv_sessions->whereIn('user_id', $users_to_delete)->delete();
            return true;
            
        } else {
            return false;
        }
        
          
        
        
        
        
    }
}
