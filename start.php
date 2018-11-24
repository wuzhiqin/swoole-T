<?php

require_once "./vendor/autoload.php";

use think\Db;

//创建webSocket服务
$server = new Swoole\WebSocket\Server("0.0.0.0", 9501);

//服务配置
$server->set(array(
    'worker_num' => 8,  //子进程数量
    "dispatch_mode" => 5,
    //设置心跳包，前端需要定时发数据ping防止断开连接（10s）
    'heartbeat_check_interval' => 5,
    'heartbeat_idle_time' => 10,
));

//服务主进程成功启动回调
$server->on("start", function () {
});

//服务子进程成功启动回调
$server->on('workerStart', function () {
    Db::setConfig([
        'type' => 'mysql',
        'break_reconnect' => true,  //是否断线重连
        'one_draw' => "mysql://root:8c6f61aada4c2039@127.0.0.1:3306/one_draw#utf8mb4",
        'wild_shooting' => "mysql://root:8c6f61aada4c2039@127.0.0.1:3306/wild_shooting#utf8mb4"
    ]);
    //定时查一下数据库，防止数据库连接丢失
    swoole_timer_tick(60 * 1000, function () {
        Db::connect('one_draw')->query("show tables;");
        Db::connect('wild_shooting')->query("show tables;");
    });
});

//有新连接回调
$server->on('open', function (Swoole\WebSocket\Server $server, $request) {
});

//收到新数据回调
$server->on('message', function (Swoole\WebSocket\Server $server, $frame) {
    if ($frame->data !== "ping") {  //不打印ping数据
        $data = json_decode($frame->data, true);  //收到客户端数据
        if (is_array($data) && isset($data['cmd'])) {
            switch ($data['cmd']) {  //cmd为指令，data为数据数组

                //绑定连接 -> 用户id (通过openid)
                case 'login':
                    if (isset($data['data']['game_code']) && isset($data['data']['openid'])) {
                        $openid = $data['data']['openid'];
                        $game_code = $data['data']['game_code'];
                        switch ($game_code) {
                            case 1:
                                $db_name = "one_draw";
                                break;
                            case 2:
                                $db_name = "wild_shooting";
                                break;
                            default:
                                $db_name = null;
                                break;
                        }
                        if (!is_null($db_name)) {
                            $user = Db::connect($db_name)->name("user")->where("openid", $openid)->field("id")->find();
                            if ($user) {
                                $uid = $user['id'];
                                $server->bind($frame->fd, $uid);  //绑定当前连接对应数据库的用户id
                                //todo：记录玩家开始游戏
                            }
                        }
                    }
                    break;

                //记录玩家日志
                case 'log':
                    //todo: 记录玩家游戏记录
                    break;
            }
        }
    }
});

//有连接断开回调
$server->on('close', function ($server, $fd) {
    $clientInfo = $server->getClientInfo($fd);  //获取当前断开连接的用户信息，包含之前bind绑定的uid(数据库用户id)
    if (isset($clientInfo['uid'])) {
        $uid = $clientInfo['uid'];
        //todo：记录玩家结束游戏
    }
});

//HTTP请求
$server->on('request', function (Swoole\Http\Request $request, Swoole\Http\Response $response) use ($server) {
    $stats = $server->stats();
    $start_time = date("Y-m-d H:i:s", $stats['start_time']);
    $response->header("Content-Type", "text/html; charset=UTF-8");
    $response->end("
    <div style='max-width:800px;margin: 0 auto;'>
        <h1>WebSocket Server is run!</h1><br>
        服务器启动的时间：{$start_time}<br>
        当前连接用户：{$stats['connection_num']}<br>
        已接受连接：{$stats['accept_count']}<br>
        已关闭连接：{$stats['close_count']}
    </div>");
});


//开启服务
$server->start();
