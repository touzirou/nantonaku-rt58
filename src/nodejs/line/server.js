'use strict';

var http = require('http'),
    https = require('https'),
    crypt = require('crypto');

const HOST = 'api.line.me';
const REPLY_PATH = '/v2/bot/message/reply';
const CH_SECRET = process.env.CH_SECRET;
const CH_ACCESS_TOKEN = process.env.CH_ACCESS_TOKEN;
const SIGNATURE = crypt.createHmac('sha256', CH_SECRET);
const PORT = process.env.PORT;

/**
 * http リクエスト部分
 * @param {*} replyToken 
 * @param {*} sendMessageObject 
 */
const client = (replyToken, sendMessageObject) => {
    let postDataStr = JSON.stringify({ replyToken: replyToken, messages: sendMessageObject});
    let options = {
        host: HOST,
        port: 443,
        path: REPLY_PATH,
        method: 'POST',
        headers: {
            'Content-Type' : 'application/json; charset=UTF-8',
            'X-Line-Signature' : SIGNATURE,
            'Authorization' : `Bearer ${CH_ACCESS_TOKEN}`,
            'Content-Length' : Buffer.byteLength(postDataStr)
        }
    };

    return new Promise((resolve, reject) => {
        let req = https.request(options, (res) => {
            let body = '';
            res.setEncoding('utf8');
            res.on('data', (chunk) => {
                body += chunk;
            });
            res.on('end', () => {
                resolve(body);
            });
        });

        req.on('error', (e) => {
            reject(e);
        });
        req.write(postDataStr);
        req.end();
    });
};

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
        if(body === ''){
            console.log('bodyが空です');
            return;
        }

        let WebhookEventObject = JSON.parse(body).events[0];
        // メッセージが送られてきた場合
        if(WebhookEventObject.type === 'message'){
            let SendMessageObject;
            if(WebhookEventObject.message.type === 'text'){
                SendMessageObject = [{
                    type: 'text',
                    text: WebhookEventObject.message.text
                }];
            }
            client(WebhookEventObject.replyToken, SendMessageObject).then((body) => {
                console.log(body);
            }, (e)=>{console.log(e)});
        }

        res.writeHead(200, {'Content-Type': 'test/plain'});
        res.end('su');
    });
}).listen(PORT);

console.log(`Server running at ${PORT}`);