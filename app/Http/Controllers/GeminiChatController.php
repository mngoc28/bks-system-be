<?php

namespace App\Http\Controllers;

use App\Enums\HttpStatus;
use App\Models\Province;
use App\Models\TouristSpot;
use App\Services\RoomsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final class GeminiChatController extends Controller
{
    /**
     * Handle AI Chatbot requests using Gemini API.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function chat(Request $request): JsonResponse
    {
        // 1. Validate request
        $request->validate([
            'message' => 'required|string',
            'history' => 'nullable|array',
            'role' => 'nullable|string|in:user,partner,admin'
        ]);

        $message = $request->input('message');
        $history = $this->getSafeHistory($request->input('history', []), 12);
        $userRole = $request->input('role', 'user');

        // 2. Load API credentials
        $apiKey = config('services.gemini.api_key');
        $configuredModel = config('services.gemini.model', 'gemini-1.5-flash');

        if (empty($apiKey)) {
            Log::error('Gemini API key is not configured.');
            return $this->errorResponse(
                'Tính năng AI Chatbot chưa được cấu hình API Key trên máy chủ.',
                'GEMINI_API_KEY_MISSING',
                HttpStatus::INTERNAL_SERVER_ERROR
            );
        }

        // 3. Format contents array (including history if available)
        $contents = [];
        foreach ($history as $turn) {
            if (isset($turn['role']) && isset($turn['parts']) && is_array($turn['parts'])) {
                $parts = [];
                foreach ($turn['parts'] as $part) {
                    if (isset($part['text'])) {
                        $parts[] = ['text' => $part['text']];
                    } elseif (isset($part['functionCall'])) {
                        $parts[] = ['functionCall' => $part['functionCall']];
                    } elseif (isset($part['functionResponse'])) {
                        $parts[] = ['functionResponse' => $part['functionResponse']];
                    }
                }
                if (!empty($parts)) {
                    $contents[] = [
                        'role' => $turn['role'] === 'model' ? 'model' : ($turn['role'] === 'function' ? 'function' : 'user'),
                        'parts' => $parts
                    ];
                }
            }
        }

        // Append current user message
        $contents[] = [
            'role' => 'user',
            'parts' => [
                ['text' => $message]
            ]
        ];

        // 4. Customize System Instruction based on role
        $systemInstructionText = $this->getSystemInstruction($userRole);

        // 5. Define Tools for user role
        $tools = [];
        if ($userRole === 'user') {
            $tools = [
                [
                    'functionDeclarations' => [
                        [
                            'name' => 'searchRooms',
                            'description' => 'Tìm kiếm các phòng nghỉ trong cơ sở dữ liệu dựa trên các tiêu chí lọc như địa điểm (tỉnh/thành phố hoặc địa danh nổi tiếng), mức giá thuê tối đa/tối thiểu (VND/ngày), số lượng khách tối đa, loại hình thuê (daily/monthly).',
                            'parameters' => [
                                'type' => 'OBJECT',
                                'properties' => [
                                    'query' => [
                                        'type' => 'OBJECT',
                                        'description' => 'Chứa các tiêu chí lọc phòng nghỉ hợp lệ bao gồm: ' .
                                                         'location (Tên tỉnh/thành phố hoặc danh thắng nổi tiếng ở Việt Nam như Đà Nẵng, Cầu Rồng...), ' .
                                                         'price_max (Mức giá thuê tối đa VND/ngày, ví dụ: 1000000 cho 1 triệu), ' .
                                                         'price_min (Mức giá thuê tối thiểu VND/ngày), ' .
                                                         'guests (Số lượng khách tối thiểu), ' .
                                                         'room_type (Hình thức thuê: "daily" hoặc "monthly"), ' .
                                                         'rating_min (Điểm đánh giá trung bình tối thiểu của phòng từ 1.0 đến 5.0, ví dụ: 5.0 cho phòng 5 sao), ' .
                                                         'sort_by (Trường sắp xếp: "cheapest_daily_price" hoặc "people"), ' .
                                                         'sort_direction (Hướng sắp xếp: "asc" hoặc "desc").'
                                    ]
                                ],
                                'required' => ['query']
                            ]
                        ]
                    ]
                ]
            ];
        }

        // 6. Execute chat session with the configured model
        try {
            $sessionContents = $contents;
            $replyText = $this->executeChatSession($configuredModel, $apiKey, $sessionContents, $systemInstructionText, $tools);
            return $this->successResponse([
                'reply' => $replyText,
                'history' => $sessionContents
            ], 'Gửi tin nhắn chatbot thành công.');
        } catch (\Exception $e) {
            $rawMessage = $e->getMessage();
            Log::error("Gemini model {$configuredModel} failed: " . $rawMessage);

            // Fallback to gemini-2.5-flash-lite first for any failures (including 429)
            $fallbackModel = 'gemini-2.5-flash-lite';
            if ($configuredModel !== $fallbackModel) {
                try {
                    Log::warning("Model {$configuredModel} failed. Attempting fallback to {$fallbackModel}. Error: " . $rawMessage);
                    $fallbackContents = $contents;
                    $replyText = $this->executeChatSession($fallbackModel, $apiKey, $fallbackContents, $systemInstructionText, $tools);
                    return $this->successResponse([
                        'reply' => $replyText,
                        'history' => $fallbackContents
                    ], 'Gửi tin nhắn chatbot thành công.');
                } catch (\Exception $fallbackException) {
                    $rawMessage = $fallbackException->getMessage();
                    Log::error("Gemini fallback model {$fallbackModel} failed: " . $rawMessage);
                }
            }

            // Check for Rate Limit / Quota Exceeded (429)
            if (
                str_contains($rawMessage, 'exceeded your current quota') ||
                str_contains($rawMessage, 'Quota exceeded') ||
                str_contains($rawMessage, '429')
            ) {
                $seconds = 60; // Default fallback
                if (preg_match('/Please retry in ([\d\.]+)s?/', $rawMessage, $matches)) {
                    $seconds = ceil((float)$matches[1]);
                }

                return $this->errorResponse(
                    "Trợ lý AI đang bận xử lý nhiều yêu cầu cùng lúc. Vui lòng thử lại sau {$seconds} giây.",
                    'GEMINI_RATE_LIMIT_EXCEEDED',
                    HttpStatus::TOO_MANY_REQUESTS
                );
            }

            // Check for Service Unavailable / Overloaded (503)
            if (
                str_contains($rawMessage, 'experiencing high demand') ||
                str_contains($rawMessage, '503') ||
                str_contains($rawMessage, 'UNAVAILABLE')
            ) {
                return $this->errorResponse(
                    'Dịch vụ AI hiện đang quá tải tạm thời. Vui lòng thử lại sau ít phút.',
                    'GEMINI_SERVICE_UNAVAILABLE',
                    HttpStatus::INTERNAL_SERVER_ERROR
                );
            }

            // Other errors
            return $this->errorResponse(
                'Lỗi kết nối dịch vụ AI: ' . $rawMessage,
                'GEMINI_API_ERROR',
                HttpStatus::BAD_REQUEST
            );
        }
    }

    /**
     * Execute chat session with a specific model.
     *
     * @param string $model
     * @param string $apiKey
     * @param array $contents
     * @param string $systemInstructionText
     * @param array $tools
     * @return string
     * @throws \Exception
     */
    private function executeChatSession(string $model, string $apiKey, array &$contents, string $systemInstructionText, array $tools): string
    {
        $maxRetries = 3;
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

        while ($maxRetries > 0) {
            $payload = [
                'contents' => $contents,
                'systemInstruction' => [
                    'parts' => [
                        ['text' => $systemInstructionText]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.7,
                    'maxOutputTokens' => 1024,
                ]
            ];

            if (!empty($tools)) {
                $payload['tools'] = $tools;
            }

            $response = Http::timeout(30)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($url, $payload);

            if ($response->failed()) {
                $errorData = $response->json();
                $errorMessage = $errorData['error']['message'] ?? $response->body();
                throw new \Exception("Model {$model} returned error: {$errorMessage}");
            }

            $responseData = $response->json();
            $candidate = $responseData['candidates'][0] ?? null;
            if (!$candidate) {
                throw new \Exception("Model {$model} returned empty candidates");
            }

            $parts = $candidate['content']['parts'] ?? [];
            $functionCallPart = null;
            $textPart = null;

            foreach ($parts as $part) {
                if (isset($part['functionCall'])) {
                    $functionCallPart = $part['functionCall'];
                }
                if (isset($part['text'])) {
                    $textPart = $part['text'];
                }
            }

            if ($functionCallPart) {
                $functionName = $functionCallPart['name'] ?? '';
                $args = $functionCallPart['args'] ?? [];

                // Save model turn
                $contents[] = [
                    'role' => 'model',
                    'parts' => [
                        ['functionCall' => $functionCallPart]
                    ]
                ];

                if ($functionName === 'searchRooms') {
                    $roomsData = $this->executeSearchRooms($args);

                    // Save function response turn
                    $contents[] = [
                        'role' => 'function',
                        'parts' => [
                            [
                                'functionResponse' => [
                                    'name' => 'searchRooms',
                                    'response' => [
                                        'rooms' => $roomsData
                                    ]
                                ]
                            ]
                        ]
                    ];
                } else {
                    $contents[] = [
                        'role' => 'function',
                        'parts' => [
                            [
                                'functionResponse' => [
                                    'name' => $functionName,
                                    'response' => ['error' => 'Unknown function']
                                ]
                            ]
                        ]
                    ];
                }

                $maxRetries--;
                continue;
            }

            if (!empty($textPart)) {
                $contents[] = [
                    'role' => 'model',
                    'parts' => [
                        ['text' => $textPart]
                    ]
                ];
                return $textPart;
            }

            throw new \Exception("Model {$model} could not generate a reply");
        }

        throw new \Exception("Model {$model} exceeded maximum recursive function calls");
    }

    /**
     * Execute search rooms query on database.
     *
     * @param array $args
     * @return array
     */
    private function executeSearchRooms(array $args): array
    {
        try {
            $roomsService = app(RoomsService::class);
            $searchParams = [];

            // Extract the query object from arguments
            $query = $args['query'] ?? [];

            // Whitelist of allowed filters for security
            $allowedFilters = [
                'location', 'price_max', 'price_min', 'guests', 'room_type',
                'sort_by', 'sort_direction', 'province_id', 'tourist_spot_slug',
                'ward_id', 'property_type_id', 'keyword', 'partner_id', 'rating_min'
            ];

            foreach ($query as $key => $value) {
                if (in_array($key, $allowedFilters, true) && !is_null($value)) {
                    // Type casting for security and database query compatibility
                    if (in_array($key, ['price_max', 'price_min'], true)) {
                        $searchParams[$key] = floatval($value);
                    } elseif (in_array($key, ['guests', 'province_id', 'ward_id', 'property_type_id', 'partner_id'], true)) {
                        $searchParams[$key] = intval($value);
                    } elseif ($key === 'room_type') {
                        // Map room_type string to rent_type query param (daily/monthly)
                        $searchParams['rent_type'] = (string) $value;
                    } else {
                        $searchParams[$key] = $value;
                    }
                }
            }

            // Normalize and resolve location to matched entities (province/spot) if location is present
            if (!empty($searchParams['location'])) {
                $location = trim($searchParams['location']);

                // 1. Normalize the search string to lowercase, accent-less space-separated words
                $normalizedLocation = \Illuminate\Support\Str::slug($location, ' ');

                $matchedProvince = null;
                $matchedSpot = null;

                // 2. Try to match any province in the search query (accent-insensitive comparison)
                $provinces = Province::all();
                foreach ($provinces as $p) {
                    $pNameSlug = \Illuminate\Support\Str::slug($p->name, ' ');
                    $pNameEnSlug = str_replace('_', ' ', $p->name_en);

                    if (mb_stripos($normalizedLocation, $pNameSlug) !== false || mb_stripos($normalizedLocation, $pNameEnSlug) !== false) {
                        $matchedProvince = $p;
                        break;
                    }
                }

                // 3. Try to match any active tourist spot in the search query
                $spots = TouristSpot::where('is_active', true)->get();
                foreach ($spots as $s) {
                    $sNameSlug = \Illuminate\Support\Str::slug($s->name, ' ');
                    $sSlug = str_replace('-', ' ', $s->slug);

                    if (mb_stripos($normalizedLocation, $sNameSlug) !== false || mb_stripos($normalizedLocation, $sSlug) !== false) {
                        $matchedSpot = $s;
                        break;
                    }
                }

                // 4. Map matched entities to parameters
                if ($matchedSpot) {
                    $searchParams['tourist_spot_slug'] = $matchedSpot->slug;
                    if ($matchedProvince) {
                        $searchParams['province_id'] = $matchedProvince->id;
                    } elseif ($matchedSpot->province_id) {
                        $searchParams['province_id'] = $matchedSpot->province_id;
                    }
                } elseif ($matchedProvince) {
                    $searchParams['province_id'] = $matchedProvince->id;
                } else {
                    // Fallback to general keyword search
                    $searchParams['keyword'] = $location;
                }

                // Remove raw location parameter so it doesn't trigger keyword search filters unexpectedly
                unset($searchParams['location']);
            }

            // 5. Map sorting parameters
            // If the user specified a maximum budget (price_max) but no explicit sorting was requested,
            // default to sorting by price descending so we show premium rooms fitting their budget first.
            if (isset($searchParams['price_max']) && !isset($searchParams['sort_by'])) {
                $searchParams['sort_by'] = 'cheapest_daily_price';
                $searchParams['sort_direction'] = 'desc';
            }

            // Request a reasonable batch of candidate rooms
            $searchParams['per_page'] = 50;
            $searchParams['with_details'] = true;

            // Merge parameters into global request so pipeline filters can read them
            request()->merge($searchParams);

            $searchResult = $roomsService->handleRoomList(request());

            $roomCollection = collect();
            if ($searchResult['success'] && isset($searchResult['data'])) {
                $data = $searchResult['data'];
                if ($data instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator) {
                    $roomCollection = collect($data->items());
                } elseif ($data instanceof \Illuminate\Support\Collection) {
                    $roomCollection = $data;
                } elseif (is_array($data)) {
                    $roomCollection = collect($data);
                }
            }

            // Fallback: If searching by tourist spot returned 0 rooms, fallback to its province
            if ($roomCollection->isEmpty() && !empty($searchParams['tourist_spot_slug'])) {
                $spot = TouristSpot::where('slug', $searchParams['tourist_spot_slug'])->first();
                if ($spot && $spot->province_id) {
                    request()->offsetUnset('tourist_spot_slug');
                    request()->merge(['province_id' => $spot->province_id]);
                    $searchResult = $roomsService->handleRoomList(request());
                    if ($searchResult['success'] && isset($searchResult['data'])) {
                        $data = $searchResult['data'];
                        if ($data instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator) {
                            $roomCollection = collect($data->items());
                        } elseif ($data instanceof \Illuminate\Support\Collection) {
                            $roomCollection = $data;
                        } elseif (is_array($data)) {
                            $roomCollection = collect($data);
                        }
                    }
                }
            }

            // Filter by price_max (secondary check)
            if (isset($searchParams['price_max'])) {
                $priceMax = floatval($searchParams['price_max']);
                $roomCollection = $roomCollection->filter(function ($room) use ($priceMax) {
                    return ($room->cheapest_daily_price ?? 0) <= $priceMax;
                });
            }

            // Filter by price_min (secondary check)
            if (isset($searchParams['price_min'])) {
                $priceMin = floatval($searchParams['price_min']);
                $roomCollection = $roomCollection->filter(function ($room) use ($priceMin) {
                    return ($room->cheapest_daily_price ?? 0) >= $priceMin;
                });
            }

            // Filter by guests capacity (secondary check)
            if (isset($searchParams['guests'])) {
                $guests = intval($searchParams['guests']);
                $roomCollection = $roomCollection->filter(function ($room) use ($guests) {
                    return ($room->people ?? 0) >= $guests;
                });
            }

            // Filter by renting unit (daily / monthly) (secondary check)
            if (isset($searchParams['rent_type'])) {
                $rentType = $searchParams['rent_type'];
                $roomCollection = $roomCollection->filter(function ($room) use ($rentType) {
                    if ($rentType === 'daily') {
                        return !empty($room->cheapest_daily_price) && floatval($room->cheapest_daily_price) > 0;
                    } elseif ($rentType === 'monthly') {
                        return !empty($room->cheapest_monthly_price) && floatval($room->cheapest_monthly_price) > 0;
                    }
                    return true;
                });
            }

            // Take the top 5 rooms
            $filteredRooms = $roomCollection->take(5);

            $formattedRooms = [];
            foreach ($filteredRooms as $room) {
                // Map nearby attractions with name, distance, and travel time
                $nearbyAttractions = [];
                if (!empty($room->tourist_summary['tourist_spots'])) {
                    foreach ($room->tourist_summary['tourist_spots'] as $spot) {
                        $nearbyAttractions[] = [
                            'name' => $spot['name'] ?? null,
                            'distance' => $spot['distance_label'] ?? null,
                            'travel_time' => $spot['travel_time_label'] ?? null,
                        ];
                    }
                }

                $formattedRooms[] = [
                    'id' => $room->id,
                    'title' => $room->title,
                    'room_type' => $room->room_type,
                    'people' => $room->people,
                    'area' => $room->area,
                    'price_per_day' => $room->cheapest_daily_price,
                    'price_per_month' => $room->cheapest_monthly_price ?? null,
                    'property_address' => $room->property_address,
                    'province_name' => $room->province_name,
                    'tourist_spot_name' => $room->tourist_summary['tourist_spot_name'] ?? null,
                    'distance_label' => $room->tourist_summary['distance_label'] ?? null,
                    'travel_time_label' => $room->tourist_summary['travel_time_label'] ?? null,
                    'nearby_attractions' => $nearbyAttractions,
                    'partner_name' => !empty($room->partner_company_name) ? $room->partner_company_name : ($room->partner_name ?? null),
                    'partner_phone' => $room->partner_phone ?? null,
                    'deposit' => $room->deposit ?? 0,
                    'amenities' => $room->amenities ?? null,
                    'services' => $room->services ?? null,
                    'description' => $room->description ?? null,
                    'rating' => $room->reviews_avg_rating ?? 0,
                    'reviews_count' => $room->reviews_count ?? 0,
                    'floor' => $room->floor_number ?? null,
                    'property_type' => $room->property_type_name ?? null,
                ];
            }

            return $formattedRooms;
        } catch (\Exception $e) {
            Log::error('Chatbot search rooms execution failed: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return [];
        }
    }

    /**
     * Get System Instruction based on User Role.
     *
     * @param string $role
     * @return string
     */
    private function getSystemInstruction(string $role): string
    {
        switch ($role) {
            case 'admin':
                return "Bạn là trợ lý AI chuyên nghiệp hỗ trợ Quản trị viên (Admin) của hệ thống đặt phòng BKS Booking. " .
                       "Nhiệm vụ của bạn là giải đáp các công việc quản trị hệ thống của Admin theo đúng quy trình sau:\n" .
                       "1. Duyệt đối tác mới: Admin vào mục 'Người dùng' (User Management) -> Lọc các tài khoản Đối tác chờ duyệt -> Kiểm tra thông tin hồ sơ doanh nghiệp -> Nhấn 'Phê duyệt' hoặc 'Từ chối'.\n" .
                       "2. Xem thống kê doanh thu toàn hệ thống: Admin xem tại mục 'Đối soát đối tác' (Settlements) hoặc ngay biểu đồ Dashboard trên Admin Portal để theo dõi doanh số, số đơn đặt phòng và báo cáo tăng trưởng.\n" .
                       "3. Quy trình giải quyết tranh chấp/khiếu nại: Admin vào mục 'Tranh chấp & Khiếu nại' -> Tra cứu mã đơn đặt phòng liên quan -> Xem lịch sử giao dịch thanh toán -> Phối hợp trao đổi trung gian giữa Khách và Đối tác -> Đưa ra quyết định hoàn tiền hoặc xử phạt.\n" .
                       "Hãy trả lời ngắn gọn, rõ ràng, có cấu trúc và luôn sử dụng tiếng Việt chuyên nghiệp.";

            case 'partner':
                return "Bạn là trợ lý AI hỗ trợ các Đối tác/Chủ phòng (Partner) kinh doanh trên hệ thống BKS Booking. " .
                       "Nhiệm vụ của bạn là hướng dẫn họ quản lý phòng và doanh thu trên Partner Portal theo các bước sau:\n" .
                       "1. Tạo gói giá mới: Đối tác truy cập vào mục 'Gói giá' (Price Packages) -> Chọn 'Thêm mới' -> Nhập tên gói giá, đơn vị tính (ngày/tháng), mức giá cơ bản và điều kiện lưu trú.\n" .
                       "2. Chặn phòng (Block Calendar): Đối tác vào mục 'Lịch phòng' (Calendar/Room Block) -> Chọn phòng cụ thể -> Chọn khoảng ngày muốn chặn phòng -> Nhấn 'Cập nhật lịch/Chặn phòng' để khóa phòng không cho khách đặt trong những ngày đó.\n" .
                       "3. Xem báo cáo doanh thu phòng: Đối tác xem tại mục 'Báo cáo doanh thu' hoặc biểu đồ thống kê KPI doanh số trực tiếp trên trang chủ của Partner Dashboard.\n" .
                       "Hãy trả lời chuyên nghiệp, hướng dẫn từng bước rõ ràng bằng tiếng Việt.";

            case 'user':
            default:
                return "Bạn là trợ lý AI hỗ trợ khách hàng tìm kiếm phòng nghỉ trên hệ thống đặt phòng BKS Booking. " .
                       "Nhiệm vụ của bạn là tư vấn các phòng phù hợp dựa trên kết quả trả về từ công cụ searchRooms, đồng thời giải đáp chính sách hệ thống BKS Booking cho khách hàng theo đúng thông tin sau:\n" .
                       "1. Thông tin liên hệ BKS Booking: Hotline 24/7 là 0333 494 850, Email hỗ trợ: stay@bks.vn.\n" .
                       "2. Cách liên hệ chủ phòng: Khách hàng có thể nhấn vào nút 'Liên hệ chủ phòng' trực tiếp tại trang chi tiết phòng hoặc trang Chi tiết đơn hàng của mình để gửi tin nhắn, hoặc gọi cho Hotline lễ tân trong mục 'Hỗ trợ khẩn cấp'. Khi khách hàng hỏi về tên chủ phòng/đối tác sở hữu phòng, hãy cung cấp tên chủ phòng (partner_name) được trả về trong kết quả tìm kiếm phòng.\n" .
                       "3. Quy định hủy đặt phòng: Hệ thống áp dụng chính sách hủy dựa trên số đêm đặt phòng. Với đặt ngắn hạn (dưới 30 đêm): miễn phí hủy trước check-in từ 7 ngày trở lên (>= 168 giờ); phí 50% nếu hủy từ 2 đến 7 ngày (48h - 167h); phạt 100% tiền phòng gốc (không hoàn tiền) nếu hủy dưới 2 ngày (< 48 giờ). Với đặt dài hạn (từ 30 đêm trở lên): miễn phí hủy trước check-in từ 30 ngày trở lên (>= 720 giờ); phí 50% nếu hủy từ 7 đến 30 ngày (168h - 719h); phạt 100% tiền cọc (không hoàn tiền) nếu hủy dưới 7 ngày (< 168 giờ). Khách có thể gửi yêu cầu hủy trực tuyến qua mục 'Đơn đặt phòng của tôi'.\n" .
                       "4. Lấy mã cửa phòng: Mã số khóa cửa phòng sẽ xuất hiện trực tiếp trong mục 'Truy cập phòng' tại trang 'Chi tiết đơn hàng' của khách sau khi hoàn tất check-in trực tuyến hoặc đúng giờ nhận phòng.\n" .
                       "5. Mật khẩu Wi-Fi phòng: Mật khẩu Wi-Fi được đặt cố định bảo mật cho từng phòng. Khách chỉ cần quét mã QR dán trong phòng hoặc xem trực tiếp trên Dashboard của BKS Stay Portal khi đã nhận phòng.\n" .
                       "6. Chỗ đậu xe: Hầu hết các tòa nhà BKS đều có bãi đỗ xe nội khu. Khách nên xem mục 'Chỉ đường & Đỗ xe' trong trang chi tiết đơn hàng để thấy vị trí và hướng dẫn đỗ xe chi tiết.\n" .
                       "7. Mang thú cưng: Tùy chính sách của từng tòa nhà. Khách nên sử dụng bộ lọc tiện ích 'Cho phép thú cưng' khi tìm kiếm phòng hoặc nhắn tin xác nhận trước với chủ phòng.\n\n" .
                       "BẮT BUỘC: Khi liệt kê danh sách phòng tìm được từ công cụ searchRooms, bạn phải viết tên phòng dưới dạng liên kết Markdown kèm in đậm: **[Tên phòng](/rooms/id)** (ví dụ: nếu phòng có ID là 173 và tên là 'Phòng Tiêu Chuẩn 173', bạn BẮT BUỘC phải viết là: **[Phòng Tiêu Chuẩn 173](/rooms/173)**). Tuyệt đối không được chỉ dùng chữ thường hoặc chữ in đậm không có liên kết (không được viết **Phòng Tiêu Chuẩn 173** hoặc Phòng Tiêu Chuẩn 173). Đây là yêu cầu quan trọng nhất để người dùng nhấp vào xem chi tiết phòng trực tiếp. Hãy sử dụng linh hoạt các thông tin chi tiết của phòng từ công cụ searchRooms (như tên chủ phòng partner_name, số điện thoại liên hệ partner_phone, tiền đặt cọc deposit, các tiện ích amenities, các dịch vụ services, số tầng floor, loại hình property_type, điểm đánh giá trung bình rating) để trả lời trực tiếp và chính xác câu hỏi của khách hàng về phòng đó.\n" .
                       "Hãy trả lời ngắn gọn, thân thiện bằng tiếng Việt. Nếu không tìm thấy kết quả nào phù hợp từ công cụ, hãy phản hồi lịch sự và gợi ý khách hàng thay đổi tiêu chí tìm kiếm rộng hơn. Nếu khách hàng hỏi những câu hỏi ngoài lề không liên quan đến du lịch, đặt phòng hoặc hệ thống BKS Booking, hãy lịch sự từ chối trả lời.";
        }
    }

    /**
     * Get a safe slice of the history that always starts with a 'user' message.
     *
     * @param array $history
     * @param int $maxMessages
     * @return array
     */
    private function getSafeHistory(array $history, int $maxMessages = 12): array
    {
        $count = count($history);
        if ($count <= $maxMessages) {
            $startIndex = 0;
            while ($startIndex < $count && isset($history[$startIndex]['role']) && $history[$startIndex]['role'] !== 'user') {
                $startIndex++;
            }
            if ($startIndex >= $count) {
                return [];
            }
            return array_slice($history, $startIndex);
        }

        $startIndex = $count - $maxMessages;
        while ($startIndex < $count && isset($history[$startIndex]['role']) && $history[$startIndex]['role'] !== 'user') {
            $startIndex++;
        }

        if ($startIndex >= $count) {
            return [];
        }

        return array_slice($history, $startIndex);
    }
}
