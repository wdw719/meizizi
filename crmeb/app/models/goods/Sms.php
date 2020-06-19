<?php
namespace app\models\goods;

use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Exception\ServerException;
use crmeb\basic\BaseModel;

class Sms extends BaseModel{

    /**
     * 发送短信
     */
    public function sendSms($code , $phone , $type){
        if($type == 1){ //登陆
            $TemplateCode = "SMS_193130610";
        }else{  //注册
            $TemplateCode = "SMS_193130608";
        }
        $code = json_encode(array('code'=>$code));
        AlibabaCloud::accessKeyClient('LTAI4G29BjpEQhANw7HYsuQL' , 'JEJL1nEjFNU1dr7vr4UZCGF5Sig4EL') -> regionId('cn-hangzhou') -> asDefaultClient();
        try {
            $result = AlibabaCloud::rpc()
                ->product('Dysmsapi')
                ->version('2017-05-25')
                ->action('SendSms')
                ->method('POST')
                ->host('dysmsapi.aliyuncs.com')
                ->options([
                    'query' => [
                        'RegionId' => "cn-hangzhou",
                        'PhoneNumbers' => $phone ,
                        'SignName' => "美孜孜",
                        'TemplateCode' => $TemplateCode,
                        'TemplateParam' => $code,
                    ],
                ])
                ->request();
            return $result;
        } catch (ClientException $e) {
        } catch (ServerException $e) {
        }
    }
}
