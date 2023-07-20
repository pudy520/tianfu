<?php


namespace Pdy\Tianfu;


trait Common
{
    /**
     * CURL POST请求
     * @param $url
     * @param $value
     * @return bool|string
     */
    public static function http_post($url, $value)
    {
        $header = array(
            'Content-Type: application/json'
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        // 设置请求头
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $value);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $info = curl_exec($ch);
        if (curl_errno($ch)) {
            return 'Errno' . curl_error($ch);
        }
        curl_close($ch);
        return $info;
    }


    /**
     * @param $data
     * @return string
     */
    public static function getSignContent($data)
    {
        ksort($data);
        $buff = '';

        foreach ($data as $k => $v) {
            $buff .= (('sign' != $k && '' != $v && !is_array($v)) && ('sign_type' != $k && '' != $v && !is_array($v))) ? $k . '=' . $v . '&' : '';
        }
        $string = preg_replace('/(\")/', '', trim($buff, '&'));
        return str_replace(':', '=', $string);
    }
}