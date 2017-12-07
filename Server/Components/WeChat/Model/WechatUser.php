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



    /**
     * 批量获取用户
     *
     * @param array  $openids
     * @param string $lang
     *
     * @return object
     */
    public function select(array $openids, string $lang = 'zh_CN')
    {
        $response = yield $this->WenXinHttpClient->httpClient->setQuery([
            'user_list' => array_map(function ($openid) use ($lang) {
                return [
                    'openid' => $openid,
                    'lang' => $lang,
                ];
            }, $openids),
        ])->coroutineExecute('/cgi-bin/user/info/batchget');
        $json = $response['body'];
        return  json_decode($json, true);
    }

    /**
     * 展示用户列表
     *
     * @param string $nextOpenId
     *
     * @return \Psr\Http\Message\ResponseInterface|\EasyWeChat\Kernel\Support\Collection|array|object|string
     */
    public function list(string $nextOpenId = null)
    {
        $response = yield $this->WenXinHttpClient->httpClient->setQuery(['next_openid' => $nextOpenId])->coroutineExecute('/cgi-bin/user/get');
        $json = $response['body'];
        return  json_decode($json, true);
    }


    /**
     * 获取黑名单
     *
     * @param string|null $beginOpenid
     *
     * @return object
     */
    public function blacklist(string $beginOpenid = null)
    {
        $response = yield $this->WenXinHttpClient->httpClient->setQuery(['begin_openid' => $beginOpenid])->coroutineExecute('/cgi-bin/tags/members/getblacklist');
        $json = $response['body'];
        return  json_decode($json, true);
    }

    /**
     * 批量获取黑名单
     *
     * @param array|string $openidList
     *
     * @return object
     */
    public function block($openidList)
    {
        $response = yield $this->WenXinHttpClient->httpClient->setQuery(['openid_list' => (array) $openidList])->coroutineExecute('/cgi-bin/tags/members/batchblacklist');
        $json = $response['body'];
        return  json_decode($json, true);
    }

    /**
     *批量解禁
     *
     * @param array $openidList
     *
     * @return object
     */
    public function unblock($openidList)
    {
        $response = yield $this->WenXinHttpClient->httpClient->setQuery(['openid_list' => (array) $openidList])->coroutineExecute('/cgi-bin/tags/members/batchunblacklist');
        $json = $response['body'];
        return  json_decode($json, true);
    }
}