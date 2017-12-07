<?php
/**
 * Created by PhpStorm.
 * User: zhangjincheng
 * Date: 17-12-6
 * Time: 上午11:12
 */

namespace Server\Components\WeChat;

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

    public function getcode(){
        $appid = $this->config->get('wechat_appid');
        return $url;
    }
}