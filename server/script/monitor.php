<?php
class Monitor
{
    //设置需要监控的端口
    const PORT = 9999;

    public function port()
    {
        //需要执行的端口监控命令 (shell)
        $getPortStatus = "netstat -anp|grep " . self::PORT . " | grep LISTEN | wc -l";
        //执行命令,获取输出结果
        $result = shell_exec($getPortStatus);
        if ((int)$result != 1) {
            //服务端口异常,发送短信或邮件给管理员
            return 'Error';
        } else {
            return 'Normal';
        }
    }
}

$mon = new Monitor;
swoole_timer_tick(2000, function () use ($mon) {
    echo date("Y-m-d H:i:s") . "-------Monitor Server: " . $mon->port() . "\n";
});