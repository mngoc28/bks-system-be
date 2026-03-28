{{ $data['last_name'] }} {{ $data['first_name'] }}様<br />
<br />
Uqeyをご利用頂きまして、ありがとうございます。<br />
以下の内容で、返却を行いましたのでご確認ください。<br />
<br />
【予約内容】<br />
予約管理番号：{{ $data['booking_id'] }}<br />
出発ステーション：{{ $data['station_start_name_jp'] }}<br />
返却ステーション：{{ $data['station_end_name_jp'] }}<br />
車両：{{ $data['brand'] }} {{ $data['name'] }}<br />
車両ナンバー：{{ $data['vehicle_number'] }}<br />
予約日時：{{ $data['start_time'] }}<br />
返却日時：{{ $data['end_time'] }}<br />
予約時間：{{ $data['time_distance'] }}<br />
予約時間料金：{{ number_format($data['usage_fee'], 0, ',') }}円<br />
利用開始日時：{{ $data['operation_start_time'] }}<br />
利用終了日時：{{ $data['operation_end_time'] }}<br />
@if ($data['overtime'])
延長時間：{{(int)($data['overtime']/60) }}時間{{(int)($data['overtime']%60) }}分<br />
@else
延長時間：0分<br />
@endif
延長料金：{{ number_format($data['overtime_fee'], 0, ',') }}円<br />
@if (!empty($data['accessories']))
    オプション：<br>
    @foreach ($data['accessories'] as $item)
        {{ $item['name'] }} x{{ $item['quantity'] }}：{{ number_format($item['total_fee_actual'], 0, ',') }}円<br />
    @endforeach
@endif
@if (!empty($data['insurances']))
    保険：<br>
    @foreach ($data['insurances'] as $item)
    @if ($item['calc_day'] != 0)
    {{ $item['name'] }} x{{ $item['calc_day'] }}日分：{{ number_format($item['total_fee_actual'],0,',') }}円<br />
    @else
    {{ $item['name'] }}：{{ number_format($item['total_fee_actual'],0,',') }}円<br />
    @endif
    @endforeach
@endif
<br />
料金合計：{{ number_format($data['total_amount_actual'], 0, ',') }}円<br />
<br />
運転者情報：{{ $data['nameSub'] }}<br />
{{ $data['return_certificate_email_add_message'] }}<br />
<br />
----------------------------------------------------------------------------<br />
【お問い合わせ先】<br />
{{$data['franchise']}} {{$data['station_name']}}<br />
電話番号：{{$data['station_phone']}}<br />
@if ($data['always_open'])
営業時間：24時間営業<br />
@else
営業時間：{{$data['station_start_time']}}～{{$data['station_end_time']}}<br />
@endif
住所：{{$data['station_address']}}<br />
----------------------------------------------------------------------------<br />
<br />
このEメールアドレスは配信専用です。<br />
返信は受付できませんのでご了承ください。<br />
