<?php


namespace Epajarito\SystemHistoricalLogs\Traits;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Epajarito\SystemHistoricalLogs\Models\SystemLog;

trait ModelLog
{

    public static function bootModelLog()
    {
        static::saved(function($model){
           if($model->wasRecentlyCreated){
                static::storeLog($model,static::class,'CREATED');
           }else{
               if(!$model->getChanges()){
                   return;
               }
               static::storeLog($model,static::class,'UPDATED');
           }
        });

        static::deleted(function($model){
            static::storeLog($model,static::class,'DELETED');
        });
    }

    public static function getTagName(Model $model)
    {
        return !empty($model->tagName)
            ? $model->tagName
            : Str::title(Str::snake(class_basename($model),' '));
    }

    public static function activeUserId()
    {
        return Auth::guard(static::activeUserGuard())->id();
    }

    public static function activeUserGuard()
    {
        foreach (array_keys(config('auth.guards')) as $guard){
            if(\auth()->guard($guard)->check()){
                return $guard;
            }
        }
        return null;
    }

    public static function storeLog(Model $model,$modelPath,$action)
    {
        $newValues = null;
        $oldValues = null;
        if($action === 'CREATED'){
            $newValues = $model->getAttributes();
        }elseif($action === 'UPDATED'){
            $newValues = $model->getChanges();
        }

        if($action !== 'CREATED'){
            $oldValues = $model->getOriginal();
        }

        $systemLog = new SystemLog();
        $systemLog->system_logable_id = $model->id;
        $systemLog->system_logable_type = $modelPath;
        $systemLog->user_id = static::activeUserId();
        $systemLog->guard_name = static::activeUserGuard();
        $systemLog->module_name = static::getTagName($model);
        $systemLog->action = $action;
        $systemLog->old_value = !empty($oldValues) ? json_encode($oldValues) : null;
        $systemLog->new_value = !empty($newValues) ? json_encode($newValues) : null;
        $systemLog->ip_address = request()->ip();
        $systemLog->save();
    }
}
