<?php

return [
    'sns' => [
        'params' => [
            'credentials' =>[
                'key' => env('AWS_ACCESS_KEY_ID'),
                'secret' => env('AWS_SECRET_ACCESS_KEY')
            ],
            'region' => env('AWS_DEFAULT_REGION'),
            'version' => 'latest'
        ],
        "messageStructure" => env('AWS_SMS_STRUCTURE','DefaultSMSType'),
        "senderId" => env('AWS_SMS_SENDERID', ''),
        "originationNumber" => env('AWS_SMS_ORIGINATIONNUMBER', ''),
        "SMSType" => env('AWS_SMS_TYPE', 'Promotional'),
        "messageAttributes" => [
            "AWS.SNS.SMS.SMSType" => [
                "StringValue" => env('AWS_SMS_TYPE','Promotional'),
                "DataType" => "String",
            ],
            "AWS.SNS.SMS.SenderID" => [
                "StringValue" => env('AWS_SMS_SENDERID','mySenderID'),
                "DataType" => "String",
            ],
            "AWS.MM.SMS.OriginationNumber" => [
                'DataType' => 'String',
                'StringValue' => env('AWS_SMS_ORIGINATIONNUMBER','+84123456789'),
            ]
        ],
    ]
];
