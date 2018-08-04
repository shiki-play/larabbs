<?php
namespace App\Models\Traits ;

use App\Models\Topics;
use App\Models\Reply;
use Caebon\Carbon;
use Cache;
use DB;

trait ActiveUserHelper
{
    protected $users = [];

    protected $topic_weight = 4;
    protected $reply_weight = 1;
    protected $pass_days = 7;
    protected $user_number = 6;

    protected $cache_key = 'larabbs_active_users';
    protected $cache_expire_in_minutes = 65;

    public function getActiveUsers(){
        return Cache::remember($this->cache_key,$this->cache_expire_in_minutes,function(){
            return $this->calcilateActiveUsers();
        });
    }

    public function calculateAndCacheActiveUsers()
    {
        $active_users = $this->calcilateActiveUsers();
        $this->cacheActiveUsers($active_users);
    }

    private function calculateActiveUsers()
    {

    }
}