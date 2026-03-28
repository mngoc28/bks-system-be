/**
 * @api {get} /api/v1/admin/partner/:id Get partner information
 * @apiName GetPartnerInforById
 * @apiGroup Partner
 * @apiVersion 1.0.0
 *
 * @apiHeader {String} Authorization Bearer token (JWT)
 * @apiDescription Protected endpoint - Requires authentication and admin,partner role
 *
 * @apiParam (Path) {Number} id partner ID
 *
 * @apiSampleRequest /api/v1/admin/partner/1
 *
 * @apiSuccessExample {json} Success-Reponse:
 * HTTP/1.1 200 Success
{
    "status": "success",
    "message": "Lấy chi tiết thông tin đối tác thành công!",
    "data": {
        "id": 1,
        "user_id": 2,
        "province_id": 46,
        "ward_id": 54,
        "address": "1757 Phố Đậu Quế Phước",
        "company_name": null,
        "phone": "0650-096-4058",
        "website": null,
        "description": "Chuyên cung cấp các dịch vụ bất động sản chất lượng cao, với đội ngũ nhân viên chuyên nghiệp và giàu kinh nghiệm. Cam kết mang đến những giải pháp tốt nhất cho khách hàng.",
        "image_1": "https://via.placeholder.com/800x600.png/0033aa?text=business+aut",
        "image_2": null,
        "image_3": "https://via.placeholder.com/800x600.png/009955?text=business+cumque",
        "created_by": 14,
        "updated_by": 20,
        "created_at": "2025-10-24T11:17:20.000000Z",
        "updated_at": "2025-10-23T11:17:20.000000Z",
        "user": {
            "id": 2,
            "name": "Nhân viên",
            "email": "partner@gmail.com",
            "is_email_verified": true,
            "email_verified_at": null,
            "token_expires_at": null,
            "role": "partner",
            "phone": "0791103085",
            "avatar": null,
            "status": "1",
            "created_by": 1,
            "updated_by": 1,
            "created_at": "2025-11-21T11:17:14.000000Z",
            "updated_at": "2025-11-21T11:17:14.000000Z",
            "sub": null
        },
        "province": {
            "id": 46,
            "name": "Quảng Bình",
            "name_en": "Quang Binh",
            "created_by": 1,
            "updated_by": 1,
            "created_at": "2025-11-21T11:17:19.000000Z",
            "updated_at": "2025-11-21T11:17:19.000000Z"
        },
        "ward": {
            "id": 54,
            "province_id": 46,
            "name": "Phường Phố Cam",
            "created_by": 20,
            "updated_by": 17,
            "created_at": "2025-11-07T11:17:20.000000Z",
            "updated_at": "2025-10-27T11:17:20.000000Z"
        }
    }
}
 */

/**
 * @api {get} /api/v1/admin/partner/search Search partner
 * @apiName SearchPartner
 * @apiGroup Partner
 * @apiVersion 1.0.0
 *
 * @apiHeader {String} Authorization Bearer token (JWT)
 * @apiDescription Protected endpoint - Requires authentication and admin,partner role
 *
 * @apiParam {String} [user_name] partner name
 * @apiParam {String} [province_name] Province name
 * @apiParam {String} [ward_name] Ward name
 * @apiParam {string} [phone] Phone number
 * @apiParam {String} [address] Address
 * @apiParam {Number} [page=1] Page number
 * @apiParam {Number} [per_page=10] Number of items per page
 *
 * @apiSampleRequest /api/v1/admin/partner/search
 *
 * @apiSuccessExample {json} Success-Reponse:
 * HTTP/1.1 200 Success
{
    "status": "success",
    "message": "Lấy danh sách thông tin đối tác thành công!",
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": 1,
                "user_id": 2,
                "province_id": 46,
                "ward_id": 54,
                "address": "1757 Phố Đậu Quế Phước",
                "company_name": null,
                "phone": "0650-096-4058",
                "website": null,
                "description": "Chuyên cung cấp các dịch vụ bất động sản chất lượng cao, với đội ngũ nhân viên chuyên nghiệp và giàu kinh nghiệm. Cam kết mang đến những giải pháp tốt nhất cho khách hàng.",
                "image_1": "https://via.placeholder.com/800x600.png/0033aa?text=business+aut",
                "image_2": null,
                "image_3": "https://via.placeholder.com/800x600.png/009955?text=business+cumque",
                "created_by": 14,
                "updated_by": 20,
                "created_at": "2025-10-24T11:17:20.000000Z",
                "updated_at": "2025-10-23T11:17:20.000000Z",
                "user_name": "Nhân viên",
                "province_name": "Quảng Bình",
                "ward_name": "Phường Phố Cam"
            },
            {
                "id": 2,
                "user_id": 3,
                "province_id": 25,
                "ward_id": 2,
                "address": "93 Phố Trân",
                "company_name": "Công ty CP Phát triển Delta",
                "phone": "84-62-087-0084",
                "website": "http://ma.com/ut-dolor-vero-dolorem-dolor-sit-fuga-ipsum",
                "description": "Với phương châm \"Khách hàng là trung tâm\", chúng tôi luôn nỗ lực mang đến những dịch vụ bất động sản chất lượng cao, đáp ứng mọi nhu cầu của khách hàng.",
                "image_1": "https://via.placeholder.com/800x600.png/00dd55?text=business+exercitationem",
                "image_2": null,
                "image_3": null,
                "created_by": 2,
                "updated_by": 1,
                "created_at": "2025-11-17T11:17:20.000000Z",
                "updated_at": "2025-11-18T11:17:20.000000Z",
                "user_name": "Bình Đông Thùy",
                "province_name": "Hà Giang",
                "ward_name": "Phường Nhân Chính"
            },
            {
                "id": 3,
                "user_id": 4,
                "province_id": 41,
                "ward_id": 87,
                "address": "3710 Phố Phùng Dũng Khánh",
                "company_name": "Công ty TNHH Quản lý Tài sản Alpha",
                "phone": "(0165)351-9884",
                "website": "https://vu.com/est-inventore-dignissimos-beatae-id-in.html",
                "description": null,
                "image_1": "https://via.placeholder.com/800x600.png/001133?text=business+qui",
                "image_2": "https://via.placeholder.com/800x600.png/00dd00?text=business+velit",
                "image_3": null,
                "created_by": 3,
                "updated_by": 8,
                "created_at": "2025-10-18T11:17:20.000000Z",
                "updated_at": "2025-11-02T11:17:20.000000Z",
                "user_name": "Khoa Cảnh",
                "province_name": "Nghệ An",
                "ward_name": "Phường Phố Mâu"
            },
            {
                "id": 4,
                "user_id": 5,
                "province_id": 4,
                "ward_id": 17,
                "address": "428 Phố Thân Nghi Huỳnh",
                "company_name": "Công ty TNHH Quản lý Chung cư PQR",
                "phone": "(0126) 575 7676",
                "website": null,
                "description": null,
                "image_1": "https://via.placeholder.com/800x600.png/009911?text=business+vel",
                "image_2": null,
                "image_3": "https://via.placeholder.com/800x600.png/00aa11?text=business+pariatur",
                "created_by": 20,
                "updated_by": 1,
                "created_at": "2025-10-28T11:17:20.000000Z",
                "updated_at": "2025-10-13T11:17:20.000000Z",
                "user_name": "Bác. Thân Luận",
                "province_name": "Đà Nẵng",
                "ward_name": "Phường Dương Nội"
            },
            {
                "id": 5,
                "user_id": 6,
                "province_id": 20,
                "ward_id": 57,
                "address": "8849 Phố Mã Vượng Võ",
                "company_name": null,
                "phone": "067-870-8864",
                "website": null,
                "description": "Công ty chuyên đầu tư và phát triển các dự án bất động sản quy mô lớn, tạo ra những không gian sống hiện đại và đẳng cấp cho cộng đồng.",
                "image_1": null,
                "image_2": "https://via.placeholder.com/800x600.png/001111?text=business+qui",
                "image_3": "https://via.placeholder.com/800x600.png/0077dd?text=business+ut",
                "created_by": 20,
                "updated_by": 1,
                "created_at": "2025-10-22T11:17:20.000000Z",
                "updated_at": "2025-10-18T11:17:20.000000Z",
                "user_name": "Khoa Cao Khiêm",
                "province_name": "Đắk Nông",
                "ward_name": "Phường Phố Liễu Hằng Giáp"
            },
            {
                "id": 6,
                "user_id": 7,
                "province_id": 27,
                "ward_id": 97,
                "address": "146 Phố Trang",
                "company_name": "Công ty CP Quản lý Tòa nhà GHI",
                "phone": "84-93-217-2242",
                "website": "http://www.nguyn.info.vn/ut-quisquam-qui-id-quas-quis.html",
                "description": null,
                "image_1": "https://via.placeholder.com/800x600.png/00dd33?text=business+facere",
                "image_2": null,
                "image_3": "https://via.placeholder.com/800x600.png/00bb33?text=business+aliquam",
                "created_by": 8,
                "updated_by": 2,
                "created_at": "2025-10-26T11:17:20.000000Z",
                "updated_at": "2025-10-17T11:17:20.000000Z",
                "user_name": "Bàng Thủy",
                "province_name": "Hà Tĩnh",
                "ward_name": "Phường Phố Giang Phi Bích"
            },
            {
                "id": 7,
                "user_id": 8,
                "province_id": 12,
                "ward_id": 75,
                "address": null,
                "company_name": "Công ty TNHH Bất động sản JKL",
                "phone": "+84-169-854-2146",
                "website": null,
                "description": null,
                "image_1": "https://via.placeholder.com/800x600.png/0022ff?text=business+facilis",
                "image_2": null,
                "image_3": null,
                "created_by": 19,
                "updated_by": 1,
                "created_at": "2025-11-08T11:17:20.000000Z",
                "updated_at": "2025-11-07T11:17:20.000000Z",
                "user_name": "Chú. Lữ Ngôn",
                "province_name": "Bến Tre",
                "ward_name": "Phường Phố Đào"
            },
            {
                "id": 8,
                "user_id": 9,
                "province_id": 42,
                "ward_id": 31,
                "address": "585 Phố Liễu Thiện Phi",
                "company_name": "Công ty TNHH Quản lý Tài sản Alpha",
                "phone": "(0121) 490 2151",
                "website": null,
                "description": null,
                "image_1": "https://via.placeholder.com/800x600.png/001155?text=business+mollitia",
                "image_2": null,
                "image_3": "https://via.placeholder.com/800x600.png/0055bb?text=business+totam",
                "created_by": 3,
                "updated_by": 19,
                "created_at": "2025-11-10T11:17:20.000000Z",
                "updated_at": "2025-10-17T11:17:20.000000Z",
                "user_name": "Chu Như",
                "province_name": "Ninh Bình",
                "ward_name": "Phường Tân Thuận Tây"
            },
            {
                "id": 9,
                "user_id": 10,
                "province_id": 25,
                "ward_id": 23,
                "address": "8 Phố Mang Hà Ly",
                "company_name": null,
                "phone": "094-978-6088",
                "website": null,
                "description": null,
                "image_1": null,
                "image_2": "https://via.placeholder.com/800x600.png/001199?text=business+incidunt",
                "image_3": null,
                "created_by": 7,
                "updated_by": 16,
                "created_at": "2025-11-20T11:17:20.000000Z",
                "updated_at": "2025-10-26T11:17:20.000000Z",
                "user_name": "Liễu Chiêu Thiên",
                "province_name": "Hà Giang",
                "ward_name": "Phường 6"
            },
            {
                "id": 10,
                "user_id": 11,
                "province_id": 20,
                "ward_id": 57,
                "address": "12 Phố Phúc",
                "company_name": "Công ty CP Phát triển Đô thị STU",
                "phone": "(84)(8)8513-1240",
                "website": null,
                "description": "Công ty phát triển đô thị hàng đầu, chuyên về các dự án nhà ở và hạ tầng đô thị. Tạo ra những không gian sống hiện đại, tiện nghi cho cộng đồng.",
                "image_1": null,
                "image_2": "https://via.placeholder.com/800x600.png/006655?text=business+accusantium",
                "image_3": null,
                "created_by": 6,
                "updated_by": 16,
                "created_at": "2025-11-05T11:17:20.000000Z",
                "updated_at": "2025-11-13T11:17:20.000000Z",
                "user_name": "Trang Xuyến Thùy",
                "province_name": "Đắk Nông",
                "ward_name": "Phường Phố Liễu Hằng Giáp"
            }
        ],
        "first_page_url": "http://localhost:8000/api/v1/admin/partner/search?page=1",
        "from": 1,
        "last_page": 3,
        "last_page_url": "http://localhost:8000/api/v1/admin/partner/search?page=3",
        "links": [
            {
                "url": null,
                "label": "&laquo; Trước",
                "active": false
            },
            {
                "url": "http://localhost:8000/api/v1/admin/partner/search?page=1",
                "label": "1",
                "active": true
            },
            {
                "url": "http://localhost:8000/api/v1/admin/partner/search?page=2",
                "label": "2",
                "active": false
            },
            {
                "url": "http://localhost:8000/api/v1/admin/partner/search?page=3",
                "label": "3",
                "active": false
            },
            {
                "url": "http://localhost:8000/api/v1/admin/partner/search?page=2",
                "label": "Sau &raquo;",
                "active": false
            }
        ],
        "next_page_url": "http://localhost:8000/api/v1/admin/partner/search?page=2",
        "path": "http://localhost:8000/api/v1/admin/partner/search",
        "per_page": 10,
        "prev_page_url": null,
        "to": 10,
        "total": 21
    }
}
*/

/**
 * @api {post} /api/v1/admin/partner/:id update partner information
 * @apiName UpdatePartner
 * @apiGroup Partner
 * @apiVersion 1.0.0
 *
 * @apiHeader {String} Authorization Bearer token (JWT)
 * @apiHeader {String} Content-Type multipart/form-data
 * @apiDescription Protected endpoint - Requires authentication and admin,partner role
 *
 * @apiParam (Path) {Number} id partner ID
 * 
 * @apiParam (Body) {String} [company_name] Company name (max 255 characters)
 * @apiParam (Body) {String} [phone] Phone number (max 20 characters, format: numbers and symbols)
 * @apiParam (Body) {String} [address] Address (max 500 characters)
 * @apiParam (Body) {String} [website] Website URL (must be valid URL, max 255 characters)
 * @apiParam (Body) {String} [description] Description (max 2000 characters)
 * @apiParam (Body) {File} [image_1] Image 1 file (jpeg, png, jpg, webp, max 5MB). Send empty to delete.
 * @apiParam (Body) {File} [image_2] Image 2 file (jpeg, png, jpg, webp, max 5MB). Send empty to delete.
 * @apiParam (Body) {File} [image_3] Image 3 file (jpeg, png, jpg, webp, max 5MB). Send empty to delete.
 *
 * @apiSampleRequest /api/v1/admin/partner/5
 *
 * @apiSuccessExample {json} Success-Reponse:
 * HTTP/1.1 200 Success
{
    "status": "success",
    "message": "Cập nhật thông tin đối tác thành công!",
    "data": {
        "id": 5,
        "user_id": 6,
        "province_id": 43,
        "ward_id": 228,
        "address": "123 hải thượng lán ông",
        "company_name": "Công ty tình bể bình",
        "phone": "0909999997",
        "website": "https://haithuonglanong.com",
        "description": "nhà thuốc đông y được ví như mẹ hiền",
        "image_1": null,
        "image_2": null,
        "image_3": "/storage/images/partner/5/C3aMLegGCN_1765428110.webp",
        "created_by": 13,
        "updated_by": 1,
        "created_at": "2025-11-23T03:18:38.000000Z",
        "updated_at": "2025-12-11T04:41:50.000000Z",
        "user": {
            "id": 6,
            "name": "Cụ. Thi Nhân Nghiệp",
            "email": "cu6929@gmail.com",
            "is_email_verified": true,
            "email_verified_at": null,
            "token_expires_at": null,
            "role": "partner",
            "phone": "0705699845",
            "avatar": null,
            "status": "1",
            "created_by": 1,
            "updated_by": 1,
            "created_at": "2025-12-09T03:18:33.000000Z",
            "updated_at": "2025-12-09T03:18:33.000000Z",
            "sub": null
        },
        "province": {
            "id": 43,
            "name": "Ninh Thuận",
            "name_en": "Ninh Thuan",
            "created_by": 1,
            "updated_by": 1,
            "created_at": "2025-12-09T03:18:38.000000Z",
            "updated_at": "2025-12-09T03:18:38.000000Z"
        },
        "ward": {
            "id": 228,
            "province_id": 60,
            "name": "Xã Bản Máy",
            "created_by": 1,
            "updated_by": 1,
            "created_at": "2025-12-09T03:18:38.000000Z",
            "updated_at": "2025-12-09T03:18:38.000000Z"
        }
    }
}
 */