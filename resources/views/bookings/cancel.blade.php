{{ $name }}<br />
<br />
Uqeyをご利用頂きまして、ありがとうございます。<br />
以下の予約をキャンセル致しましたのでお知らせいたします。<br />
<br />
【キャンセル料金】<br />
@if( !empty($data["cancelAmount"]) && $data["cancelAmount"] > 0)
{{number_format($data["cancelAmount"])}}円
@else 
発生しておりません
@endif
<br />
<br />
【予約内容】<br />
予約管理番号：{{$data['booking_id'] }}<br />
出発ステーション：{{$data['station_start'] }}<br />
返却ステーション：{{ $data['station_end'] }}<br />
車両：{{ $data['vehicel_model_name']  }}<br />
車両ナンバー：{{ $data['vehicle_number'] }}<br />
予約日時：{{ $data['start_time'] }}<br />
返却日時：{{ $data['end_time'] }}<br />
予約時間：{{ $data['time_distance']}}<br />
予約時間料金：{{ number_format($data['usage_fee'],0,',') }}円<br />
@if (!empty($data['accessories']))
    オプション：<br />
    @foreach ($data['accessories'] as $item)
        {{ $item['name']}} x{{ $item['quantity'] }}：{{ number_format($item['total_fee_actual'],0,',') }}円<br />
    @endforeach
@endif
@if (!empty($data['insurances']))
    保険：<br />
    @foreach ($data['insurances'] as $item)
    @if ($item['calc_day'] != 0)
    {{ $item['name'] }} x{{ $item['calc_day'] }}日分：{{ number_format($item['total_fee_actual'],0,',') }}円<br />
    @else
    {{ $item['name'] }}：{{ number_format($item['total_fee_actual'],0,',') }}円<br />
    @endif
    @endforeach
@endif
<br />
料金合計：{{number_format($data['total'])}}円<br />
<br />
運転者情報：{{$data['nameSub']}}<br />
<br />
キャンセルポリシー：<br />
@if ($data['cancel_policy_max_amount'] || $data['cancel_policy_max_amount'] === 0)
キャンセル料金 上限 {{number_format($data['cancel_policy_max_amount'])}}円<br />
@else
キャンセル料金 上限 無し<br />
@endif
@if (!empty($data['dataCanPlan']))
    @foreach ($data['dataCanPlan'] as $item)
        {{ $item['day_number'] }}日前 料金の{{ $item['rate']}}％<br />
    @endforeach
@endif
<br />
{{ $data['cancel_email_add_message'] }}<br />
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