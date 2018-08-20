<?php
/**
 * Created by PhpStorm.
 * User: 朱江
* Date: 2017/2/12
* Time: 19:44
*/

namespace app\lib\exception;

use think\exception\Handle;
use think\Log;
use think\Request;
use Exception;

/*
 * 重写Handle的render方法，实现自定义异常消息
 */
class ExceptionHandler extends Handle
{
    private $code;
    private $msg;
    private $errorCode;

    public function render(\Exception $e)
     {
        if ($e instanceof BaseException)
        {
            //如果是自定义异常，则控制http状态码，不需要记录日志
            $this->code = $e->code;
            $this->msg = $e->msg;
            $this->errorCode = $e->errorCode;
        }
        else{
            // 如果是服务器未处理的异常，将http状态码设置为500，并记录日志
            if(config('app_debug')){
                //调试状态用自带的
                return parent::render($e);
            }
            $this->code = 500;
            $this->msg = 'sorry，we were wrong. (^o^)§';
            $this->errorCode = 999;
//            var_dump($e->getMessage());die;
            $this->recordErrorLog($e);//记录日志
        }

        $request = Request::instance();
        $result = [
            'msg'  => $this->msg,
            'error_code' => $this->errorCode,
            'request_url' => $request = $request->url()
        ];
        return json($result, $this->code);
    }

    /*
     * 将异常写入日志
     */
    private function recordErrorLog(Exception $e)
    {
        Log::init([
            'type'  =>  'File',
            'path'  =>  LOG_PATH,
            'level' => ['error']
        ]);
//        Log::record($e->getTraceAsString());
        Log::record($e->getMessage(),'error');
    }
}