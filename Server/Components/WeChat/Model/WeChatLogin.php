<?php
/**
 * Created by PhpStorm.
 * User: zhangjincheng
 * Date: 17-12-6
 * Time: 上午11:12
 */

namespace Server\Components\WeChat;
use Server\CoreBase\HttpInput;
use Server\CoreBase\HttpOutput;

class WeChatLogin extends WeChatBaseModel
{
    /**
     * 登录
     * @param $code
     * @return mixed
     * @throws \Exception
     */
    public function login($code)
    {
        $response = yield $this->WenXinHttpClient->httpClient->setQuery([
            'appid' => $this->config->get('wechat_appid'),
            'secret' => $this->config->get('wechat_appsecret'),
            'code' => $code,
            'grant_type' => 'authorization_code'
        ])->coroutineExecute('/sns/oauth2/access_token');
        $json = $response['body'];
        $info = json_decode($json, true);
        if (array_key_exists('errcode', $info)) {
            throw new WeChatException('微信登录失败'.$info['errmsg']);
        }

        //可以拉取用户信息了
        $response = yield $this->WenXinHttpClient->httpClient->setQuery([
            'access_token' => $info['access_token'],
            'openid' => $info['openid'],
            'lang' => 'zh_CH'
        ])->coroutineExecute('/sns/userinfo');
        $json = $response['body'];

        $wuser_info = json_decode($json, true);
        if (array_key_exists('errcode', $wuser_info)) {
            throw new WeChatException('微信登录失败'.$wuser_info['errmsg']);
        }

        return $wuser_info;
    }
    /**
     * 获取code
     * @param $request
     * @param $scope
     * @return string
     * @throws \Exception
     */
    public function auth(HttpInput &$request,HttpOutput &$http_output,$scope='snsapi_userinfo'){
        if (!$request->get('code')){

            $appid = $this->config->get('wechat_appid');
            $redirect_uri = $request->getRequestUri();
            $url="https://open.weixin.qq.com/connect/oauth2/authorize?appid={$appid}&redirect_uri={$redirect_uri}&response_type=code&scope={$scope}";

            $http_output->setStatusHeader(302);
            $http_output->setHeader('Location', $url);
            $http_output->end('end');
        }
        
        return $request->get('code');
    }
}