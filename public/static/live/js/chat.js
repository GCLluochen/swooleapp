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
});