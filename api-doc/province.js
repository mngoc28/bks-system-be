/**
 * @api {post} /api/v1/admin/provinces Create a new province
 * @apiName CreateProvince
 * @apiGroup Provinces
 * @apiVersion 1.0.0
 *
 * @apiHeader {String} Authorization Bearer token (JWT)
 * @apiDescription Protected endpoint - Requires authentication and admin role
 *
 * @apiParam {String{1..100}} name Province name
 *
 * @apiSampleRequest /api/v1/admin/provinces
 *
 * @apiSuccessExample {json} Success-Reponse:
 * HTTP/1.1 201 Success
{
    "status": "success",
    "message": "Tạo tỉnh/thành phố thành công",
    "data": {
        "name": "Nghệ Tĩnh",
        "name_en": "nghe_tinh",
        "created_by": 1,
        "updated_by": 1,
        "updated_at": "2025-11-14T07:20:57.000000Z",
        "created_at": "2025-11-14T07:20:57.000000Z",
        "id": 66
    }
}
 */

/**
 * @api {put} /api/v1/admin/provinces/:id Update a province
 * @apiName UpdateProvince
 * @apiGroup Provinces
 * @apiVersion 1.0.0
 *
 * @apiHeader {String} Authorization Bearer token (JWT)
 * @apiDescription Protected endpoint - Requires authentication and admin role
 *
 * @apiParam (Path) {Number} id Province ID
 *
 * @apiParam {String{1..100}} [name] Province name
 *
 * @apiSampleRequest /api/v1/admin/provinces/1
 *
 * @apiSuccessExample {json} Success-Reponse:
 * HTTP/1.1 200 Success
{
    "status": "success",
    "message": "Cập nhật tỉnh/thành phố thành công",
    "data": {
        "id": 12,
        "name": "Hà Nội",
        "name_en": "ha_noi",
        "created_by": 1,
        "updated_by": 1,
        "created_at": "2025-11-13T09:19:44.000000Z",
        "updated_at": "2025-11-14T02:31:59.000000Z"
    }
}
 */

/**
 * @api {get} /api/v1/admin/provinces/:id Get province information
 * @apiName GetProvinceById
 * @apiGroup Provinces
 * @apiVersion 1.0.0
 *
 * @apiHeader {String} Authorization Bearer token (JWT)
 * @apiDescription Protected endpoint - Requires authentication and admin role
 *
 * @apiParam (Path) {Number} id Province ID
 *
 * @apiSampleRequest /api/v1/admin/provinces/1
 *
 * @apiSuccessExample {json} Success-Reponse:
 * HTTP/1.1 200 Success
{
    "status": "success",
    "message": "Lấy thông tin Tỉnh/Thành phố thành công.",
    "data": {
        "id": 63,
        "name": "Yên Bái",
        "name_en": "Yen Bai",
        "created_by": 1,
        "updated_by": 1,
        "created_at": "2025-11-13T09:19:44.000000Z",
        "updated_at": "2025-11-13T09:19:44.000000Z"
    }
}
 */

/**
 * @api {get} /api/v1/admin/provinces Search provinces
 * @apiName SearchProvinces
 * @apiGroup Provinces
 * @apiVersion 1.0.0
 *
 * @apiHeader {String} Authorization Bearer token (JWT)
 * @apiDescription Protected endpoint - Requires authentication and admin role
 *
 * @apiParam {String} [name] Province name
 * @apiParam {Number} [page=1] Page number
 * @apiParam {Number} [per_page=10] Number of items per page
 *
 * @apiSampleRequest /api/v1/admin/provinces
 *
 * @apiSuccessExample {json} Success-Reponse:
 * HTTP/1.1 200 Success
{
    "status": "success",
    "message": "province.messages.search_success",
    "data": {
        "success": true,
        "data": {
            "current_page": 1,
            "data": [
                {
                    "id": 1,
                    "name": "Hà Tĩnh1",
                    "name_en": "ha_tinh1",
                    "created_by": 1,
                    "updated_by": 1,
                    "created_at": "2025-11-13T09:19:44.000000Z",
                    "updated_at": "2025-11-13T15:34:12.000000Z"
                },
                {
                    "id": 2,
                    "name": "Hồ Chí Minh",
                    "name_en": "Ho Chi Minh",
                    "created_by": 1,
                    "updated_by": 1,
                    "created_at": "2025-11-13T09:19:44.000000Z",
                    "updated_at": "2025-11-13T09:19:44.000000Z"
                },
                {
                    "id": 3,
                    "name": "Hải Phòng",
                    "name_en": "Hai Phong",
                    "created_by": 1,
                    "updated_by": 1,
                    "created_at": "2025-11-13T09:19:44.000000Z",
                    "updated_at": "2025-11-13T09:19:44.000000Z"
                },
                {
                    "id": 4,
                    "name": "Đà Nẵng",
                    "name_en": "Da Nang",
                    "created_by": 1,
                    "updated_by": 1,
                    "created_at": "2025-11-13T09:19:44.000000Z",
                    "updated_at": "2025-11-13T09:19:44.000000Z"
                },
                {
                    "id": 5,
                    "name": "Cần Thơ",
                    "name_en": "Can Tho",
                    "created_by": 1,
                    "updated_by": 1,
                    "created_at": "2025-11-13T09:19:44.000000Z",
                    "updated_at": "2025-11-13T09:19:44.000000Z"
                },
                {
                    "id": 6,
                    "name": "An Giang",
                    "name_en": "An Giang",
                    "created_by": 1,
                    "updated_by": 1,
                    "created_at": "2025-11-13T09:19:44.000000Z",
                    "updated_at": "2025-11-13T09:19:44.000000Z"
                },
                {
                    "id": 7,
                    "name": "Bà Rịa - Vũng Tàu",
                    "name_en": "Ba Ria - Vung Tau",
                    "created_by": 1,
                    "updated_by": 1,
                    "created_at": "2025-11-13T09:19:44.000000Z",
                    "updated_at": "2025-11-13T09:19:44.000000Z"
                },
                {
                    "id": 8,
                    "name": "Bắc Giang",
                    "name_en": "Bac Giang",
                    "created_by": 1,
                    "updated_by": 1,
                    "created_at": "2025-11-13T09:19:44.000000Z",
                    "updated_at": "2025-11-13T09:19:44.000000Z"
                },
                {
                    "id": 9,
                    "name": "Bắc Kạn",
                    "name_en": "Bac Kan",
                    "created_by": 1,
                    "updated_by": 1,
                    "created_at": "2025-11-13T09:19:44.000000Z",
                    "updated_at": "2025-11-13T09:19:44.000000Z"
                },
                {
                    "id": 10,
                    "name": "Bạc Liêu",
                    "name_en": "Bac Lieu",
                    "created_by": 1,
                    "updated_by": 1,
                    "created_at": "2025-11-13T09:19:44.000000Z",
                    "updated_at": "2025-11-13T09:19:44.000000Z"
                }
            ],
            "first_page_url": "http://127.0.0.1:8000/api/v1/admin/provinces?page=1",
            "from": 1,
            "last_page": 8,
            "last_page_url": "http://127.0.0.1:8000/api/v1/admin/provinces?page=8",
            "links": [
                {
                    "url": null,
                    "label": "&laquo; Trước",
                    "active": false
                },
                {
                    "url": "http://127.0.0.1:8000/api/v1/admin/provinces?page=1",
                    "label": "1",
                    "active": true
                },
                {
                    "url": "http://127.0.0.1:8000/api/v1/admin/provinces?page=2",
                    "label": "2",
                    "active": false
                },
                {
                    "url": "http://127.0.0.1:8000/api/v1/admin/provinces?page=3",
                    "label": "3",
                    "active": false
                },
                {
                    "url": "http://127.0.0.1:8000/api/v1/admin/provinces?page=4",
                    "label": "4",
                    "active": false
                },
                {
                    "url": "http://127.0.0.1:8000/api/v1/admin/provinces?page=5",
                    "label": "5",
                    "active": false
                },
                {
                    "url": "http://127.0.0.1:8000/api/v1/admin/provinces?page=6",
                    "label": "6",
                    "active": false
                },
                {
                    "url": "http://127.0.0.1:8000/api/v1/admin/provinces?page=7",
                    "label": "7",
                    "active": false
                },
                {
                    "url": "http://127.0.0.1:8000/api/v1/admin/provinces?page=8",
                    "label": "8",
                    "active": false
                },
                {
                    "url": "http://127.0.0.1:8000/api/v1/admin/provinces?page=2",
                    "label": "Sau &raquo;",
                    "active": false
                }
            ],
            "next_page_url": "http://127.0.0.1:8000/api/v1/admin/provinces?page=2",
            "path": "http://127.0.0.1:8000/api/v1/admin/provinces",
            "per_page": 10,
            "prev_page_url": null,
            "to": 10,
            "total": 71
        },
        "message": "province.messages.search_success"
    }
}
*/

/**
 * @api {delete} /api/v1/admin/provinces/:id Delete a province
 * @apiName DeleteProvince
 * @apiGroup Provinces
 * @apiVersion 1.0.0
 *
 * @apiHeader {String} Authorization Bearer token (JWT)
 * @apiDescription Protected endpoint - Requires authentication and admin role
 *
 * @apiParam (Path) {Number} id Province ID
 *
 * @apiSampleRequest /api/v1/admin/provinces/1
 *
 * @apiSuccessExample {json} Success-Reponse:
 * HTTP/1.1 200 Success
{
    "status": "success",
    "message": "Tỉnh/Thành phố đã được xóa thành công.",
    "data": null
}
 */

/**
 * @api {get} /api/v1/admin/provinces/types Get all provinces types
 * @apiName GetAllProvincesTypes
 * @apiGroup Provinces
 * @apiVersion 1.0.0
 *
 * @apiHeader {String} Authorization Bearer token (JWT)
 * @apiDescription Protected endpoint - Requires authentication and admin role
 *
 * @apiSampleRequest /api/v1/admin/provinces/types
 *
 * @apiSuccessExample {json} Success-Reponse:
 * HTTP/1.1 200 Success
 * {
 * "status": "success",
    "message": "Lấy danh sách loại Tỉnh/Thành phố thành công.",
    "data": [
        {
            "id": 1,
            "name": "Hà Nội",
            "name_en": "Ha Noi"
        }
    ]
}
 */
