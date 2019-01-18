$(function () {
    var ws = new WebSocket("ws://192.168.11.10:9998");
    ws.onopen = function (event) {
        console.log('Connect Server');
    }; 
    ws.onmessage = function (event) {
        var wsData = JSON.parse(event.data);
        console.log(wsData);
        switch(wsData.msg_type) {
            case 'outs':
                push(wsData);
                break;
            case 'chat':
                pushChat(wsData);
                break;
        }
    };

    ws.onclose = function (event) {
        console.log('Disconnected');
    };

    /**
     * 发送聊天内容
     */
    $("#send_comment").on("focus", function (){
        $("#send_comment").on("keyup", function (e) {
            //当前焦点在聊天内容输入框,且按下了 回车 键
            if (parseInt(e.keyCode) == 13) {
                //获取聊天内容
                var chatContent = $.trim($(this).val());
                var chatData = {
                    from_user: '17717500912',
                    user_name: 'luochen',
                    content: chatContent,
                };
                //发送聊天数据到 WebSocket
                ws.send(JSON.stringify(chatData));
                
                //清空输入框
                $(this).val('');
            }
        });
    });
});