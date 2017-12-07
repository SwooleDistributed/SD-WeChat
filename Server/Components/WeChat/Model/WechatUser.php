<?php
/**
 * Created by VSCODE.
 * User: Xavier
 * Date: 17-12-7
 * Time: 上午9:56
 */

namespace Server\Components\WeChat;

class WeChatUser extends WeChatBaseModel
{
    /**
     * 修改用户备注
     * @param $openid
     * @param $remark
     * @return object
     * @throws \Exception
     */
    public function remark(string $openid, string $remark)
    {
        $response = yield $this->WenXinHttpClient->httpClient->setQuery([
            'openid' => $openid,
            'remark' => $remark,
        ])->coroutineExecute('/cgi-bin/user/info/updateremark');
        $json = $response['body'];
        
        return  json_decode($json, true);
    }
    /**
     * 通过用户ID查找用户
     *
     * @param string $openid
     * @param string $lang
     *
     * @return object
     */
    public function get(string $openid, string $lang = 'zh_CN')
    {
        $response = yield $this->WenXinHttpClient->httpClient->setQuery([
            'openid' => $openid,
            'lang' => $lang,
        ])->coroutineExecute('/cgi-bin/user/info');
        $json = $response['body'];
        return  json_decode($json, true);
    }
}