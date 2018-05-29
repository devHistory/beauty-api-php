<?php

/**
 * AliYun访问控制
 * @link https://help.aliyun.com/document_detail/31867.html
 */

namespace App\Http\Controllers\V1;


use App\Providers\Components\FilterTrait;
use OSS\OssClient;
use Sts\Request\V20150401 as Sts;
use Zend\Math\Rand;


class FilesController extends ControllerBase
{

    use FilterTrait;


    private $default_region;


    private $timeout = 3600;


    public function beforeExecuteRoute()
    {
        parent::beforeExecuteRoute();

        // set default region
        if (isset($this->config['aliyun']['region'])) {
            $this->default_region = $this->config['aliyun']['region'];
        }
        else {
            $this->default_region = 'shanghai';
        }
    }


    public function accessAction()
    {
        $type = $this->request->get('type');
        if ($type == 'sts') {
            $response = $this->aliYunSTS();
        }
        else {
            $response = $this->aliYunSignUrl();
        }
        return $this->response->setJsonContent($response);
    }


    /**
     * aliyun-openapi-php-sdk   [STS鉴权模式]
     * @link https://help.aliyun.com/document_detail/28791.html
     * @link https://github.com/aliyun/aliyun-openapi-php-sdk
     */
    private function aliYunSTS()
    {
        define("REGION_ID", "cn-" . $this->default_region);
        define("ENDPOINT", "sts.cn-{$this->default_region}.aliyuncs.com");
        define("ACCESS_ID", $this->config['aliyun']['accessId']);
        define("ACCESS_KEY", $this->config['aliyun']['accessKey']);
        define("ROLE_ARN", $this->config['aliyun']['roleArn']);

        $ip = $this->request->getClientAddress();
        $object = $this->generatePath();
        $bucket = $this->config['aliyun']['bucket'];


        // @link https://help.aliyun.com/document_detail/31867.html
        // TODO :: 注意权限问题
        $policy = <<<POLICY
{
  "Statement": [
    {
      "Action": [
        "oss:GetObject",
        "oss:PutObject",
        "oss:DeleteObject"
      ],
      "Effect": "Allow",
      "Resource": [
        "acs:oss:*:*:{$bucket}/{$object}"
      ],
      "Condition": {
        "IpAddress": {
            "acs:SourceIp": "{$ip}"
        }
      }
    }
  ],
  "Version": "1"
}
POLICY;


        include_once APP_DIR . '/providers/Sdk/aliyun-php-sdk-core/Config.php';

        // 只允许子用户使用角色
        \DefaultProfile::addEndpoint(REGION_ID, REGION_ID, "Sts", ENDPOINT);
        $iClientProfile = \DefaultProfile::getProfile(REGION_ID, ACCESS_ID, ACCESS_KEY);
        $client = new \DefaultAcsClient($iClientProfile);

        // RoleSessionName  临时身份的会话名称，用于区分不同的临时身份, 可以使用客户的ID作为会话名称
        // RoleArn          角色资源描述符, 在RAM的控制台的资源详情页上可以获取
        $request = new Sts\AssumeRoleRequest();
        $request->setRoleSessionName('uid-' . $this->uid);
        $request->setRoleArn(ROLE_ARN);
        $request->setPolicy($policy);
        $request->setDurationSeconds($this->timeout);
        try {
            $response = $client->getAcsResponse($request);
            return [
                'code'    => 200,
                'message' => 'success',
                'payload' => [
                    'object'          => $object,
                    'accessKeyId'     => $response->Credentials->AccessKeyId,
                    'accessKeySecret' => $response->Credentials->AccessKeySecret,
                    'expiration'      => $response->Credentials->Expiration,
                    'securityToken'   => $response->Credentials->SecurityToken,
                ]
            ];
        } catch (\ServerException $e) {
        } catch (\ClientException $e) {
        }
        return [
            'code'    => 400,
            'message' => $e->getMessage()
        ];
    }


    /**
     * aliyun-oss-php-sdk       [url签名模式]
     * @link https://help.aliyun.com/document_detail/32106.html
     */
    private function aliYunSignUrl()
    {
        $object = $this->generatePath();
        $endpoint = "https://oss-cn-{$this->default_region}.aliyuncs.com";
        $ossClient = new OssClient(
            $this->config['aliyun']['accessId'], $this->config['aliyun']['accessKey'], $endpoint, false, null
        );
        $signedUrl = $ossClient->signUrl($this->config['aliyun']['bucket'], $object, $this->timeout);
        return [
            'code'    => 200,
            'message' => 'success',
            'payload' => ['url' => $signedUrl]
        ];
    }


    private function generatePath($prefix = 'album')
    {
        $charList = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $random = Rand::getString(24, $charList);
        return $prefix . date('/Ym/') . $random . '.png';
    }

}
