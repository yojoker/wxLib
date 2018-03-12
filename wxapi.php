<?php
require './api.php';

class wechat extends Api
{
    public function valid() 
    {
        //随机字符串
        $echoStr = $_GET["echostr"];

        //校验token
        //if($this->checkSignature()){
            echo $echoStr;
            exit;
        //}
    }
    public function responseMsg()
    {
        //与$_POST功能类似，主要用于接受HTTP请求中的POST数据
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];

        //判断数据是否为空
        if (!empty($postStr)) 
        {
            //安全处理：防止XXE漏洞
            libxml_disable_entity_loader(true);
            //将字符串划分为对象
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $fromUsername = $postObj->FromUserName; //发送者唯一标识
            $toUsername = $postObj->ToUserName;     //接受者唯一标识
            $keyword = trim($postObj->Content);     //发送的内容
            $time = time();
            switch($postObj->MsgType ) {
                case 'location':
                    //传递经度、纬度获取城市天气
                    $weatherData = $this->getWeather($postObj->Location_X, $postObj->Location_Y);
                    //$weatherData = $this->getWeather('39.903226', '116.397716');
                    //声明图文消息数据
                   $articleData[] = array('title' => '天气预报', 'desc'=>'', 'img'=>'', 'url'=>'');
                    //遍历城市天气数据，组装图文消息数据
                   foreach ($weatherData as $weather) {

                        $articleData[] =  array(
                        'title' => $weather['date'] . $weather['weather'] . $weather['wind'] . $weather['temperature'], 
                        'desc' => '', 
                        'img' => $weather['dayPictureUrl'], 
                        'url' => ''
                        );
                    }
                    //响应图文消息
                    $this->sendNews($fromUsername, $toUsername, $articleData);


                    //$content = '您发送的是地理位置消息，经度：'.$postObj->Location_Y.'，纬度：'.$postObj->Location_X;
                    //$this->sendText($fromUsername, $toUsername, $content);
                    break;
                default:
                    $this->sendText($fromUsername, $toUsername, '你有瑕疵，请尽快联系传智PHP学院帮你修复');
            }



            // $articleData = array(
            //     array('title' => '美丽的家乡', 'desc' => '描述', 'img' => 'http://118.31.9.103/img/b1.jpg', 'url' => ''),
            //     array('title' => '标题1', 'desc' => '描述1', 'img' => 'http://118.31.9.103/img/one.png', 'url' => ''),
            //     array('title' => '标题2', 'desc' => '描述2', 'img' => 'http://118.31.9.103/img/two.png', 'url' => ''),
            //     array('title' => '标题3', 'desc' => '描述3', 'img' => 'http://118.31.9.103/img/three.png', 'url' => ''),
            //     array('title' => '标题4', 'desc' => '描述4', 'img' => 'http://118.31.9.103/img/four.png', 'url' => '')
            // );

            //响应图文消息
            // $this->sendNews($fromUsername, $toUsername, $articleData);

            //响应文字消息
            //$this->sendText($fromUsername, $toUsername, "传智播客上海PHP学院1");

            //响应音乐消息
            //$musicUrl = 'http://118.31.9.103/红日.mp3';
            //$mediaid = 'JVQXJiG-e6RC0bi9Ld54vVo9QZFj9UstHlhllvEyQn_5S5ZeXkvNnuBzXL4faVaF';
            //$this->sendMusic($fromUsername, $toUsername, $keyword, '音乐描述', $musicUrl, $musicUrl, $mediaid);

        }else {
            echo "";
            exit;
        }
    }	
}	


//创建微信对象
$wechatObj = new wechat;
//验证身份
//$wechatObj->valid();
//响应请求消息
$wechatObj->responseMsg();
