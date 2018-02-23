import boto3, json, base64, hashlib, hmac, os

print('Loading function')

def lambda_handler(event, context):

    if "X-Line-Signature" in event["headers"]:
    
        channel_secret = os.environ["LINE_CH_SECRET"]
        body = event.get('body', '')
        hash = hmac.new(channel_secret.encode('utf-8'),body.encode('utf-8'), hashlib.sha256).digest()
        signature = base64.b64encode(hash).decode('utf-8')

        if signature == event["headers"]["X-Line-Signature"]:
            # Lambda Function 呼出
            clientLambda = boto3.client("lambda")
            clientLambda.invoke(
                FunctionName="line_bot",
                InvocationType="Event",
                Payload=body
            )

    return {
        'statusCode': '200',
        'body': "",
        'headers': {
            'Content-Type': 'application/json',
        },
    }

