<?php
/**
 * Created by PhpStorm.
 * User: zhangjincheng
 * Date: 17-12-6
 * Time: 上午11:49
 */

namespace Server\Components\WeChat;

class WeChatOrder extends WeChatBaseModel
{
    /**
     * 统一下单方法
     * @param $order_id 自定义的订单号
     * @param $openid 微信的openid
     * @param $money 订单金额 只能为整数 单位为分
     * @param $ip 用户端实际ip
     * @param $body 名称
     * @return bool|mixed
     */
    public function unifiedOrder($order_id,$openid,$money,$ip,$body)
    {
        $nonce_str = $this->getRandStr(30);
        $params['out_trade_no'] = $order_id;                                 //自定义的订单号
        $params['total_fee'] = $money;                                       //订单金额 只能为整数 单位为分
        $params['spbill_create_ip'] = $ip;
        $params['appid'] = $this->wechat_appid;
        $params['openid'] = $openid;
        $params['mch_id'] = $this->wechat_partner;
        $params['nonce_str'] = $nonce_str;
        $params['trade_type'] = 'JSAPI';
        $params['notify_url'] = $this->wechat_callback;
        $params['body'] = $body;
        //获取签名数据
        $sign = $this->makeSign($this->wechat_appkey,$params);
        $params['sign'] = $sign;
        $xml = $this->data_to_xml($params);

        $response = yield $this->postXmlCurl($xml);
        if (!$response) {
            return false;
        }
        $result = $this->xml_to_data($response);
        if (!empty($result['result_code']) && !empty($result['err_code'])) {
            $result['err_msg'] = $this->error_code($result['err_code']);
        }
        return $result;
    }

    /**
     * 支付的回调
     * @param $post_arr
     * @return array|bool
     */
    public function pay_callback($post_arr)
    {
        $data = (array)simplexml_load_string($post_arr, 'SimpleXMLElement', LIBXML_NOCDATA);
        $return_code = $data['return_code'];//成功标识
        $sign = $data['sign'];//签名

        //验证是否支付成功
        if (empty($return_code) || strtoupper($return_code) != 'SUCCESS') {
            return false;
        }
        if ($this->wx_sign($sign, $data)) {//签名验证成功
            return $data;
        } else {
            return false;
        }
    }

    /**
     * post xml 到微信请求统一下单接口
     * @param $xml
     * @return mixed
     */
    private function postXmlCurl($xml)
    {
        $xmlData = $xml;
        $response = yield $this->Wechat_HttpClient->httpClient->setData($xmlData)
            ->setHeaders(['Content-type' => 'application/xml'])
            ->setMethod('POST')->coroutineExecute($this->wechat_unifiedorder);

        return $response['body'];
    }

    /**
     * 错误代码
     * @param $code
     * @return mixed
     */
    private function error_code($code)
    {
        $errList = array(
            'NOAUTH' => '商户未开通此接口权限',
            'NOTENOUGH' => '用户帐号余额不足',
            'ORDERNOTEXIST' => '订单号不存在',
            'ORDERPAID' => '商户订单已支付，无需重复操作',
            'ORDERCLOSED' => '当前订单已关闭，无法支付',
            'SYSTEMERROR' => '系统错误!系统超时',
            'APPID_NOT_EXIST' => '参数中缺少APPID',
            'MCHID_NOT_EXIST' => '参数中缺少MCHID',
            'APPID_MCHID_NOT_MATCH' => 'appid和mch_id不匹配',
            'LACK_PARAMS' => '缺少必要的请求参数',
            'OUT_TRADE_NO_USED' => '同一笔交易不能多次提交',
            'SIGNERROR' => '参数签名结果不正确',
            'XML_FORMAT_ERROR' => 'XML格式错误',
            'REQUIRE_POST_METHOD' => '未使用post传递参数 ',
            'POST_DATA_EMPTY' => 'post数据不能为空',
            'NOT_UTF8' => '未使用指定编码格式',
        );
        if (array_key_exists($code, $errList)) {
            return $errList[$code];
        }
    }
}