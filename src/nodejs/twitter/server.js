var twitter = require('twitter'),
    http = require('http'),
    qs = require('querystring');

var server = http.createServer();
var client = new twitter({
    consumer_key: process.env.TWITTER_CONSUMER_KEY,
    consumer_secret: process.env.TWITTER_CONSUMER_SECRET,
    access_token_key: process.env.TWITTER_ACCESS_TOKEN_KEY,
    access_token_secret: process.env.TWITTER_ACCESS_TOKEN_SECRET
});

function post(tweet){
    client.post('statuses/update', {status: tweet}, function(error, tweet, response){
        if(!error){
            console.log(tweet);
        }else{
            console.log('error');
        }
    });
}

function get(){
    client.get('statuses/user_timeline', function(error, tweets, response){
        if(!error){
            console.log(tweets);
        }else{
            console.log('error');
        }
    });
}

function search(condition){
    client.get('search/tweets', {q: condition}, function(error, tweets, response){
        if(!error){
            results = [];
            tweets.statuses.forEach(function(tweet){
                results.push(tweet.created_at + " : " + tweet.text);
            });
            return results;
        }else{
            console.log('error');
        }
    });
}

function dispatch(req, query){
    switch(req.url){
        case '/post':
            post(query.tweet);
            break;
        case '/get':
            get();
            break;
        case '/search':
            return search(query.condition);
        case '/stream':
            strerm();
        default:
            console.log('想定外のリクエスト');
    }
}

server.on('request', function(req, res){

    if(req.method === 'POST'){
        var bufs = [];
        bufs.totalLength = 0;
        var query = "";

        req.on("data", function(chunk){
            bufs.push(chunk);
            bufs.totalLength += chunk.length;
        });

        req.on("end", function(){
            var data = Buffer.concat(bufs, bufs.totalLength);
            query = qs.parse(data.toString());
            var resData = dispatch(req, query);
            // res.writeHead(200, {'Content-Type': 'text/plain'});
            // res.write(resData);
            // res.end();
        });
    }else{
        res.writeHead(404, {'Content-Type': 'text/html'});
    }
    res.end();
});

server.listen(process.env.POST, process.env.HOST);
console.log("server listening...");