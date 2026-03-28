/**
 * @api {get} /api/v1/admin/services/search Get Services (Pagination + Search)
 * @apiName Get All Services
 * @apiGroup Services
 * @apiVersion 1.0.0
 *
 * @apiDescription Public endpoint - No authentication required
 *
 * @apiParam {String} [name]     Search by service name
 * @apiParam {String} [min_price]  Search by service min price
 * @apiParam {String} [max_price]  Search by service max price
 * @apiParam {Number} [page=1]   Current page number
 * @apiParam {Number} [per_page=10] Number of records per page
 *
 * @apiSampleRequest /api/v1/admin/services/search
 *
 * @apiSuccessExample {json} Success-Response:
 * HTTP/1.1 200 Success
 {
    "status": "success",
    "message": "Lấy dịch vụ thành công.",
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": 1,
                "name": "WiFi Internet tốc độ cao",
                "description": "Dịch vụ WiFi miễn phí tốc độ cao 100Mbps, phủ sóng toàn bộ tòa nhà. Hỗ trợ đa thiết bị, kết nối ổn định 24/7. Không giới hạn dung lượng, phù hợp cho công việc và giải trí.",
                "price": "0.00",
                "created_by": 1,
                "updated_by": 8,
                "created_at": "2025-10-15T11:17:21.000000Z",
                "updated_at": "2025-10-08T11:17:21.000000Z"
            },
            {
                "id": 2,
                "name": "Điện nước cơ bản",
                "description": "Dịch vụ điện nước cơ bản được bao gồm trong giá phòng. Hệ thống điện ổn định, nước nóng lạnh 24/7. Không giới hạn sử dụng trong phạm vi hợp lý.",
                "price": "0.00",
                "created_by": 11,
                "updated_by": 20,
                "created_at": "2025-10-23T11:17:21.000000Z",
                "updated_at": "2025-10-04T11:17:21.000000Z"
            },
            {
                "id": 3,
                "name": "Vệ sinh phòng hàng ngày",
                "description": "Dọn phòng hàng ngày từ 9h-11h sáng. Bao gồm: thay ga gối, dọn dẹp phòng, vệ sinh phòng tắm, thay khăn tắm, bổ sung đồ vệ sinh cá nhân. Dịch vụ miễn phí cho khách lưu trú.",
                "price": "0.00",
                "created_by": 9,
                "updated_by": 12,
                "created_at": "2025-11-04T11:17:21.000000Z",
                "updated_at": "2025-10-03T11:17:21.000000Z"
            },
            {
                "id": 4,
                "name": "Bảo vệ 24/7",
                "description": "Hệ thống bảo vệ chuyên nghiệp 24/7 với camera giám sát toàn bộ tòa nhà. Bảo vệ túc trực tại cổng, thang máy, khu vực chung. Đảm bảo an ninh và an toàn cho khách hàng.",
                "price": "0.00",
                "created_by": 14,
                "updated_by": 7,
                "created_at": "2025-10-17T11:17:21.000000Z",
                "updated_at": "2025-10-12T11:17:21.000000Z"
            },
            {
                "id": 5,
                "name": "Thang máy",
                "description": "Hệ thống thang máy hiện đại, hoạt động 24/7. Thang máy cao tốc, an toàn, có camera giám sát. Phục vụ tất cả các tầng, tiện lợi cho việc di chuyển.",
                "price": "0.00",
                "created_by": 16,
                "updated_by": 20,
                "created_at": "2025-10-15T11:17:21.000000Z",
                "updated_at": "2025-11-14T11:17:21.000000Z"
            },
            {
                "id": 6,
                "name": "Bãi đỗ xe",
                "description": "Bãi đỗ xe miễn phí cho khách lưu trú. Bãi đỗ xe tầng hầm và ngoài trời, có bảo vệ giám sát. Mỗi phòng được cấp 1 chỗ đỗ xe miễn phí.",
                "price": "0.00",
                "created_by": 9,
                "updated_by": 3,
                "created_at": "2025-10-19T11:17:21.000000Z",
                "updated_at": "2025-10-07T11:17:21.000000Z"
            },
            {
                "id": 7,
                "name": "Tư vấn du lịch",
                "description": "Dịch vụ tư vấn du lịch miễn phí tại quầy lễ tân. Tư vấn về các điểm tham quan, nhà hàng, quán cà phê, địa điểm vui chơi trong khu vực. Cung cấp bản đồ và hướng dẫn chi tiết.",
                "price": "0.00",
                "created_by": 19,
                "updated_by": 6,
                "created_at": "2025-10-20T11:17:21.000000Z",
                "updated_at": "2025-11-06T11:17:21.000000Z"
            },
            {
                "id": 8,
                "name": "Dịch vụ giặt ủi",
                "description": "Dịch vụ giặt ủi chuyên nghiệp. Lấy đồ vào buổi sáng (8h-10h), trả vào buổi chiều (16h-18h). Giặt khô, giặt ướt, ủi phẳng. Giá từ 50.000đ/kg, tối thiểu 2kg. Dịch vụ nhanh (24h) có phụ phí 30%.",
                "price": "50000.00",
                "created_by": 14,
                "updated_by": 22,
                "created_at": "2025-11-15T11:17:21.000000Z",
                "updated_at": "2025-10-18T11:17:21.000000Z"
            },
            {
                "id": 9,
                "name": "Đưa đón sân bay",
                "description": "Dịch vụ đưa đón sân bay chuyên nghiệp. Xe 4-7 chỗ, có người cầm bảng tên đón tại sân bay. Giá một chiều 350.000đ, khứ hồi 600.000đ. Xe 16 chỗ: một chiều 500.000đ, khứ hồi 900.000đ. Cần đặt trước ít nhất 24h.",
                "price": "350000.00",
                "created_by": 21,
                "updated_by": 15,
                "created_at": "2025-10-19T11:17:21.000000Z",
                "updated_at": "2025-10-07T11:17:21.000000Z"
            },
            {
                "id": 10,
                "name": "Bữa sáng buffet",
                "description": "Bữa sáng buffet phong phú từ 6h30-10h sáng hàng ngày. Menu đa dạng: món Á, món Âu, đồ uống, trái cây, bánh ngọt. Giá 150.000đ/người, trẻ em dưới 6 tuổi miễn phí, 6-12 tuổi giảm 50%. Có thể đặt theo ngày hoặc theo kỳ.",
                "price": "150000.00",
                "created_by": 9,
                "updated_by": 10,
                "created_at": "2025-10-22T11:17:21.000000Z",
                "updated_at": "2025-11-13T11:17:21.000000Z"
            },
            {
                "id": 11,
                "name": "Dịch vụ massage tại phòng",
                "description": "Dịch vụ massage chuyên nghiệp tại phòng. Massage body, foot massage, massage đầu. Thời gian 60 phút, giá 400.000đ. Thời gian 90 phút, giá 550.000đ. Cần đặt trước ít nhất 2 giờ. Có massage đôi, giá ưu đãi.",
                "price": "400000.00",
                "created_by": 5,
                "updated_by": 3,
                "created_at": "2025-11-12T11:17:21.000000Z",
                "updated_at": "2025-11-03T11:17:21.000000Z"
            },
            {
                "id": 12,
                "name": "Dịch vụ spa",
                "description": "Dịch vụ spa đầy đủ: tắm hơi, xông hơi, tắm bùn, chăm sóc da mặt, chăm sóc body. Gói cơ bản 800.000đ (90 phút), gói cao cấp 1.500.000đ (150 phút). Cần đặt trước ít nhất 1 ngày. Có gói dành cho cặp đôi.",
                "price": "800000.00",
                "created_by": 19,
                "updated_by": 18,
                "created_at": "2025-11-04T11:17:21.000000Z",
                "updated_at": "2025-10-17T11:17:21.000000Z"
            },
            {
                "id": 13,
                "name": "Phòng gym và fitness",
                "description": "Phòng gym hiện đại với đầy đủ thiết bị: máy chạy bộ, xe đạp, tạ, máy tập các nhóm cơ. Mở cửa 6h-22h hàng ngày. Vé ngày 100.000đ, vé tuần 500.000đ, vé tháng 1.500.000đ. Có huấn luyện viên cá nhân, giá riêng.",
                "price": "100000.00",
                "created_by": 1,
                "updated_by": 6,
                "created_at": "2025-10-28T11:17:21.000000Z",
                "updated_at": "2025-11-14T11:17:21.000000Z"
            },
            {
                "id": 14,
                "name": "Tour du lịch trong ngày",
                "description": "Tour du lịch trong ngày đến các điểm tham quan nổi tiếng. Bao gồm: xe đưa đón, hướng dẫn viên, vé tham quan, bữa trưa. Giá từ 500.000đ/người tùy địa điểm. Tour nhóm ưu đãi giảm 10-20%. Cần đặt trước ít nhất 1 ngày.",
                "price": "500000.00",
                "created_by": 4,
                "updated_by": 19,
                "created_at": "2025-10-27T11:17:21.000000Z",
                "updated_at": "2025-11-20T11:17:21.000000Z"
            },
            {
                "id": 15,
                "name": "Dịch vụ đặt xe taxi/Grab",
                "description": "Dịch vụ hỗ trợ đặt xe taxi, Grab, xe công nghệ. Quầy lễ tân hỗ trợ gọi xe 24/7. Không tính phí dịch vụ, khách chỉ trả phí vận chuyển theo bảng giá. Có thể đặt trước cho các chuyến đi xa.",
                "price": "0.00",
                "created_by": 1,
                "updated_by": 1,
                "created_at": "2025-11-06T11:17:21.000000Z",
                "updated_at": "2025-10-09T11:17:21.000000Z"
            }
        ],
        "first_page_url": "http://localhost:8000/api/v1/admin/services/search?page=1",
        "from": 1,
        "last_page": 2,
        "last_page_url": "http://localhost:8000/api/v1/admin/services/search?page=2",
        "links": [
            {
                "url": null,
                "label": "&laquo; Trước",
                "active": false
            },
            {
                "url": "http://localhost:8000/api/v1/admin/services/search?page=1",
                "label": "1",
                "active": true
            },
            {
                "url": "http://localhost:8000/api/v1/admin/services/search?page=2",
                "label": "2",
                "active": false
            },
            {
                "url": "http://localhost:8000/api/v1/admin/services/search?page=3",
                "label": "3",
                "active": false
            },
            {
                "url": "http://localhost:8000/api/v1/admin/services/search?page=2",
                "label": "Next &raquo;",
                "active": false
            }
        ],
        "next_page_url": "http://localhost:8000/api/v1/admin/services/search?page=2",
        "path": "http://localhost:8000/api/v1/admin/services/search",
        "per_page": 15,
        "prev_page_url": null,
        "to": 15,
        "total": 29
    }
}
 */

/**
 * @api {get} /api/v1/admin/services/:id Get Service Details
 * @apiName Get Service By Id
 * @apiGroup Services
 * @apiVersion 1.0.0
 *
 * @apiDescription Public endpoint - No authentication required
 *
 * @apiParam (Path) {Number} id Service ID
 *
 * @apiSampleRequest /api/v1/admin/services/:id
 *
 * @apiSuccessExample {json} Success-Response:
 * HTTP/1.1 200 Success
{
    "status": "success",
    "message": "Lấy dịch vụ thành công.",
    "data": {
        "id": 7,
        "name": "Tư vấn du lịch",
        "description": "Dịch vụ tư vấn du lịch miễn phí tại quầy lễ tân. Tư vấn về các điểm tham quan, nhà hàng, quán cà phê, địa điểm vui chơi trong khu vực. Cung cấp bản đồ và hướng dẫn chi tiết.",
        "price": "0.00",
        "created_by": 19,
        "updated_by": 6,
        "created_at": "2025-10-20T11:17:21.000000Z",
        "updated_at": "2025-11-06T11:17:21.000000Z"
    }
}
 */

/**
 * @api {post} /api/v1/admin/services Create a New Service
 * @apiName CreateService
 * @apiGroup Services
 * @apiVersion 1.0.0
 *
 * @apiHeader {String} Authorization Bearer token (JWT)
 * @apiDescription Protected endpoint - Requires authentication and admin/partner role
 *
 * @apiParam {String{1..150}} [name] Service name
 * @apiParam {String{0..100}} [price] Service price
 * @apiParam {String} [description] Service description

 *
 * @apiSampleRequest /api/v1/admin/services
 *
 * @apiSuccessExample {json} Success-Response:
 * HTTP/1.1 201 Success
{
    "status": "success",
    "message": "Tạo dịch vụ thành công.",
    "data": {
        "name": "Dịch vụ massage",
        "price": "300000.00",
        "description": "massage tận giường 24/24",
        "created_by": 1,
        "updated_at": "2025-11-26T07:27:50.000000Z",
        "created_at": "2025-11-26T07:27:50.000000Z",
        "id": 30
    }
}
 */

/**
 * @api {put} /api/v1/admin/services/:id Update a Service
 * @apiName UpdateService
 * @apiGroup Services
 * @apiVersion 1.0.0
 *
 * @apiHeader {String} Authorization Bearer token (JWT)
 * @apiDescription Protected endpoint - Requires authentication and admin/partner role
 *
 * @apiParam (Path) {Number} id Service ID
 *
 * @apiParam {String} [name] Service name
 * @apiParam {String} [price] Service price
 * @apiParam {String} [description] Service description

 *
 * @apiSampleRequest /api/v1/admin/services/1
 *
 * @apiSuccessExample {json} Success-Response:
 * HTTP/1.1 200 Success
{
    "status": "success",
    "message": "Cập nhật dịch vụ thành công.",
    "data": true
}
 */

/**
 * @api {delete} /api/v1/admin/services/:id Delete a Service
 * @apiName DeleteService
 * @apiGroup Services
 * @apiVersion 1.0.0
 *
 * @apiHeader {String} Authorization Bearer token (JWT)
 * @apiDescription Protected endpoint - Requires authentication and admin/partner role
 *
 * @apiParam (Path) {Number} id Service ID
 *
 * @apiSampleRequest /api/v1/admin/services/1
 *
 * @apiSuccessExample {json} Success-Response:
 * HTTP/1.1 200 Success
{
    "status": "success",
    "message": "Xóa dịch vụ thành công.",
    "data": null
}
 */
