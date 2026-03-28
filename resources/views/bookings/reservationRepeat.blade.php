{{ $data['franchise_name'] }} {{ $data['station_name_jp'] }}様<br />
<br />
Uqeyからのお知らせです。<br />

@if($type === App\Enums\SendMailReservationsType::BEFORE->value)
    {{ $data['vehicle_name'] }}({{ $data['vehicle_number'] }})の貸出時間が近づいています。<br />
@elseif ($type === App\Enums\SendMailReservationsType::AFTER->value)
    {{ $data['vehicle_name'] }}({{ $data['vehicle_number'] }})の返却時間が近づいています。<br />
@else
    {{ $data['vehicle_name'] }}({{ $data['vehicle_number'] }})が、返却日時を過ぎましたが未返却です。<br />
@endif

下記URLから詳細をご確認ください。<br />
<br />
予約詳細URL：{{ $data['url'] }}<br />
