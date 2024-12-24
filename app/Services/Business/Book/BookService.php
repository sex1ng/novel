<?php


namespace App\Services\Business\Book;

use App\Models\Book;
use App\Models\Category;
use App\Models\Chapter;
use App\Models\Content;
use App\Models\User;
use App\Models\UserBook;
use App\Models\UserReadRecord;
use App\Models\UserToken;
use App\Services\Business\BaseService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class BookService extends BaseService {

    /**
     * 获取书本通过书本ID
     * @param $book_id_arr
     * @return array
     */
    public function getBooksInfoByIds($book_id_arr, $cid = '') {
        $model = Book::query();
        if (!empty($cid)) {
            $model->where('cid', $cid);
        }
        $books = $model
            ->whereIn('book_id', $book_id_arr)
            ->select('book_id', 'book_name', 'author', 'cover', 'cid', 'description', 'keywords', 'score')
            ->with('category')
            ->get();

        $book_list = [];
        foreach ($books as $key => $item) {
            $book_list[$item->book_id]['book_id'] = $item->book_id;
            $book_list[$item->book_id]['name'] = $item->book_name;
            $book_list[$item->book_id]['icon'] = $item->cover;
            $book_list[$item->book_id]['author'] = $item->author;
            $book_list[$item->book_id]['category'] = $item->category ? $item->category->cat_name : "";
            $book_list[$item->book_id]['description'] = $item->description;
            $book_list[$item->book_id]['keywords'] = $item->keywords;
            $book_list[$item->book_id]['score'] = $item->score;
            $book_list[$item->book_id]['cid'] = $item->cid;
        }
        return $book_list;
    }

    /**
     * 获取书本所有章节
     * @param $params
     * @return mixed
     */
    public function getAllBookChapter($params) {
        $book_id = array_get($params, 'book_id');
        $limit = array_get($params, 'limit', 10);
        return Chapter::where('book_id', $book_id)
            ->select('chapter_id', 'chapter_title', 'words', 'display_order')
            ->paginate($limit);
    }

    /**
     * 获取章节内容
     * @param $params
     * @return array|false
     */
    public function getBookContent($params) {
        $book_id = array_get($params, 'book_id');
        $chapter_id = array_get($params, 'chapter_id');
        $uid = array_get($params, 'uid');

        $key = 'book:content:' . $book_id . ':' . $chapter_id;
        if ($cache = Cache::get($key)) {
            $return_data = $cache;
            $chapter_info = new Chapter();
            $chapter_info->chapter_id = $chapter_id;
            $chapter_info->chapter_title = $return_data['chapter_title'];
            $chapter_info->display_order = $return_data['display_order'];
        } else {
            $content = Content::where('book_id', $book_id)
                ->select('content', 'book_id', 'chapter_id')
                ->where('chapter_id', $chapter_id)
                ->first();
            if (!$content) {
                $this->errorMessage = '内容不存在';
                return false;
            }
            //书本总章节数
            $chapter_count = Chapter::where('book_id', $book_id)->count();
            $return_data = [
                'content' => $content->content,
                'book_id' => $content->book_id,
                'chapter_id' => $content->chapter_id,
                'chapter_count' => $chapter_count,
            ];
            $chapter_info = Chapter::where('book_id', $book_id)
                ->where('chapter_id', $chapter_id)
                ->first();
            //查询上一章和下一章的章节数据
            $on_a_info = Chapter::where('book_id', $book_id)->where('display_order', $chapter_info->display_order - 1)->first();
            $next_info = Chapter::where('book_id', $book_id)->where('display_order', $chapter_info->display_order + 1)->first();
            $return_data['chapter_title'] = $chapter_info->chapter_title ?? '未知';
            $return_data['display_order'] = $chapter_info->display_order ?? 0;
            $return_data['on_a_chapter_id'] = $on_a_info->chapter_id ?? 0;
            $return_data['on_a_chapter_title'] = $on_a_info->chapter_title ?? '无';
            $return_data['next_chapter_id'] = $next_info->chapter_id ?? 0;
            $return_data['next_chapter_title'] = $next_info->chapter_title ?? '无';
            Cache::put($key, $return_data, 1);
        }

        //如果这本书是加入书架的话，要更新下最后阅读的章节
        $user_book = UserBook::where('uid', $uid)->where('book_id', $book_id)->first();
        if ($user_book && $chapter_info) {
            $user_book->last_chapter_id = $chapter_info->chapter_id;
            $user_book->last_display_order = $chapter_info->display_order;
            $user_book->save();
        }
        //记录用户阅读记录
        $user_read_record = UserReadRecord::where('uid', $uid)->where('book_id', $book_id)->first();
        if (!$user_read_record) {
            $user_read_record = new UserReadRecord();
            $user_read_record->uid = $uid;
            $user_read_record->book_id = $book_id;
        }
        $user_read_record->last_chapter_id = $chapter_info->chapter_id;
        $user_read_record->last_display_order = $chapter_info->display_order;
        $user_read_record->save();
        //是否加入书架
        $is_book_case = 1;
        $user_book = UserBook::where('uid', $uid)
            ->where('book_id', $book_id)
            ->where('status', 1)
            ->first();
        $user_book && $is_book_case = 2;

        $return_data['is_book_case'] = $is_book_case;
        return $return_data;
    }

    /**
     * 书本加入书架
     * @param $params
     * @return array|false
     */
    public function addBookToBookcase($params) {
        $uid = array_get($params, 'uid');
        $book_id = array_get($params, 'book_id');
        $book_case = UserBook::where('uid', $uid)->where('book_id', $book_id)->first();
        if ($book_case) {
            if ($book_case->status == 2) {
                $book_case->status = 1;
            }
        } else {
            $chapter_info = Chapter::where('book_id', $book_id)->orderBy('display_order', 'asc')->first();
            $book_case = new UserBook();
            $book_case->uid = $uid;
            $book_case->book_id = $book_id;
            $book_case->last_chapter_id = $chapter_info->chapter_id;
            $book_case->last_display_order = $chapter_info->display_order;
            $book_case->status = 1;
        }
        return $book_case->save();
    }

    /**
     * 书本移除书架
     * @param $params
     * @return mixed
     */
    public function removeBookToBookcase($params) {
        $uid = array_get($params, 'uid');
        $book_id = array_get($params, 'book_id');
        $book_case = UserBook::where('uid', $uid)->where('book_id', $book_id)->first();
        if ($book_case) {
            $book_case->status = 2;
            $book_case->save();
        }
        return $book_case;
    }

    /**
     * 更新打开书架时间
     * @param $params
     * @return void
     */
    public function setUserBookReadTime($params) {
        $uid = array_get($params, 'uid');
        $book_id = array_get($params, 'book_id');
        $book = UserBook::where('uid', $uid)
            ->where('book_id', $book_id)
            ->where('status', 1)
            ->first();
        $book->last_open_time = Carbon::now()->toDateTimeString();
        $book->save();
    }

    /**
     * 获取用户书架
     * @param $params
     * @return array
     */
    public function getUserBookCase($params) {
        $uid = array_get($params, 'uid');
        $user_books = UserBook::where('uid', $uid)->where('status', 1)->orderBy('last_open_time', 'desc')->orderBy('created_at', 'desc')->get();
        $last_book = UserBook::where('uid', $uid)->where('status', 1)->orderBy('updated_at', 'desc')->first();
        $user_books_data = [
            'book_list' => [],
            'total' => 0,
            'last_book' => []
        ];
        foreach ($user_books as $key => $item) {
            $book_info = Book::where('book_id', $item->book_id)->first();
            $user_books_data['book_list'][$key]['book_id'] = $item->book_id;
            $user_books_data['book_list'][$key]['last_chapter_id'] = $item->last_chapter_id;
            $user_books_data['book_list'][$key]['last_display_order'] = $item->last_display_order;
            $user_books_data['book_list'][$key]['total_chapters'] = $book_info->total_chapters;
            $user_books_data['book_list'][$key]['name'] = $book_info->book_name;
            $user_books_data['book_list'][$key]['icon'] = $book_info->cover;
            $user_books_data['book_list'][$key]['description'] = $book_info->description;
            $user_books_data['book_list'][$key]['score'] = $book_info->score;
            $user_books_data['book_list'][$key]['words'] = $book_info->words;
            $user_books_data['book_list'][$key]['author'] = $book_info->author;
            $category_info = Category::where('cid', $book_info->cid)->first();
            $user_books_data['book_list'][$key]['category'] = !empty($category_info->cat_name) ? $category_info->cat_name : '';
        }
        $user_books_data['total'] = count($user_books_data['book_list']);
        if ($last_book){
            $last_book_info = Book::where('book_id', $last_book->book_id)->first();
            $user_books_data['last_book']['book_id'] = $last_book->book_id;
            $user_books_data['last_book']['last_chapter_id'] = $last_book->last_chapter_id;
            $user_books_data['last_book']['last_display_order'] = $last_book->last_display_order;
            $user_books_data['last_book']['total_chapters'] = $last_book_info->total_chapters;
            $user_books_data['last_book']['name'] = $last_book_info->book_name;
            $user_books_data['last_book']['icon'] = $last_book_info->cover;
            $user_books_data['last_book']['description'] = $last_book_info->description;
            $user_books_data['last_book']['score'] = $last_book_info->score;
            $user_books_data['last_book']['words'] = $last_book_info->words;
            $user_books_data['last_book']['author'] = $last_book_info->author;
            $category_info = Category::where('cid', $last_book_info->cid)->first();
            $user_books_data['last_book']['category'] = !empty($category_info->cat_name) ? $category_info->cat_name : '';
        }


        return $user_books_data;
    }

    /**
     * 获取书本详情
     * @param $params
     * @return array|false
     */
    public function getBookInfo($params) {
        $uid = array_get($params, 'uid');
        $book_id = array_get($params, 'book_id');

        $book = Book::where('book_id', $book_id)->first();
        if (!$book) {
            $this->errorMessage = '书本不存在';
            return false;
        }

        $category = Category::where('cid', $book->cid)->first();
        if (!$category) {
            $category_name = '未知';
        } else {
            $category_name = $category->cat_name;
        }
        //是否加入书架
        $is_book_case = 1;
        $user_book = UserBook::where('uid', $uid)
            ->where('book_id', $book_id)
            ->where('status', 1)
            ->first();
        if ($user_book) {
            $is_book_case = 2;
        }
        //查询阅读记录，如果没有的话就返回第一章节的ID下去
        $user_read_record = UserReadRecord::where('uid', $uid)
            ->where('book_id', $book_id)
            ->first();
        if ($user_read_record) {
            $last_chapter_id = $user_read_record->last_chapter_id;
            $last_display_order = $user_read_record->last_display_order;
        } else {
            $chapter_info = Chapter::where('book_id', $book_id)->orderBy('display_order', 'asc')->first();
            $last_chapter_id = $chapter_info->chapter_id;
            $last_display_order = $chapter_info->display_order;
        }

        $return_data = [
            'book_id' => $book->book_id,
            'book_name' => $book->book_name,
            'author' => $book->author,
            'words' => $book->words,
            'icon' => $book->cover,
            'score' => $book->score,
            'price' => $book->price,
            'description' => $book->description,
            'status' => $book->status_text,
            'category' => $category_name,
            'last_display_order' => $last_display_order,
            'last_chapter_id' => $last_chapter_id,
            'is_book_case' => $is_book_case,
        ];
        return $return_data;
    }

    /**
     * 获取书本通过书本CID
     * @param $params
     * @return array|false
     */
    public function getBooksInfoByCid($params) {
        $cid = array_get($params, 'cid');
        $count = array_get($params, 'count');
        $books = Book::where('cid', $cid)
            ->where('enable', Book::ENABLE_TRUE)
            ->select('book_id', 'book_name', 'cover', 'description', 'keywords', 'score', 'words')
            ->inRandomOrder()
            ->take($count)
            ->get();
        $category = Category::where('cid', $cid)->select('cat_name')->first();

        $book_list = [];
        foreach ($books as $key => $item) {
            $book_list[$item->book_id]['book_id'] = $item->book_id;
            $book_list[$item->book_id]['name'] = $item->book_name;
            $book_list[$item->book_id]['icon'] = $item->cover;
            $book_list[$item->book_id]['description'] = $item->description;
            $book_list[$item->book_id]['keywords'] = $item->keywords;
            $book_list[$item->book_id]['score'] = $item->score;
            $book_list[$item->book_id]['words'] = $item->words;
            $book_list[$item->book_id]['category'] = isset($category->cat_name) ? $category->cat_name : '';
        }
        return $book_list;
    }

    /**
     * 获取书名
     * @param $book_id
     * @return array
     */
    public function getBookName($book_id)
    {
        return Book::query()
            ->where('book_id', $book_id)
            ->value('book_name');
    }
}