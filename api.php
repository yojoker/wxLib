<?php
define('APPID', 'wxf88e4e16e3ce365e');//微信公众号的APPID
define('APPKEY', '6cba2fe7d5b326a520bac79015e6a4df');

class Api
{
    /**
     * 获取access_token（注：其他接口调用凭证）
     * @return string
     */
    public function getToken()
    {
        $apiData = array(
            'appid'=> APPID,
            'secret'=> APPKEY
        );
        $api = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&".http_build_query($apiData);
        $data = $this->httpRequest($api);
        return $data['access_token'];
    }

    /**
     * PHP发送请求
     * @param  string $api       接口地址
     * @param  array  $postData  POST请求数据
     * @return array
     */
    public function httpRequest($api, $postData = array())
    {
        //1.初始化
        $ch = curl_init();
        //2.配置
            //2.1设置请求地址
            curl_setopt($ch, CURLOPT_URL, $api);
            //2.2数据流不直接输出
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            //2.3POST请求
            if ($postData) {
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
            }
            //curl注意事项，如果发送的请求是https，必须要禁止服务器端校检SSL证书
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        //3.发送请求
        $data = curl_exec($ch);
        //4.释放资源
        curl_close($ch);
        return json_decode($data, true);
    }

    /**
     * 获取素材ID
     * @param  string $filepath  素材路径
     * @param  string  $type     类型
     * 图片（image）、语音（voice）、视频（video）和缩略图（thumb）
     * @return string
     */
    public function getMediaId($filepath, $type = 'image') 
    {
        #步骤1：定义接口
        $apiData = array(
            'access_token'=> $this->getToken(),
            'type'=> $type
        );
        $api = "https://api.weixin.qq.com/cgi-bin/media/upload?".http_build_query($apiData);
        #步骤2：设置POST提交数据
        //$filepath = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'luoli.jpg';
        if(version_compare(PHP_VERSION,'5.5.0', '<')) {  
            //PHP5.5版本以下
            $postData = array('media' => '@' . $filepath);  
        }else{  
            //PHP5.5版本以上
            $postData = array('media' => new Curlfile($filepath));  
        }  
        #步骤3：发送请求
        $data = $this->httpRequest($api, $postData);
        return $data['media_id'];
    }

    /*
     * 响应图文消息
     * @param string $toUserName   接受者（原发送者）
     * @param string $fromUserName 发送者（原接受者）
     * @param string $news      图文数据（格式：[['title','desc','img','url'],....]）
     */
    public function sendNews($toUserName, $fromUserName, $news)
    {
        $tpl ="<xml>
            <ToUserName><![CDATA[%s]]></ToUserName>
            <FromUserName><![CDATA[%s]]></FromUserName>
            <CreateTime>%s</CreateTime>
            <MsgType><![CDATA[news]]></MsgType>
            <ArticleCount>%s</ArticleCount>
            <Articles>";

        foreach ($news as $new) {
            $tpl .= "<item>
                <Title><![CDATA[{$new['title']}]]></Title>
                <Description><![CDATA[{$new['desc']}]]></Description>
                <PicUrl><![CDATA[{$new['img']}]]></PicUrl>
                <Url><![CDATA[{$new['url']}]]></Url>
            </item>";
        }
                
        $tpl .= "</Articles>
        </xml>";
        echo sprintf($tpl, $toUserName, $fromUserName, time(), count($news));
        die;
    }

    /*
     * 响应音乐消息
     * @param string $toUserName   接受者（原发送者）
     * @param string $fromUserName 发送者（原接受者）
     * @param string $title        标题
     * @param string $desc         描述
     * @param string $url          音乐地址
     * @param string $mediaid      缩略图媒体ID（注：得通过第三方接口获取）
     */
   public function sendMusic($toUserName, $fromUserName, $title, $desc, $url,  $mediaid)
    {
        $tpl ="<xml>
            <ToUserName><![CDATA[%s]]></ToUserName>
            <FromUserName><![CDATA[%s]]></FromUserName>
            <CreateTime>%s</CreateTime>
            <MsgType><![CDATA[music]]></MsgType>
            <Music>
                <Title><![CDATA[%s]]></Title>
                <Description><![CDATA[%s]]></Description>
                <MusicUrl><![CDATA[%s]]></MusicUrl>
                <HQMusicUrl><![CDATA[%s]]></HQMusicUrl>
                <ThumbMediaId><![CDATA[%s]]></ThumbMediaId>
            </Music>
        </xml>";
        echo sprintf($tpl, $toUserName, $fromUserName, time(), $title, $desc, $url, $url, $mediaid);
        die;
    }

    /*
     * 响应视频消息
     * @param string $toUserName   接受者（原发送者）
     * @param string $fromUserName 发送者（原接受者）
     * @param string $mediaid      媒体ID（注：得通过第三方接口获取）
     * @param string $title        标题
     * @param string $desc         描述
     */
    public  function sendVideo($toUserName, $fromUserName, $mediaid, $title, $desc)
    {
        $tpl ="<xml>
            <ToUserName><![CDATA[%s]]></ToUserName>
            <FromUserName><![CDATA[%s]]></FromUserName>
            <CreateTime>%s</CreateTime>
            <MsgType><![CDATA[video]]></MsgType>
            <Video>
                <MediaId><![CDATA[%s]]></MediaId>
                <Title><![CDATA[%s]]></Title>
                <Description><![CDATA[%s]]></Description>
            </Video>
        </xml>";
        echo sprintf($tpl, $toUserName, $fromUserName, time(), $mediaid, $title, $desc);
        die;
    }

    /*
     * 响应图片消息
     * @param string $toUserName   接受者（原发送者）
     * @param string $fromUserName 发送者（原接受者）
     * @param string $mediaid      媒体ID（注：得通过第三方接口获取）
     */
    public function sendImg($toUserName, $fromUserName, $mediaid)
    {
        $tpl ="<xml>
            <ToUserName><![CDATA[%s]]></ToUserName>
            <FromUserName><![CDATA[%s]]></FromUserName>
            <CreateTime>%s</CreateTime>
            <MsgType><![CDATA[image]]></MsgType>
            <Image>
                <MediaId><![CDATA[%s]]></MediaId>
            </Image>
        </xml>";
        echo sprintf($tpl, $toUserName, $fromUserName, time(), $mediaid);
        die;
    }

    /*
     * 响应文本消息
     * @param string $toUserName   接受者（原发送者）
     * @param string $fromUserName 发送者（原接受者）
     * @param string $content      内容
     */
    public function sendText($toUserName, $fromUserName, $content)
    {
        $tpl ="<xml>
            <ToUserName><![CDATA[%s]]></ToUserName>
            <FromUserName><![CDATA[%s]]></FromUserName>
            <CreateTime>%s</CreateTime>
            <MsgType><![CDATA[text]]></MsgType>
            <Content><![CDATA[%s]]></Content>
        </xml>";
        echo sprintf($tpl, $toUserName, $fromUserName, time(), $content);
        die;
    }
    /**
     * 传递经度和纬度获取对应城市的天气
     * @param string $x 纬度
     * @param string $y 经度
     */
    public function getWeather($x, $y) 
    {
        //1.定义获取城市接口
        $apiData = array(
            //'location'=>'31.034795,121.611812',
            'location'=> $x . ',' . $y,
            'get_poi'=>1,
            'key'=>'VJLBZ-KSCCR-6ILWP-WVXQL-IAVE7-LIBNH'
        );
        $api = "http://apis.map.qq.com/ws/geocoder/v1/?".http_build_query($apiData);
        //2.获取接口数据
        $data = json_decode(file_get_contents($api), true);
        //3.获取城市
        //echo $data['result']['address_component']['city'];
        $cityName = $data['result']['address_component']['city'];


        //1.定义获取城市天气接口
        $apiData = array(
            //'location'=>'上海',
            'location'=>$cityName,
            'output'=>'json',
            'ak'=>'tExQBV8stPQTOWhPGBfpYO9dGwG570w5'
        );
        $api = "http://api.map.baidu.com/telematics/v3/weather?".http_build_query($apiData);
        //2.获取接口数据
        $data = json_decode(file_get_contents($api), true);
        //3.获取城市天气数据
        return $data['results'][0]['weather_data'];
    }
}
