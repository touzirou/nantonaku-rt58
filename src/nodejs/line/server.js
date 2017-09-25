const http = require('http'),
      aws = require('aws-sdk');

const port = 3000;
const functionName = 'line_bot';

aws.config.update({
    region : "us-west-2"
});
var lambda = new aws.Lambda();

http.createServer((req, res) => {
    // POST のみ有効
    if(req.url !== '/' || req.method !== 'POST'){
        res.writeHead(200, {'Content-Type': 'text/plain'});
        res.end('');
    }

    // リクエストデータ受信
    let body = '';
    req.on('data', (chunk) => {
        body += chunk;
    });
    // 受信完了
    req.on('end', () => {
        if(body !== ''){
            var params = {
                FunctionName: functionName,
                InvokeArgs: body
            };

            // Lambda呼出し
            lambda.invokeAsync(params, function(err, data) {
                if (err) console.log(err, err.stack);
                else     console.log(data);
            });
        }
        res.writeHead(200, {'Content-Type': 'test/plain'});
        res.end();
    });
}).listen(port);

console.log(`Server running at ${port}`);
