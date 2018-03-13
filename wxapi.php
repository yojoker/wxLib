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
		//同$_POST用来接收用户发送给腾讯服务器，腾讯服务器推送过来的消息
		$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        //将腾讯服务器推送的XML数据转化为对象
      	$postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
        //根据接口的XML数据-> 逻辑判断/业务需求判断 -> 响应XML数据给腾讯服务器（文本、图片、视频等）
        $toUserName  = $postObj->ToUserName;     //开发者微信号（原接受者）
        $fromUserName = $postObj->FromUserName;  //发送方帐号（原发送者）
        switch ($postObj->MsgType) {
                case 'voice':
                #$this->sendText($fromUserName, $toUserName, '语音内容为：'.$postObj->Recognition);
           
                $this->sendText($fromUserName, $toUserName,$this->liaotian($postObj->Recognition));
                break;

                case 'event':
                    if ($postObj->Event == 'subscribe') {
                        $this->sendText($fromUserName, $toUserName, '新用户，来自门店'.$postObj->EventKey);
                    }else if ($postObj->Event == 'SCAN') {
                        $this->sendText($fromUserName, $toUserName, '老用户，来自门店'.$postObj->EventKey);
                    }
                    break;
                case 'location':

                    // 步骤1：定义请求接口
                    $apiData = array(
                    'key'=>'b94b446f4ecad8b4f0e6cf758bacf915',
                    'location'=> $postObj->Location_Y . ',' . $postObj->Location_X,
                    'keywords'=>'如家',
                    'types'=>'',
                    'radius'=>'10000',
                    'offset'=>'20',
                    'page'=>'1',
                    'extensions'=>'all'
                    );
                    $api = "https://restapi.amap.com/v3/place/around?".http_build_query($apiData);
                    // 步骤2：发送请求
                    $data = json_decode(file_get_contents($api), true);

                    foreach ($data['pois'] as $pois) {
                        $temp[] = $pois['name'];
                        $temp[] = $pois['distance'];
                        $temp[] = $pois['address'];
                    }

                    #$msg = "您发送的是地理位置消息，经度：{$postObj->Location_Y}，纬度：{$postObj->Location_X}";
                    $this->sendText($fromUserName, $toUserName, implode(',', $temp));
                case 'text':
                    $content = $postObj->Content;
                    if (strpos('_'.$content, '翻译')) {
                        $this->sendText($fromUserName, $toUserName, $this->fanyi($content));
                    }
                if ($content == '新闻') {
                    $pdo = new PDO('mysql:dbname=syg;charset=utf8', 'root', '');
                    $pdostatement = $pdo->query("select * from news");
                    $data = $pdostatement->fetchAll(PDO::FETCH_ASSOC);

                    foreach ($data as $news) {
                        $sendData[]  = array(
                            'title' => $news['title'],
                            'desc' => $news['description'],
                            'img' => $news['picurl'],
                            'url' => $news['url']
                        );
                    }
                    $this->sendNews($fromUserName, $toUserName, $sendData);
                }

                    #图灵机器人聊天
                    $this->sendText($fromUserName, $toUserName,$this->liaotian($content));
                    break;
                default:
                    # code...
                    break;
            }

    }


    /*
     * 翻译功能
     * @param  string $content 内容
     * @return string
     */
    public function fanyi($content) 
    {
        //$content = "翻译dog";
        //判断内容里面是否有翻译二字
        // 步骤1：定义请求接口
        $api = "http://fanyi.youdao.com/openapi.do?keyfrom=xujiangtao&key=1490852988&type=data&doctype=json&version=1.1&q=".str_replace('翻译', '', $content);
        // 步骤2：获取数据
        $data = json_decode(file_get_contents($api), true);
        return $data['translation'][0];
    }
    /*
     * 智能聊天
     * @param  string $content 内容
     * @param  user   $userid  用户ID
     * @return string
     */
    public function liaotian($content, $userid = '') 
    {
        $api = "http://www.tuling123.com/openapi/api";
        $apiData = json_encode(array(
            "key"=>"105042050cc245fd9ec31f21f5da6952",
            "info"=> $content,
            "userid"=>$userid
        ));
        $data = $this->httpRequest($api, $apiData, true);
        return $data['text'];
    }





}


//创建微信对象
$wechatObj = new wechat;
//验证身份
//$wechatObj->valid();
//响应请求消息
$wechatObj->responseMsg();

