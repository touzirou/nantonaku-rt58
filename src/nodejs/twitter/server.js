var twitter = require('twitter'),
    http = require('http'),
    qs = require('querystring'),
    async = require('async');

var server = http.createServer();
var client = new twitter({
    consumer_key: process.env.TWITTER_CONSUMER_KEY,
    consumer_secret: process.env.TWITTER_CONSUMER_SECRET,
    access_token_key: process.env.TWITTER_ACCESS_TOKEN_KEY,
    access_token_secret: process.env.TWITTER_ACCESS_TOKEN_SECRET
});

/**
 * Tweetする
 * @param {array} res 
 * @param {array} query リクエストデータ
 */
function post(res, query){
    client.post('statuses/update', {status: query.tweet}, function(error, tweet, response){
        if(!error){
            console.log('tweet success');
            createResponse(res, 200, null);
        }else{
            console.log('tweet error');
            createResponse(res, 500, null);
        }
    });
}

/**
 * 特定アカウントのタイムラインを取得する
 * @param {array} res レスポンス
 * @param {array} query リクエストデータ
 */
function get(res, query){

    params = {screen_name: query.screen_name, count: query.count};

    client.get('statuses/user_timeline', params, function(error, tweets, response){
        if(!error){
            console.log('get success');
            createResponse(res, 200, JSON.stringify(tweets));
        }else{
            console.log('get error');
            createResponse(res, 500, null);
        }
    });
}

/**
 * Tweet 検索
 * @param {array} res 
 * @param {array} query リクエストデータ
 */
function search(res, query){

    var params = {q: query.condition, count: query.count};

    client.get('search/tweets', params, function(error, tweets, response){
        if(!error){
            createResponse(res, 200, JSON.stringify(tweets));
        }else{
            console.log('error');
            createResponse(res, 500, null);
        }
    });
}

/**
 * 処理振り分け
 * @param {array} res レスポンス
 * @param {array} req リクエスト
 * @param {array} query リクエストデータ
 */
function dispatch(res, req, query){
    switch(req.url){
        case '/post':
            post(res, query);
            break;
        case '/get':
            get(res, query);
            break;
        case '/search':
            search(res, query);
            break;
        case '/stream':
            strerm();
        default:
            console.log('想定外のリクエスト');
            createResponse(res, 400, null);
            break;
    }
}

/**
 * レスポンスデータ作成
 * @param {array} response レスポンス
 * @param {number} code HTTPリターンコード
 * @param {string|null} data レスポンスデータ
 */
function createResponse(response, code, data){
    response.writeHead(code, {'Content-Type': 'application/json'}); 
    response.write(data);
    response.end();
}

/**
 * HTTP サーバー起動
 */
server.on('request', function(req, res){

    if(req.method === 'POST'){
        var bufs = [];
        bufs.totalLength = 0;
        var query = "";

        // リクエストデータ取得
        req.on("data", function(chunk){
            bufs.push(chunk);
            bufs.totalLength += chunk.length;
        });

        // 処理開始
        req.on("end", function(){
            var data = Buffer.concat(bufs, bufs.totalLength);
            query = qs.parse(data.toString());
            dispatch(res, req, query);
        });
    }else{
        createResponse(res, 404, null);
    }
});

server.listen(process.env.POST, process.env.HOST);
console.log("server listening...");
