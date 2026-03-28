<!DOCTYPE html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link rel="stylesheet" type="text/css" href="{{ public_path('M_00_03.css') }}">
</head>

<body>
    <div class="container">
        <div class="header">
            <img style="width: 50%;"
                src="https://s3-alpha-sig.figma.com/img/9d45/4c54/f6f6e4d5be8beb2a3cf49eb4afe1a3ba?Expires=1743984000&Key-Pair-Id=APKAQ4GOSFWCW27IBOMQ&Signature=G4yHEp5wIvsjltYCfohNHfWGDw2jm7eJtjj4uQ5x82yu~0VAGWs0g2oAo37tlFuz03nSi3lLYbo6rosUIhE8NqHMfvkjYW1JB-LeFLCOdAdT2H4XoKj1mhv6KjfvjeoQuX4eJVZc0XfZOJuYR0lz3LBCtNEw81WMg5MTiYC1C6QWa6csvYTXwBEInHUTcarDvuaTK5kh~3zAM9k8PRXR74Gpb8JdW4XhNUNLtvUlc4vPul2KD18IDy8cOQM0p0oX84c7ANq4SK4NCXQkVJTh1Dl-yU1o78jSrIa0PytD8Wrr5jLyKGObu9TUkzsjltQPIrX55GB7FwN7ouHBYURJoQ__"
                alt="ASUKA_CRUISE">
        </div>

        <h1>乗船用バーコードのお知らせ(Notice of boading barcode)</h1>

        <div class="customer-info">
            <p>{{ $data['last_name_jp'] . '　' . $data['first_name_jp'] }}様</p>
            <p>チケットの詳細</p>
        </div>

        <div class="frame-16">
            <table width="100%" border="1" cellspacing="0" cellpadding="5">
                <tr>
                    <td colspan="2"><strong>ステータス :</strong><span style="margin-left: 10px">{{ $data['status_name_ja'] }}</span></td>
                </tr>
            </table>

            <table width="100%" border="1" cellspacing="0" cellpadding="5">
                <tr>
                    <td colspan="2" align="center">
                        @if(isset($data['barcode']) && !empty($data['barcode']))
                            <img alt="" src="https://barcode.tec-it.com/barcode.ashx?data={{ $data['barcode'] }}&code=CODE128">
                        @else
                            <p>バーコードデータがありません (No barcode data available)</p>
                        @endif
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
