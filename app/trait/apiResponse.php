<?php

namespace App\trait;

trait apiResponse
{
    public function getCurrentLang(){ // for return current language
        return app()->getLocale();
    }

    public function returnError($errNum , $msg)
    {
        return response()->json([
            'status' => false,
            'errNum' => $errNum,
            'msg' => $msg


        ]);
    }

        public function successMessage($errNum , $msg){
            return response()->json([
                'status'=>true,
                'errNum'=>$errNum,
                'msg'=>$msg


            ]);
    }
    public function returnData($key, $value , $msg){
        return response()->json([
            'status'=>true,
            'errNum'=>'0000',
            'msg'=>$msg,
            $key => $value


        ]);
    }
    public function returnValidationError($code = "E001", $validator)
    {
        return $this->returnError($code, $validator->errors()->first());
    }


    public function returnCodeAccordingToInput($validator)
    {
        $inputs = array_keys($validator->errors()->toArray());
        $code = $this->getErrorCode($inputs[0]);
        return $code;
    }

}
