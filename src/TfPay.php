<?php


namespace Pdy\Tianfu;

use Symfony\Component\HttpFoundation\Request;

class TfPay
{
    use Common;

    //接口API URL前缀
    protected $API_URL_PREFIX = 'https://tfpay.tf.cn:';

    //预下单地址URL
    const UNIFIEDORDER_URL = "/tianfupay/sdk/reservateOrder";

    //聚合v3
    const UNIFIEDORDERV3_URL = "/tianfupay/pay/frontPayV3";

    //查询订单URL
    const ORDERQUERY_URL = "/tianfupay/query/queryOrder";

    //关闭订单URL
    const CLOSEORDER_URL = "/tianfupay/trans/closeOrder";

    //接口名称 reservate_service
    private $service;

    //订单生成的机器IP，指用户浏览器端IP不是商户服务器IP
    private $spbill_create_ip;

    protected $config;

    //所有参数
    private $params = array();


    public function __construct($config = null)
    {
        $this->config = config('tfpay');
        if ($config) {
            $this->config = $config;
        }
        $this->resolveBaseUri();
        $this->SetSubPartner();
    }

    /**
     * resolve baseUri.
     */
    public function resolveBaseUri()
    {
        $this->params['service_version'] = '1.0';
        $this->params['input_charset'] = 'UTF-8';
        $this->params['partner'] = $this->config['partner'];
        $this->params['sign_type'] = 'MD5';
        if ($this->config['test']) {
            $this->API_URL_PREFIX = 'https://tfpay.etest.tf.cn';
        }
    }


    protected function SetSubPartner()
    {
        if ($this->config['test']) {
            $this->params['subpartner'] = $this->config['subpartner'];
        }
    }

    /**
     *下单
     */

    public function unifiedOrder($params)
    {
        $this->service = 'pay_service';
        $this->spbill_create_ip = request()->ip();
        $this->params['service'] = $this->service;
        $this->params['out_trade_no'] = $params['out_trade_no'];
        $this->params['subject'] = $params['subject'];
        $this->params['body'] = $params['body'];
        $this->params['total_fee'] = $params['total_fee'];
        $this->params['fee_type'] = $this->config['fee_type'];
        $this->params['spbill_create_ip'] = $this->spbill_create_ip;
        $this->params['notify_url'] = $this->config['notify_url'];
        $this->params['trans_channel'] = $params['trans_channel'];
        $this->params['channel_details'] = $params['channel_details'];
        $this->params['sign'] = $this->sign2($this->params);
        $result = $this->http_post($this->API_URL_PREFIX . self::UNIFIEDORDERV3_URL, json_encode($this->params));
        return $result;

    }

    /**
     * 查询订单
     */
    public function queryOrder($params)
    {
        $this->service = 'query_order_service';
        $this->params['service'] = $this->service;
        $this->params['out_trade_no'] = $params['out_trade_no'];
        $this->params['sign'] = $this->sign2($this->params);
        $result = $this->http_post($this->API_URL_PREFIX . self::ORDERQUERY_URL, json_encode($this->params, JSON_UNESCAPED_UNICODE));
        return $result;
    }

    /**
     * 关闭订单
     */
    public function closeOrder($params)
    {
        $this->service = 'close_order';
        $this->params['service'] = $this->service;
        $this->params['out_trade_no'] = $params['out_trade_no'];
        $this->params['sign'] = $this->sign2($this->params);
        $result = $this->http_post($this->API_URL_PREFIX . self::CLOSEORDER_URL, json_encode($this->params, JSON_UNESCAPED_UNICODE));
        return $result;
    }

    /**
     * 预下单
     * @param $params
     * @return bool|string
     */
    public function reservateOrder($params)
    {
        $this->service = 'reservate_service';
        $this->spbill_create_ip = request()->ip();
        $this->params['service'] = $this->service;
        $this->params['out_trade_no'] = $params['out_trade_no'];
        $this->params['subject'] = $params['subject'];
        $this->params['body'] = $params['body'];
        $this->params['total_fee'] = $params['total_fee'];
        $this->params['show_url'] = $this->config['show_url'];
        $this->params['fee_type'] = $this->config['fee_type'];
        $this->params['spbill_create_ip'] = $this->spbill_create_ip;
        $this->params['notify_url'] = $this->config['notify_url'];
        $this->params['return_url'] = $this->config['notify_url'];
        $this->params['outmemberno'] = (string)$params['user_id'];
        $this->params['sign'] = $this->sign2($this->params);
        $result = $this->http_post($this->API_URL_PREFIX . self::UNIFIEDORDER_URL, json_encode($this->params));
        return $result;
    }

    /**
     * SDK 收银台
     * @param $result
     * @param $orderModel
     */
    public function SecondarySignature($result, $orderModel)
    {
        $pay = [
            'sign_type' => $result['sign_type'],
            'partnerno' => $result['partner'],
            'outmemberno' => (string)$orderModel->user_id,
            'transaction_id' => $result['transaction_id'],
            'sub_appid' => $result['sub_appid'],
            'channel_details' => $result['channel_details'],
        ];
        $pay['sign'] = $this->sign2($pay);
        return $pay;
    }

    /**
     * SDK 收银台
     * @param $result
     * @param $orderModel
     */
    public function SecondaryAliSignature($result, $orderModel)
    {
        $pay = [
            'sign_type' => $result['sign_type'],
            'partnerno' => $result['partner'],
            'outmemberno' => (string)$orderModel->user_id,
            'transaction_id' => $result['transaction_id'],
            'channel_details' => $result['channel_details'],
        ];
        $pay['sign'] = $this->sign2($pay);
        return $pay;
    }

    /**
     * 二次签名779
     * @param array $arr
     * @return string
     */
    public function sign2(array $arr)
    {
        $string = $this->getSignContent($arr) . $this->config['key'];
        $string = str_replace('https=', 'https:', $string);
        $string = str_replace('http=', 'http:', $string);
        return md5($string);
    }

    /**
     * 验证签名
     * @param null $data
     * @return bool
     */
    public function verify($data = null)
    {
        if (is_null($data)) {
            $request = Request::createFromGlobals();

            $data = $request->request->count() > 0 ? $request->request->all() : $request->query->all();
        }
        if (md5(self::getSignContent($data) . $this->config['key']) === $data['sign']) {
            return true;
        }
        return false;
    }
}