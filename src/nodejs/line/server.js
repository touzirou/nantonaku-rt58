
const http = require('http'),
    line = require('@line/bot-sdk');

const client = new line.Client({
    channelAccessToken: process.env.CH_ACCESS_TOKEN
});

/**
 * リプライをする
 * @param {*} replyToken 
 * @param {*} text 
 */
function reply(replyToken, text){
    var message = {
        type: 'text',
        text: text
    };
    client.replyMessage(replyToken, message).then(() => {

    }).catch((err) => {
        // error
    });
}

/**
 * プッシュする
 * @param {*} to 
 * @param {*} text 
 */
function push(to, text){

    var message = {
        type: 'text',
        text: text
    };

    client.pushMessage(to, message).then(() => {

    }).catch((err) => {
        // error
    });
}

http.createServer((req, res) => {
    if(req.url !== '/' || req.method !== 'POST'){
        res.writeHead(200, {'Content-Type': 'text/plain'});
        res.end('');
    }

    let body = '';
    req.on('data', (chunk) => {
        body += chunk;
    });
    req.on('end', () => {
        if(body !== ''){
            let WebhookEventObject = JSON.parse(body).events[0];
            switch(WebhookEventObject.type){
                case 'message':
                    reply(WebhookEventObject.replyToken, WebhookEventObject.message.text);
                    break;
                case 'push':
                    push(process.env.TO, WebhookEventObject.message);
                    break;
            }
        }
        res.writeHead(200, {'Content-Type': 'test/plain'});
        res.end();
    });
}).listen(process.env.PORT);

console.log(`Server running at ${process.env.PORT}`);