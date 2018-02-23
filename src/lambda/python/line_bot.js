'use strict';

var https = require('https');
var aws = require('aws-sdk');
var dynamo = new aws.DynamoDB.DocumentClient({region: 'us-west-2'});
var tableName = "line_bot";
var botName = "game_sale_bot"

/**
 * userId を dynamoDB に登録登録する
 */
function follow(event_data){
    // パラメータ
    var params = {
        TableName : tableName,
        Item:{
            "bot_name" : botName,
            "user_id": event_data.source.userId,
        }
    };
    
    console.log("Adding a new item...");

    // データ登録
    dynamo.put(params, function(err, data) {
        if (err) {
            console.error("Unable to add item. Error JSON:", JSON.stringify(err, null, 2));
        } else {
            console.log("Added item:", JSON.stringify(data, null, 2));
        }
    });
}

/**
 * メッセージ返信
 */
function reply(event_data){
    message_reply(event_data.replyToken, event_data.message.text);
}

/**
 * プッシュ配信
 */
function push(event_data){
    
    // DynamoDBからPush対象のUser_idを取得する
    var query_params = {
        TableName : tableName,
        KeyConditionExpression: "#bn = :name",
        ExpressionAttributeNames:{
            "#bn": "bot_name"
        },
        ExpressionAttributeValues: {
            ":name": botName
        }
    };
    
    dynamo.query(query_params, function(err, data) {
        if (err) {
            console.error("Unable to query. Error:", JSON.stringify(err, null, 2));
        } else {
            console.log("Query succeeded.");
            data.Items.forEach(function(item) {
                message_push(item.user_id, event_data.message.text);
            });
        }
    });
}

/**
 * LINE でReplyを行う
 */
function message_reply(replyToken, message){
    var message_data = {
        "replyToken": replyToken,
        "messages": [
            {
                "type": 'text',
                "text": message
            }
        ]
    };
    var body = JSON.stringify(message_data);
    
    var req = https.request({
        hostname: "api.line.me",
        port: 443,
        path: "/v2/bot/message/reply",
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "Content-Length": Buffer.byteLength(body),
            "Authorization": "Bearer " + process.env.CH_ACCESS_TOKEN
        }
    });
    req.end(body, (err) => {
        err && console.log(err);
    });
}

/**
 * 対象のUserに対しLINE Pushを行う
 */
function message_push(user_id, message){
    console.log('push message');
    var message_data = {
        "to": user_id,
        "messages": [
            {
                "type": 'text',
                "text": message
            }
        ]
    };
    var body = JSON.stringify(message_data);
    
    var req = https.request({
        hostname: "api.line.me",
        port: 443,
        path: "/v2/bot/message/push",
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "Content-Length": Buffer.byteLength(body),
            "Authorization": "Bearer " + process.env.CH_ACCESS_TOKEN
        }
    });
    req.end(body, (err) => {
        err && console.log(err);
    });
}

exports.handler = (event, context, callback) => {
    console.log('get event');

    //パラメータチェック
    if(event.events == null || event.events[0] == null){
        console.log("想定外のパラメータ")
        console.log(event);
        return;
    }
    var event_data = event.events[0];
    console.log(event_data.type);
    
    switch(event_data.type){
        // 友達追加時
        case "follow":
            console.log("友達追加イベント")
            follow(event_data);
            break;
        // メッセージ受信時
        case "message":
            console.log("メッセージ受信イベント")
            reply(event_data);
            break;
        // プッシュ送信時
        case "push":
            console.log("プッシュ送信時イベント")
            push(event_data);
            break;
        default:
            console.log("想定外のイベント : " + event_data.type);
            break;
    }
    
};
