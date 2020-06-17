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
    public function sendSms(){
        AlibabaCloud::accessKeyClient('<accessKeyId>', '<accessSecret>')
            ->regionId('cn-hangzhou')
            ->asDefaultClient();
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
                        'PhoneNumbers' => "18080498101",
                        'SignName' => "	 美孜孜",
                        'TemplateCode' => "SMS_193130608",
                        'TemplateParam' => "1520",
                    ],
                ])
                ->request();
            print_r($result->toArray());
        } catch (ClientException $e) {
            echo $e->getErrorMessage() . PHP_EOL;
        } catch (ServerException $e) {
            echo $e->getErrorMessage() . PHP_EOL;
        }
    }
}
