import json, re, urllib, urllib.request, os, boto3, datetime

print('Loading function')
push_data = {}
category_map = {}
jan_code_list = []

def name_normalize(name):
    """
    文字列から特定の文字列を削除するメソッド
    """

    # 特定の文字列を削除
    name = re.sub(r'\d{8}', '', name)
    name = re.sub(r'3DS', '', name)
    name = re.sub(r'PS VITA', '', name)
    name = re.sub(r'PS4', '', name)

    # カッコとその中身を削除
    name = re.sub(r'【.*?】', '', name)
    name = re.sub(r'《.*?》', '', name)
    name = re.sub(r'＜.*?＞', '', name)
    name = re.sub(r'（.*?）/', '', name)
    name = re.sub(r'\[.*?\]', '', name)
    name = re.sub(r'※.*?※', '', name)

    # 記号名を削除
    name = re.sub(r'☆', '', name);
    name = re.sub(r'♪', '', name);
    name = re.sub(r'◆', '', name);

    return name;

def search_data (offset, target_date):
    """
    Yahoo API を使用して商品データを取得する
    """
    
    global push_data
    
    # URL作成
    url = "https://shopping.yahooapis.jp/ShoppingWebService/V1/json/itemSearch"
    url = url + '?appid=' + os.environ["APP_ID"]
    url = url + '&category_id=2161'
    url = url + '&store_id=wondergoo'
    url = url + '&condition=new'
    url = url + '&seller=store'
    url = url + '&hits=50'
    url = url + '&offset=' + str(offset)

    # リクエスト送信、結果取得
    req = urllib.request.Request(url=url)
    f = urllib.request.urlopen(req)
    respons = json.loads(f.read().decode('UTF-8'))
    result_set = respons['ResultSet']['0']['Result']

    # 取得結果のゲームタイトルををカテゴリ毎に並べる
    for str_index in result_set.keys():
        if 'Name' in result_set[str_index]:
            jan = result_set[str_index]["JanCode"]
            release_date = result_set[str_index]["ReleaseDate"][0:10]  # 発売日
            title = name_normalize(result_set[str_index]["Name"])  # ゲームタイトル
            category_name = result_set[str_index]["Category"]["Current"]["Name"] # カテゴリ名称
            category_id = result_set[str_index]["Category"]["Current"]["Id"] # カテゴリID

            # 取得したカテゴリ名称を保持しておく
            category_map[category_id] = category_name

            # 対象の発売日であるゲームタイトルのみ保持しておく
            if release_date == target_date:
                if jan in jan_code_list:
                    continue
                jan_code_list.append(jan)
                
                if category_id not in push_data:
                    # 初めて取得したカテゴリーの場合は最初にカテゴリー名称を追加しておく
                    push_data[category_id] = ['【' + category_name + '】'] 
                push_data[category_id].append("・" + title)

    return int(respons['ResultSet']['totalResultsAvailable'])

def line_bot_function(text, invocationType):
    """
    LINE BOT用のLambda Function を呼び出す
    """

    # リクエストパラメータ    
    request = {
        "events": [{
            "type": "push",
            "message" : {
                "text" : text
            },
        }]
    }

    # Lambda Function 呼出
    clientLambda = boto3.client("lambda")
    clientLambda.invoke(
        FunctionName="line_bot",
        InvocationType=invocationType,
        Payload=json.dumps(request)
    )

def lambda_handler(event, context):
    """
    Lambda Function呼出時、最初に実行されるメソッド
    """

    global push_data
    total = 0
    offset = 50
    target_date = (datetime.date.today() + datetime.timedelta(days=1)).strftime("%Y-%m-%d")

    # パラメータに取得対象とする発売日が含まれている場合、それを使用する
    if "target_date" in event:
        target_date = event['target_date']


    # 対象の発売日のゲームタイトルを取得する
    total = search_data(0, target_date)
    while offset < total:
        search_data(offset, target_date)
        offset += 50

    if len(push_data) == 0:
        return

    # 対象のゲームタイトルが存在した場合、LINE BOT用のLambda Functionを呼び出す
    line_bot_function(target_date + " 発売のゲーム", "RequestResponse")
    for category in push_data.keys():
        line_bot_function("\r\n".join(push_data[category]), "Event")

