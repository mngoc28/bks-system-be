<!DOCTYPE html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link rel="stylesheet" type="text/css" href="{{ public_path('M_00_03.css') }}">
</head>

<body>
    <div class="container">
        <div class="header">
            <img style="width: 50%;"
                src="https://s3-alpha-sig.figma.com/img/9d45/4c54/f6f6e4d5be8beb2a3cf49eb4afe1a3ba?Expires=1742774400&Key-Pair-Id=APKAQ4GOSFWCW27IBOMQ&Signature=DyOUjYUKS0uPVsdlesbHBmWLrTKB0saYzg3io73mWedl6fg6fsr7fbB4deVorlATdrQoa13Ofq8u18RRBnErXDaSOmuWYET0e9d1~C6vHFPiB1pbLklvhT1ifggCeoon8RIaerFz~M0zqOm8vYJZlKuXHwzCYhTAGoJjCWPD0GP4~C94DZbJdj21jw~3l-laIi5POu6Sdj3uv20th-woF6u7TeN85mqanjteU58AJAluvCF58SEYjdIMfdWQW2NIOMH1p51fwsT446RU4wmn6FWpHpYnq-4KOo4qNgI6F8RJMTv5BWZGBINPRCkhcsv0sIWlyqrwjHymdEbfr96UqA__"
                alt="Traveloka">
        </div>

        <h1>訪船承認のお知らせ(Notice of approval)</h1>

        <div class="customer-info">
            <p>{{ $data['last_name_jp'] . '　' . $data['first_name_jp'] }}様</p>
            <p>ご予約リクエストが正常に確認されました。添付ファイルの電子チケットをご覧ください。</p>
        </div>

        <div class="frame-16">
            <table width="100%" border="1" cellspacing="0" cellpadding="5">
                <tr>
                    <td colspan="2"><strong>ステータス :</strong><span style="margin-left: 10px">承認済み</span></td>
                </tr>
            </table>

            <table width="100%" border="1" cellspacing="0" cellpadding="5">
                <tr>
                    <td colspan="2" align="center">
                        <img alt="" src="https://barcode.tec-it.com/barcode.ashx?data={{ $data['barcode'] }}&code=CODE128">
                    </td>
                </tr>
            </table>

            <table width="100%" border="1" cellspacing="0" cellpadding="5">
                <tr>
                    <td><strong>訪船者名</strong></td>
                    <td>{{ $data['last_name_jp'] . '　' . $data['first_name_jp'] }}</td>
                </tr>
                <tr>
                    <td><strong>代表者</strong></td>
                    <td>{{ $data['is_representative'] }}</td>
                </tr>
                <tr>
                    <td><strong>生年月日</strong></td>
                    <td>{{ $data['date_of_birth'] }}</td>
                </tr>
                <tr>
                    <td><strong>目的</strong></td>
                    <td>{{ $data['purpose'] }}</td>
                </tr>
                <tr>
                    <td><strong>乗船日</strong></td>
                    <td>{{ $data['boarding_date'] }}</td>
                </tr>
                <tr>
                    <td><strong>時間</strong></td>
                    <td>{{ $data['scheduled_boarding_time'] }}</td>
                </tr>
                <tr>
                    <td><strong>港</strong></td>
                    <td>{{ $data['boarding_port_ja'] }}</td>
                </tr>
                <tr>
                    <td><strong>船番号</strong></td>
                    <td>{{ $data['ship_number'] }}</td>
                </tr>
                <tr>
                    <td><strong>船種</strong></td>
                    <td>{{ $data['ship_model'] }}</td>
                </tr>
                <tr>
                    <td><strong>カラー</strong></td>
                    <td>{{ $data['ship_color'] }}</td>
                </tr>
            </table>
        </div>
    </div>
</body>

</html>
