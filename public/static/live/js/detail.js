$(function () {
    //连接 ws 服务端
    var ws = new WebSocket("ws://192.168.11.10:9999");
    ws.onopen = function (event) {
        console.log('Connect Server');
        // setTimeout(function () {
        //     ws.close();
        // }, 10000);
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

function push(data) {
    //data = JSON.parse(data);
    var typeName = '';//比赛节数
    switch (parseInt(data.type)) {
        case 1:
            typeName = '第一节';
            break;
        case 2:
            typeName = '第二节';
            break;
        case 3:
            typeName = '第三节';
            break;
        case 4:
            typeName = '第四节';
            break;
    }
    var gameTime = data.time;
    var gameHtml = '<div class="frame">';
        gameHtml += '<h3 class="frame-header">';
		gameHtml += '<i class="icon iconfont icon-shijian"></i>' +typeName+ ' ' + gameTime;
		gameHtml += '</h3>';
		gameHtml += '<div class="frame-item">';
		gameHtml += '<span class="frame-dot"></span>';
		gameHtml += '<div class="frame-item-author">';
        gameHtml += '<img src="' +data.logo+ '" width="20px" height="20px" />';
        gameHtml += data.team;
		gameHtml += '</div>';
        gameHtml += '<p>' +gameTime+ ' ' +data.content+ '</p>';
        if (data.image != '') {
            gameHtml += '<p><img src="' +data.image+ '" width="40%" /></p>';
        }
        gameHtml += '</div></div>';
        //将赛况内容插入到赛况最上方节点
        $("#match-result").prepend(gameHtml);
        //重新设置比分
        if (data.score_left != 0 && data.score_left != null && data.score_left != undefined) {
            //左边的队伍
            $(".poster .score_team_left").html(data.score_left);
        }
        if (data.score_right != 0 && data.score_right != null && data.score_right != undefined) {
            //左边的队伍
            $(".poster .score_team_right").html(data.score_right);
        }
}

/**
 * 添加聊天内容
 * @param  data 
 */
function pushChat(data){
    var chatHtml = '<div class="comment">';
    chatHtml += '<span>' +data.user_name+ '</span>';
    chatHtml += ' <span>' +data.content+ '</span>';
    chatHtml += '</div>';
    $("#comments").prepend(chatHtml);
}