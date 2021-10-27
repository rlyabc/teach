<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;


    /**
     * 遍历数组组成树状结构
     * @param $result
     * @param int $parentId
     * @return array
     */
    public static function toTree($result, $parentId = 0)
    {
        $tree = [];
        foreach ($result as $key => $value)
        {
            //判断当前记录的父ID跟$parentId是否相等
            if ($value['pid'] == $parentId) {
                //进行递归循环 把当前的记录的id当作parentID，在与结果集进行对比找到儿子
                $child = self::toTree($result, $value['id']);
                //过滤掉空的数组
                if ($child) {
                    $value['children'] = $child;
                }
                $tree[] = $value;
                unset($result[$key]);
            }
        }

        return $tree;
    }


    public function generateToken($user){
        $api_token=$user->api_token;
        $cache_key=$this->getCacheKey($api_token);
        $cache_value = $this->getCacheValue($cache_key);
        if($cache_value){
            //return $this->api_token;
        }else{
            $new_api_token=str_random(60);
            $user->api_token = $new_api_token;

            $user->save();
            //return $this->api_token;
            //生成新的key
            $cache_key=$this->getCacheKey($new_api_token);
            Cache::store('file')->put($cache_key, '1', 300);

            //return $this->api_token;
        }


    }

    public function getCacheValue($key){
        return $value = Cache::store('file')->get($key);
    }

    public function getCacheKey($api_token){
        return $key='api_token_'.$api_token;
    }
}
