{{ $name}}<br />
<br />
Uqeyをご利用頂きまして、ありがとうございます。<br />
以下の内容で、予約の受付が完了しました。<br />
@if(!empty($data['policy_url']))
貸渡約款のご確認（ご利用前に必ずお読みください）<br />
<a href="{{ $data['policy_url'] }}"> {{ $data['policy_title']}}</a><br />
@endif
<br />
【予約内容】<br />
予約管理番号：{{ $data['booking_id'] }}<br />
出発ステーション：{{ $data['start_station'] }}<br />
返却ステーション：{{ $data['end_station'] }}<br />
車両：{{ $data['brand'] }} {{ $data['name'] }}<br />
車両ナンバー：{{ $data['vehicle_number'] }}<br />
予約日時：{{ $data['start_time'] }}<br />
返却日時：{{ $data['end_time'] }}<br />
予約時間：{{ $data['time_distance'] }}<br />
予約時間料金：{{ number_format($data['usage_fee'], 0, ',') }}円<br />
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
料金合計：{{ number_format($data['total_amount']) }}円<br />
<br />
運転者情報：{{ $data['nameSub'] }}<br />
<br />
キャンセルポリシー：<br />
@if ($data['max_amount'] || $data['max_amount'] === 0)
キャンセル料金 上限 {{ number_format($data['max_amount'], 0, ',') }}円<br />
@else
キャンセル料金 上限 無し<br />
@endif
@if (!empty($data['cancel_plan']))
    @foreach ($data['cancel_plan'] as $item)
        {{ $item['day_number'] }}日前 料金の{{ $item['rate']}}％<br />
    @endforeach
@endif
<br />
ご利用には、運転免許証のご登録が必要です。<br />
まだご登録がお済みでない場合は、ご利用日までに<br />
Uqeyスマートフォンアプリからご登録をお願いいたします。<br />
{{ $data['config_info'] }}<br />
{{ $data['new_reservation_email_add_message'] }}
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
