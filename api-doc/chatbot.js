/**
 * @api {get} /api/v1/admin/chatbot Chatbot get list
 * @apiName Chatbot get list
 * @apiGroup  Chatbot
 * @apiPermission AuthenticatedUser
 *
 * @apiHeader {String} Authorization Bearer token
 *
 * @apiParam {string} content Tên câu hỏi
 * @apiParam {number} page Số trang
 * @apiParam {number} pagination Số lượng khóa học trên mỗi trang
 * @apiParam {string} sort_by Sắp xếp theo tên ('content', 'total_answers')
 * @apiParam {string} direction Sắp xếp theo tổng số lớp ('asc', 'desc')
 *
 * @apiSampleRequest /api/v1/admin/chatbot
 *
 * @apiSuccessExample {json} Success-Response:
HTTP/1.1 200 Success
{
    "status": "success",
    "message": "Get chatbot questions list success.",
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": 1,
                "content": "Bạn cần hỗ trợ về vấn đề gì?",
                "type": 0,
                "position_x": 100,
                "position_y": 100,
                "is_start_node": 0,
                "total_answers": 4
            }
        ],
        "first_page_url": "http://127.0.0.1:10000/api/v1/admin/chatbot?page=1",
        "from": 1,
        "last_page": 1,
        "last_page_url": "http://127.0.0.1:10000/api/v1/admin/chatbot?page=1",
        "links": [
            {
                "url": null,
                "label": "&laquo; Previous",
                "active": false
            },
            {
                "url": "http://127.0.0.1:10000/api/v1/admin/chatbot?page=1",
                "label": "1",
                "active": true
            },
            {
                "url": null,
                "label": "Next &raquo;",
                "active": false
            }
        ],
        "next_page_url": null,
        "path": "http://127.0.0.1:10000/api/v1/admin/chatbot",
        "per_page": 10,
        "prev_page_url": null,
        "to": 5,
        "total": 5
    }
}
*/

/**
 * @api {get} /api/v1/admin/chatbot/list-question-flow Chatbot list question flow
 * @apiName Chatbot list question flow
 * @apiGroup  Chatbot
 * @apiPermission AuthenticatedUser
 *
 * @apiHeader {String} Authorization Bearer token
 *
 * @apiSampleRequest /api/v1/admin/chatbot/list-question-flow
 *
 * @apiSuccessExample {json} Success-Response:
HTTP/1.1 200 Success
[
    {
        "id": 1,
        "content": "What type of support do you need?",
        "type": 0,
        "position_x": 120,
        "position_y": 60,
        "is_start_node": 1,
        "answers": [
            {
                "id": 3,
                "question_id": 1,
                "content": "Billing issue",
                "next_question_id": 4
            },
            {
                "id": 4,
                "question_id": 1,
                "content": "Technical problem",
                "next_question_id": 5
            }
        ]
    },
    {
        "id": 2,
        "content": "Need anything else?",
        "type": 0,
        "position_x": 200,
        "position_y": 120,
        "is_start_node": 0,
        "answers": [
            {
                "id": 5,
                "question_id": 2,
                "content": "Yes",
                "next_question_id": 6
            },
            {
                "id": 6,
                "question_id": 2,
                "content": "No",
                "next_question_id": null
            }
        ]
    }
]
*/

/**
 * @api {get} /api/v1/admin/chatbot/detail/{id} Chatbot get detail question
 * @apiName Chatbot get detail question
 * @apiGroup  Chatbot
 * @apiPermission AuthenticatedUser
 *
 * @apiHeader {String} Authorization Bearer token
 *
 * @apiSampleRequest /api/v1/admin/chatbot/detail/{id}
 *
 * @apiSuccessExample {json} Success-Response:
HTTP/1.1 200 Success
{
    "status": "success",
    "message": "Get detail chatbot question success.",
    "data": {
        "id": 1,
        "content": "question 1?",
        "type": 0,
        "position_x": 123,
        "position_y": 456,
        "is_start_node": 0,
        "answers": [
            {
                "id": 1,
                "question_id": 1,
                "content": "answers 1",
                "next_question_id": null
            },
            {
                "id": 2,
                "question_id": 1,
                "content": "answers 2",
                "next_question_id": 3
            },
            {
                "id": 3,
                "question_id": 1,
                "content": "answers 3",
                "next_question_id": 4
            }
        ]
    }
}
*/

/**
 * @api {get} /api/v1/common/chatbot/start-question Chatbot get start question
 * @apiName Chatbot get start question
 * @apiGroup  Chatbot
 *
 * @apiParam {Number} [type] Filter by question type.
 *
 * @apiSampleRequest /api/v1/common/chatbot/start-question
 *
 * @apiSuccessExample {json} Success-Response:
HTTP/1.1 200 Success
{
    "status": "success",
    "message": "Lấy luồng chatbot thành công.",
    "data": [
        {
            "id": 1,
            "content": "Sample question 1",
            "type": 0,
            "is_start_node": 1,
            "answers": [
                {
                    "id": 1,
                    "question_id": 1,
                    "content": "Answer A for question 1",
                    "next_question_id": 2
                },
                {
                    "id": 2,
                    "question_id": 1,
                    "content": "Answer B for question 1",
                    "next_question_id": null
                }
            ]
        },
        {
            "id": 2,
            "content": "Updated question text",
            "type": 0,
            "is_start_node": 1,
            "answers": [
                {
                    "id": 3,
                    "question_id": 2,
                    "content": "Answer A for question 2",
                    "next_question_id": 3
                },
                {
                    "id": 4,
                    "question_id": 2,
                    "content": "Answer B for question 2",
                    "next_question_id": null
                },
                {
                    "id": 12,
                    "question_id": 2,
                    "content": "Modified answer",
                    "next_question_id": null
                },
                {
                    "id": 44,
                    "question_id": 2,
                    "content": "New answer",
                    "next_question_id": 4
                },
                {
                    "id": 45,
                    "question_id": 2,
                    "content": "New answer",
                    "next_question_id": 4
                }
            ]
        }
    ]
}
*/

/**
 * @api {get} /api/v1/common/chatbot/next-question/{id} Chatbot get next question
 * @apiName Chatbot get next question
 * @apiGroup  Chatbot
 *
 * @apiParam {Number} id Answer identifier (*).
 * @apiParam {Number} [type] Filter by question type.
 *
 * @apiSampleRequest /api/v1/common/chatbot/next-question/{id}
 *
 * @apiParamExample {json} Request-Example:
 * {
 *   "type": 0
 * }
 *
 * @apiSuccessExample {json} Success-Response:
HTTP/1.1 200 Success
{
    "status": "success",
    "message": "Lấy luồng chatbot thành công.",
    "data": {
        "id": 2,
        "content": "Updated question text",
        "type": 0,
        "position_x": 20,
        "position_y": 30,
        "is_start_node": 1,
        "answers": [
            {
                "id": 3,
                "question_id": 2,
                "content": "Answer A for question 2",
                "next_question_id": 3
            },
            {
                "id": 4,
                "question_id": 2,
                "content": "Answer B for question 2",
                "next_question_id": null
            },
            {
                "id": 12,
                "question_id": 2,
                "content": "Modified answer",
                "next_question_id": null
            },
            {
                "id": 44,
                "question_id": 2,
                "content": "New answer",
                "next_question_id": 4
            },
            {
                "id": 45,
                "question_id": 2,
                "content": "New answer",
                "next_question_id": 4
            }
        ]
    }
}
*/

/**
 * @api {put} /api/v1/admin/chatbot/update-position/{id} Chatbot update position
 * @apiName Chatbot update position
 * @apiGroup  Chatbot
 * @apiPermission AuthenticatedUser
 *
 * @apiHeader {String} Authorization Bearer token
 *
 * @apiParam {Number} id Question identifier (*).
 * @apiBody {Number} position_x Horizontal coordinate (*).
 * @apiBody {Number} position_y Vertical coordinate (*).
 *
 * @apiSampleRequest /api/v1/admin/chatbot/update-position/{id}
 *
 * @apiParamExample {json} Request-Example:
 * {
 *   "position_x": 320,
 *   "position_y": 140
 * }
 *
 * @apiSuccessExample {json} Success-Response:
HTTP/1.1 200 Success
{
    "status": "success",
    "message": "Update chatbot question success.",
    "data": null
}
*/

/**
 * @api {put} /api/v1/admin/chatbot/update/{id} Chatbot update question
 * @apiName Chatbot update question
 * @apiGroup  Chatbot
 * @apiPermission AuthenticatedUser
 *
 * @apiHeader {String} Authorization Bearer token
 *
 * @apiParam {Number} id Question identifier (*).
 * @apiBody {String} content Question content (*).
 * @apiBody {Number} is_start_node Start question (0: No, 1: Yes) (*).
 * @apiBody {Object[]} answers Answer collection.
 * @apiBody {String="create","update","delete"} answers._action Action to perform on the answer.
 * @apiBody {Number} [answers.id] Answer identifier (required when `_action` is update or delete).
 * @apiBody {String} answers.content Answer content (*).
 * @apiBody {Number} [answers.next_question_id] Next question identifier.
 *
 * @apiSampleRequest /api/v1/admin/chatbot/update/{id}
 *
 * @apiParamExample {json} Request-Example:
 * {
 *   "content": "Updated question text",
 *   "is_start_node": 1,
 *   "answers": [
 *     {
 *       "_action": "update",
 *       "id": 3,
 *       "content": "Modified answer",
 *       "next_question_id": null
 *     },
 *     {
 *       "_action": "create",
 *       "content": "New answer",
 *       "next_question_id": 4
 *     }
 *   ]
 * }
 *
 * @apiSuccessExample {json} Success-Response:
HTTP/1.1 200 Success
{
    "status": "success",
    "message": "Update chatbot question success.",
    "data": null
}
*/

/**
 * @api {put} /api/v1/admin/chatbot/update-line-flow/{id} Chatbot update line flow
 * @apiName Chatbot update line flow
 * @apiGroup  Chatbot
 * @apiPermission AuthenticatedUser
 *
 * @apiHeader {String} Authorization Bearer token
 *
 * @apiParam {Number} id Answer identifier (*).
 * @apiBody {Number} answer_id Answer identifier (*).
 * @apiBody {Number} [next_question_id] Next question identifier.
 *
 * @apiSampleRequest /api/v1/admin/chatbot/update-line-flow/{id}
 *
 * @apiParamExample {json} Request-Example:
 * {
 *   "answer_id": 8,
 *   "next_question_id": 12
 * }
 *
 * @apiSuccessExample {json} Success-Response:
HTTP/1.1 200 Success
{
    "status": "success",
    "message": "Update chatbot question success.",
    "data": null
}
*/

/**
 * @api {post} /api/v1/admin/chatbot/create Chatbot create question
 * @apiName Chatbot create question
 * @apiGroup  Chatbot
 * @apiPermission AuthenticatedUser
 *
 * @apiHeader {String} Authorization Bearer token
 *
 * @apiParam {string} content Nội dung câu hỏi (*)
 * @apiParam {number} type Loại câu hỏi (0: Câu hỏi, 1: Câu trả lời) (*)
 * @apiParam {number} is_start_node Câu hỏi bắt đầu (0: Không, 1: Có) (*)
 * @apiBody {Object} answers[] {
        "answers": [
            {
                "content": "answer 1",
                "next_question_id": 1
            },
            {
                "content": "answer 2",
                "next_question_id": 1
            }
        ]
    }
 *
 * @apiSampleRequest /api/v1/admin/chatbot/create
 *
 * @apiSuccessExample {json} Success-Response:
HTTP/1.1 200 Success
{
    "status": "success",
    "message": "Create chatbot question success.",
    "data": null
}
*/

/**
 * @api {delete} /api/v1/admin/chatbot/delete/{id} Chatbot delete question
 * @apiName Chatbot delete question
 * @apiGroup  Chatbot
 * @apiPermission AuthenticatedUser
 *
 * @apiHeader {String} Authorization Bearer token
 *
 * @apiParam {Number} id Question identifier (*).
 *
 * @apiSampleRequest /api/v1/admin/chatbot/delete/{id}
 *
 * @apiSuccessExample {json} Success-Response:
HTTP/1.1 200 Success
{
    "status": "success",
    "message": "Delete chatbot question success.",
    "data": null
}
*/
