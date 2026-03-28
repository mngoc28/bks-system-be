<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => 'Trường :attribute phải được chấp nhận.',
    'accepted_if' => 'Trường :attribute phải được chấp nhận khi :other là :value.',
    'active_url' => 'Trường :attribute không phải là một URL hợp lệ.',
    'after' => 'Trường :attribute phải sau :date.',
    'after_or_equal' => 'Trường :attribute phải sau hoặc bằng :date.',
    'alpha' => 'Trường :attribute chỉ có thể chứa các chữ cái.',
    'alpha_dash' => 'Trường :attribute chỉ có thể chứa chữ cái, số và dấu gạch ngang.',
    'alpha_num' => 'Trường :attribute chỉ có thể chứa chữ cái và số.',
    'array' => 'Trường :attribute phải là một mảng.',
    'before' => 'Trường :attribute phải trước :date.',
    'before_or_equal' => 'Trường :attribute phải trước hoặc bằng :date.',
    'between' => [
        'numeric' => 'Trường :attribute phải nằm trong khoảng :min - :max.',
        'file' => 'Dung lượng trường :attribute phải từ :min - :max kB.',
        'string' => 'Trường :attribute phải từ :min - :max ký tự.',
        'array' => 'Trường :attribute phải có từ :min - :max phần tử.',
    ],
    'boolean' => 'Trường :attribute phải là true hoặc false.',
    'confirmed' => 'Xác nhận trường :attribute không khớp.',
    'current_password' => 'Mật khẩu hiện tại không chính xác.',
    'date' => 'Trường :attribute không phải là định dạng của ngày-tháng.',
    'date_equals' => 'Trường :attribute phải là một ngày bằng với :date.',
    'date_format' => 'Trường :attribute không giống với định dạng :format.',
    'declined' => 'Trường :attribute phải được từ chối.',
    'declined_if' => 'Trường :attribute phải được từ chối khi :other là :value.',
    'different' => 'Trường :attribute và :other phải khác nhau.',
    'digits' => 'Độ dài của trường :attribute phải là :digits chữ số.',
    'digits_between' => 'Độ dài của trường :attribute phải nằm trong khoảng :min và :max chữ số.',
    'dimensions' => 'Trường :attribute có kích thước không hợp lệ.',
    'distinct' => 'Trường :attribute có giá trị trùng lặp.',
    'email' => 'Trường :attribute phải là một địa chỉ email hợp lệ.',
    'ends_with' => 'Trường :attribute phải kết thúc bằng một trong những giá trị sau: :values',
    'enum' => 'Giá trị đã chọn trong trường :attribute không hợp lệ.',
    'exists' => 'Giá trị đã chọn trong trường :attribute không hợp lệ.',
    'file' => 'Trường :attribute phải là một tệp tin.',
    'filled' => 'Trường :attribute không được bỏ trống.',
    'gt' => [
        'numeric' => 'Trường :attribute phải lớn hơn :value.',
        'file' => 'Dung lượng trường :attribute phải lớn hơn :value kB.',
        'string' => 'Độ dài trường :attribute phải lớn hơn :value ký tự.',
        'array' => 'Trường :attribute phải có nhiều hơn :value phần tử.',
    ],
    'gte' => [
        'numeric' => 'Trường :attribute phải lớn hơn hoặc bằng :value.',
        'file' => 'Dung lượng trường :attribute phải lớn hơn hoặc bằng :value kB.',
        'string' => 'Độ dài trường :attribute phải lớn hơn hoặc bằng :value ký tự.',
        'array' => 'Trường :attribute phải có ít nhất :value phần tử.',
    ],
    'image' => 'Trường :attribute phải là định dạng hình ảnh.',
    'in' => 'Giá trị đã chọn trong trường :attribute không hợp lệ.',
    'in_array' => 'Trường :attribute phải thuộc tập cho phép: :other.',
    'integer' => 'Trường :attribute phải là một số nguyên.',
    'ip' => 'Trường :attribute phải là một địa chỉ IP.',
    'ipv4' => 'Trường :attribute phải là một địa chỉ IPv4.',
    'ipv6' => 'Trường :attribute phải là một địa chỉ IPv6.',
    'mac_address' => 'Trường :attribute phải là một địa chỉ MAC.',
    'json' => 'Trường :attribute phải là một chuỗi JSON.',
    'lt' => [
        'numeric' => 'Trường :attribute phải nhỏ hơn :value.',
        'file' => 'Dung lượng trường :attribute phải nhỏ hơn :value kB.',
        'string' => 'Độ dài trường :attribute phải nhỏ hơn :value ký tự.',
        'array' => 'Trường :attribute phải có ít hơn :value phần tử.',
    ],
    'lte' => [
        'numeric' => 'Trường :attribute phải nhỏ hơn hoặc bằng :value.',
        'file' => 'Dung lượng trường :attribute phải nhỏ hơn hoặc bằng :value kB.',
        'string' => 'Độ dài trường :attribute phải nhỏ hơn hoặc bằng :value ký tự.',
        'array' => 'Trường :attribute không được có nhiều hơn :value phần tử.',
    ],
    'max' => [
        'numeric' => 'Trường :attribute không được lớn hơn :max.',
        'file' => 'Dung lượng trường :attribute không được lớn hơn :max kB.',
        'string' => 'Trường :attribute không được lớn hơn :max ký tự.',
        'array' => 'Trường :attribute không được có nhiều hơn :max phần tử.',
    ],
    'mimes' => 'Trường :attribute phải là một tập tin có định dạng: :values.',
    'mimetypes' => 'Trường :attribute phải là một tập tin có định dạng: :values.',
    'min' => [
        'numeric' => 'Trường :attribute phải lớn hơn hoặc bằng :min.',
        'file' => 'Dung lượng trường :attribute phải lớn hơn hoặc bằng :min kB.',
        'string' => 'Trường :attribute phải lớn hơn hoặc bằng :min ký tự.',
        'array' => 'Trường :attribute phải có ít nhất :min phần tử.',
    ],
    'multiple_of' => 'Trường :attribute phải là bội số của :value',
    'not_in' => 'Giá trị đã chọn trong trường :attribute không hợp lệ.',
    'not_regex' => 'Trường :attribute có định dạng không hợp lệ.',
    'numeric' => 'Trường :attribute phải là một số.',
    'password' => 'Mật khẩu không chính xác.',
    'present' => 'Trường :attribute phải được cung cấp.',
    'prohibited' => 'Trường :attribute bị cấm.',
    'prohibited_if' => 'Trường :attribute bị cấm khi :other là :value.',
    'prohibited_unless' => 'Trường :attribute bị cấm trừ khi :other là một trong :values.',
    'prohibits' => 'Trường :attribute cấm :other phải không tồn tại.',
    'regex' => 'Trường :attribute có định dạng không hợp lệ.',
    'required' => 'Trường :attribute là bắt buộc.',
    'required_if' => 'Trường :attribute là bắt buộc khi :other là :value.',
    'required_unless' => 'Trường :attribute là bắt buộc trừ khi :other là một trong :values.',
    'required_with' => 'Trường :attribute là bắt buộc khi :values có giá trị.',
    'required_with_all' => 'Trường :attribute là bắt buộc khi tất cả :values có giá trị.',
    'required_without' => 'Trường :attribute là bắt buộc khi :values không có giá trị.',
    'required_without_all' => 'Trường :attribute là bắt buộc khi không có :values nào được chọn.',
    'same' => 'Trường :attribute và :other phải giống nhau.',
    'size' => [
        'numeric' => 'Trường :attribute phải bằng :size.',
        'file' => 'Dung lượng trường :attribute phải bằng :size kB.',
        'string' => 'Trường :attribute phải bằng :size ký tự.',
        'array' => 'Trường :attribute phải chứa :size phần tử.',
    ],
    'starts_with' => 'Trường :attribute phải được bắt đầu bằng một trong những giá trị sau: :values',
    'string' => 'Trường :attribute phải là một chuỗi ký tự.',
    'timezone' => 'Trường :attribute phải là một múi giờ hợp lệ.',
    'unique' => 'Trường :attribute đã có trong cơ sở dữ liệu.',
    'uploaded' => 'Trường :attribute tải lên thất bại.',
    'url' => 'Trường :attribute không giống với định dạng một URL.',
    'uuid' => 'Trường :attribute phải là một chuỗi UUID hợp lệ.',


    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'repassword' => [
            'same' => 'Xác nhận mật khẩu phải giống với mật khẩu mới.',
        ],

        'password' => [
            'min' => 'Mật khẩu phải có ít nhất 8 ký tự.',
        ],

        'rent_fee_value_*' => [
            'between' => 'Trường :attribute phải nằm trong khoảng 0 - 999,999,999.',
        ],

        'fuel_fee_value_*' => [
            'between' => 'Trường :attribute phải nằm trong khoảng 0 - 999,999,999.',
        ],

        'cleaning_fee_value_*' => [
            'between' => 'Trường :attribute phải nằm trong khoảng 0 - 999,999,999.',
        ],

        'cost.*' => [
            'between' => 'Trường :attribute phải nằm trong khoảng 0 - 999,999,999.',
        ],

        'updated_costs.*.cost' => [
            'between' => 'Trường :attribute phải nằm trong khoảng 0 - 999,999,999.',
        ],

        'added_costs.*.cost' => [
            'between' => 'Trường :attribute phải nằm trong khoảng 0 - 999,999,999.',
        ],

        'cost' => [
            'between' => 'Trường :attribute phải nằm trong khoảng 0 - 999,999,999.',
        ],

        'base_cost.*' => [
            'required_if' => 'Trường :attribute là bắt buộc khi trạng thái sử dụng được thiết lập.',
        ],

        'other_cost.*' => [
            'required_if' => 'Trường :attribute là bắt buộc khi trạng thái sử dụng được thiết lập.',
        ],

        'initial_cost.*' => [
            'required_if' => 'Trường :attribute là bắt buộc khi trạng thái sử dụng được thiết lập.',
        ],

        'optional_cost.*' => [
            'required_if' => 'Trường :attribute là bắt buộc khi trạng thái sử dụng được thiết lập.',
        ],
    ],


    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'custom' => [
    'name' => [
        'required' => 'Trường họ và tên là bắt buộc.',
        'string' => 'Trường họ và tên phải là chuỗi ký tự.',
        'max' => 'Trường họ và tên không được dài quá 255 ký tự.',
    ],

    'email' => [
        'required' => 'Trường email là bắt buộc.',
        'email' => 'Trường email phải là một địa chỉ email hợp lệ.',
        'max' => 'Trường email không được dài quá 255 ký tự.',
        'unique' => 'Trường email đã tồn tại trong hệ thống.',
    ],

    'password' => [
        'required' => 'Trường mật khẩu là bắt buộc.',
        'string' => 'Trường mật khẩu phải là chuỗi ký tự.',
        'min' => 'Mật khẩu phải có ít nhất 6 ký tự.',
        'confirmed' => 'Xác nhận mật khẩu không khớp.',
    ],

    'user_type' => [
        'required' => 'Trường loại người dùng là bắt buộc.',
        'string' => 'Trường loại người dùng phải là chuỗi ký tự.',
        'max' => 'Trường loại người dùng không được dài quá 50 ký tự.',
        'in' => 'Trường loại người dùng không hợp lệ.',
    ],
    ],

    'attributes' => [
    'name' => 'họ và tên',
    'email' => 'email',
    'password' => 'mật khẩu',
    'user_type' => 'loại người dùng',
    ],

];
