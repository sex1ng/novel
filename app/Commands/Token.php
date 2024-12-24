<?php
namespace App\Commands;

use App\Models\UserToken;
use Lock;

class Token extends Command
{

	protected $data;

    /**
     * Token constructor.
     * @param array $data
     */
	public function __construct(array $data)
	{
		$this->data = $data;
	}

	/**
	 * Execute the command.
	 *
	 */
	public function handle()
	{
		$uid     = $this->data['uid'];
		$token   = $this->data['token'];
		$ex_time = $this->data['ex_time'];
		$app_id  = $this->data['app_id'];

		$key = 'Token:' . $uid . $app_id;
		Lock::granule($key, function () use($uid, $token, $ex_time, $app_id) {
            if (!$app_id) {
                $app_id = 20071;
            }
            // 全部统一到token_app表中中处理
            $data = [
                'ex_time' => $ex_time,
                'token' => $token
            ];

            UserToken::updateOrCreate([
                'uid'      => $uid,
                'app_id'   => $app_id
            ], $data);
		});
	}
}
