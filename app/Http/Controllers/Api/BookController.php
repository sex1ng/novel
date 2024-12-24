<?php

namespace App\Http\Controllers\Api;

use App\Models\Book;
use App\Services\Business\Book\BookService;
use App\Services\Business\ResponseService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class BookController extends Controller
{

    private $resp;
    private $bookService;

    public function __construct(BookService $bookService, ResponseService $resp)
    {
        $this->bookService = $bookService;
        $this->resp        = $resp;
    }

    /**
     * 批量获取书本信息
     * api/20220428001
     * @param  Request  $request
     * @return array|mixed
     */
    public function getBooksInfo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'book_ids' => 'required',
            'cid'      => '',
        ]);
        $book_ids  = $request->input('book_ids');
        if ( ! $book_ids) {
            $book_ids = Book::query()->pluck('book_id')->toArray();
        }
        $book_list = $this->bookService->getBooksInfoByIds($book_ids, $request->input('cid'));

        return $this->resp->returnData($book_list);
    }

    /**
     * 获取书本所有章节
     * api/20220428002
     * @param  Request  $request
     * @return array|mixed
     */
    public function getBookChapterAll(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'book_id' => 'required',
            'page'    => 'required',
        ]);

        if ($validator->fails()) {
            return $this->resp->response([], -100, '请求处理失败');
        }

        $chapter_list = $this->bookService->getAllBookChapter($request->all());
        $book_name    = $this->bookService->getBookName($request->input('book_id'));

        return $this->resp->returnData(array_merge($chapter_list->toArray(), ['book_name' => $book_name]));
    }

    /**
     * 获取章节内容
     * api/20220428003
     * @param  Request  $request
     * @return array|mixed
     */
    public function getBookContent(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'book_id'    => 'required',
            'chapter_id' => 'required',
            'uid'        => 'required',
        ]);

        if ($validator->fails()) {
            return $this->resp->response([], -100, '请求处理失败');
        }
        try {
            $res = $this->bookService->getBookContent($request->all());
            if ($res === false) {
                return $this->resp->errorResponse($this->bookService->getErrorMessage() ?: '获取文章内容失败');
            }

            return $this->resp->returnData($res);
        } catch (\Exception $e) {
            return $this->resp->errorResponse('获取文章内容失败..');
        }
    }

    /**
     * 书本加入书架
     * api/20220428004
     * @param  Request  $request
     * @return array|mixed
     */
    public function addBookToBookcase(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'uid'     => 'required',
            'book_id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->resp->response([], -100, '请求处理失败');
        }

        $this->bookService->addBookToBookcase($request->all());

        return $this->resp->successResponse();
    }

    /**
     * 书本移除书架
     * api/20220428005
     * @param  Request  $request
     * @return array|mixed
     */
    public function removeBookToBookcase(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'uid'     => 'required',
            'book_id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->resp->response([], -100, '请求处理失败');
        }

        $this->bookService->removeBookToBookcase($request->all());

        return $this->resp->successResponse();
    }

    /**
     * 上报用户打开书架时间
     * @param  Request  $request
     * @return array|mixed
     */
    public function setUserBookReadTime(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'uid'     => 'required',
            'book_id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->resp->response([], -100, '请求处理失败');
        }

        $this->bookService->setUserBookReadTime($request->all());

        return $this->resp->successResponse();
    }

    /**
     * 获取用户书架
     * api/20220428009
     * @param  Request  $request
     * @return array|mixed
     */
    public function getUserBookCase(Request $request) {
        $validator = Validator::make($request->all(), [
            'uid' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->resp->response([], -100, '请求处理失败');
        }

        $user_books_data = $this->bookService->getUserBookCase($request->all());

        return $this->resp->returnData($user_books_data);
    }

    /**
     * 获取书本详情
     * api/20220429001
     * @param  Request  $request
     * @return array|mixed
     */
    public function getBookInfo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'book_id' => 'required',
            'uid'     => 'required',
        ]);

        if ($validator->fails()) {
            return $this->resp->response([], -100, '请求处理失败');
        }

        try {
            $res = $this->bookService->getBookInfo($request->all());
            if ($res === false) {
                return $this->resp->errorResponse($this->bookService->getErrorMessage() ?: '获取书本信息失败');
            }

            return $this->resp->returnData($res);
        } catch (\Exception $e) {
            dd($e->getMessage());
            return $this->resp->errorResponse('获取书本信息失败..');
        }
    }

    /**
     * 获取分类详情
     * api/20220727001
     * @param  Request  $request
     * @return array|mixed
     */
    public function getBooksInfoByCid(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cid'   => 'required',
            'count' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->resp->response([], -100, '请求处理失败');
        }

        $book_list = $this->bookService->getBooksInfoByCid($request->all());

        return $this->resp->returnData($book_list);
    }

}