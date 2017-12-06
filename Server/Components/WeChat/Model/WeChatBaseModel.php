<?php
/**
 * Created by PhpStorm.
 * User: zhangjincheng
 * Date: 17-12-6
 * Time: 上午11:07
 */
namespace Server\Components\WeChat;
use Server\Asyn\HttpClient\HttpClientPool;
use Server\CoreBase\Model;

class WeChatBaseModel extends Model
{
    protected $wechat_appkey;
    protected $wechat_appid;
    protected $wechat_partner;
    protected $wechat_callback;
    protected $wechat_url;
    protected $wechat_unifiedorder;
    /**
     * @var HttpClientPool
     */
    protected $Wechat_HttpClient;
    /**
     * @var HttpClientPool
     */
    protected $WenXinHttpClient;

    public function initialization(&$context)
    {
        parent::initialization($context);
        $this->wechat_appkey = $this->config['wechat_appkey'];                   //微信支付使用的key;
        $this->wechat_appid = $this->config['wechat_appid'];                    //微信支付id;
        $this->wechat_partner = $this->config['wechat_partner'];                  //微信支付使用的商户号;
        $this->wechat_callback = $this->config['wechat_callback'];                 //微信支付回调地址(正式);
        $this->wechat_url = $this->config['wechat_url'];                          //微信支付接口API URL前缀
        $this->wechat_unifiedorder = $this->config['wechat_unifiedorder'];             //微信支付接口API下单接口
        $this->Wechat_HttpClient = get_instance()->getAsynPool('Wechat_HttpClient');
        $this->WenXinHttpClient = get_instance()->getAsynPool('WeiXinAPI');
    }
    /**
     *生成APP端支付参数
     * @param $wechat_appid
     * @param $wechat_appkey
     * @param $prepayid
     * @return mixed
     */
    protected function getAppPayParams($wechat_appid,$wechat_appkey,$prepayid)
    {
        $data['appId'] = $wechat_appid;
        $data['package'] = "prepay_id=$prepayid";
        $data['nonceStr'] = $this->getRandStr(30);
        $data['timeStamp'] = time()."";
        $data['signType'] = 'MD5';
        $data['paySign'] = $this->makeSign($wechat_appkey,$data);
        return $data;
    }
    /**
     * 微信签名验证
     * @param $sign
     * @param $post_arr
     * @return bool
     */
    protected function wx_sign($sign,$post_arr){
        $app_key = $this->config['wechat_appkey'];
        $sign_true = '';
        ksort($post_arr);
        foreach($post_arr as $key=> $val){
            if(empty($val) || $key=='sign'){
                continue;
            }
            $sign_true .= $key."=".$val."&";
        }
        $sign_true .="key=".$app_key;
        $my_sign = strtoupper(md5($sign_true));
        if($sign==$my_sign){
            return true;
        }else{
            return false;
        }
    }
    /**
     * array转为xml格式
     * @param $params
     * @return bool|string
     */
    protected function data_to_xml($params)
    {
        if (!is_array($params) || count($params) <= 0) {
            return false;
        }
        $xml = "<xml>";
        foreach ($params as $key => $val) {
            if (is_numeric($val)) {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            } else {
                $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
            }
        }
        $xml .= "</xml>";
        return $xml;
    }

    /**
     * 将xml转为array
     * @param $xml
     * @return bool|mixed
     */
    protected function xml_to_data($xml)
    {
        if (!$xml) {
            return false;
        }
        //将XML转为array
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);

        return $data;
    }
    /**
     * 随机生成以dl开头的字符串  长度为length+2
     * @param $length
     * @return string
     */
    protected function getRandStr($length)
    {
        $str = "QWERTYUIOPASDFGHJKLZXCVBNM1234567890qwertyuiopasdfghjklzxcvbnm";
        str_shuffle($str);
        $rand_str = "dl" . substr(str_shuffle($str), 0, $length);
        return $rand_str;
    }

    /**
     * 生成签名
     * @param $wechat_appkey
     * @param $params
     * @return string
     */
    protected function makeSign($wechat_appkey,$params)
    {
        $app_key = $wechat_appkey;
        $sign_true = '';
        ksort($params);                             //签名步骤一：按字典序排序数组参数
        foreach ($params as $key => $val) {
            if (empty($val) || $key == 'sign') {
                continue;
            }
            $sign_true .= $key . "=" . $val . "&";
        }
        $sign_true .= "key=" . $app_key;            //签名步骤二：在string后加入KEY
        $my_sign = strtoupper(md5($sign_true));  //签名步骤三：MD5加密 //签名步骤四：所有字符转为大写
        return $my_sign;
    }
}