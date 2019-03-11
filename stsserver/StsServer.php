<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/11 0011
 * Time: 上午 10:36
 */

namespace stsserver;

include_once 'aliyun-php-sdk-core/Config.php';

use DefaultAcsClient;
use DefaultProfile;
use Sts\Request\V20150401 as Sts;
use yii\base\Component;



class StsServer extends Component
{
    public $accessKeyID = '';
    
    public $accessKeySecret = '';
    
    public $roleArn = '';
    
    public $tokenExpire = 900;
    
    public $policyName = '';
    
    public $bucket = '';
    
    
    private $policy = '';
    
    public function init(){
        parent::init();
        if (!$this->accessKeyID) {
            throw new \Exception('accessKeyID is empty');
        }
        if (!$this->accessKeySecret) {
            throw new \Exception('accessKeySecret is empty');
        }
    
        if (!$this->roleArn) {
            throw new \Exception('roleArn is empty');
        }
    
        if (!$this->bucket) {
            throw new \Exception('bucket is empty');
        }
        
        if(!file_exists($this->policyName)){
            throw new \Exception('policyName is no exist');
        }else{
            $this->policy = read_file($this->policyName);
        }
        
//        if (!$this->endPoint) {
//            throw new \Exception('endPoint is empty');
//        }
    }
    
    public function requestData(){
        $iClientProfile = DefaultProfile::getProfile("cn-hangzhou", $this->accessKeyID, $this->accessKeySecret);
        $client = new DefaultAcsClient($iClientProfile);
    
        $request = new Sts\AssumeRoleRequest();
        $request->setRoleSessionName("client_name");
        $request->setRoleArn($this->roleArn);
        $request->setPolicy($this->policy);
        $request->setDurationSeconds($this->tokenExpire);
        $response = $client->doAction($request);
    
        $rows = array();
        $body = $response->getBody();
        $content = json_decode($body);
        if ($response->getStatus() == 200)
        {
            $rows['StatusCode'] = 200;
            $rows['AccessKeyId'] = $content->Credentials->AccessKeyId;
            $rows['AccessKeySecret'] = $content->Credentials->AccessKeySecret;
            $rows['Expiration'] = $content->Credentials->Expiration;
            $rows['SecurityToken'] = $content->Credentials->SecurityToken;
        }
        else
        {
            $rows['StatusCode'] = 500;
            $rows['ErrorCode'] = $content->Code;
            $rows['ErrorMessage'] = $content->Message;
        }
        
        return $rows;
    }
}