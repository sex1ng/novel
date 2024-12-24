<?php

namespace App\Http\Controllers;


use App\Commands\AlipayMerchantPay;
use App\Exceptions\WithdrawException;
use App\Models\Book;
use App\Models\Category;
use App\Models\Chapter;
use App\Models\Content;
use App\Models\User;
use App\Models\UserBook;
use App\Services\Business\BaseService;
use App\Services\Business\ResponseService;
use App\Services\External\SnowFlake\SnowFlake;
use Carbon\Carbon;
use Curl\Curl;
use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Storage;
use OnlineConfig;

class HomeController extends Controller
{
    private $resp;

    public function __construct(ResponseService $resp)
    {
        $this->resp = $resp;
    }

    public function showWelcome(Request $request) {
        return view('welcome');
    }
}