<?php


namespace App\Services\Business;


class ResponseService
{
    /**
     * @param mixed $data 返回数据
     * @param int $code 返回码
     * @param string $message 消息
     * @return mixed
     */
    public function response($data, $code = 200, $message = '')
    {
        return ['data' => $data, 'code' => $code, 'msg' => $message];
    }

    /**
     * @param string $message
     * @return mixed
     */
    public function successResponse($message = '操作成功')
    {
        return $this->response('', 200, $message);
    }

    /**
     * 返回数据
     * @param $data
     * @return mixed
     */
    public function returnData($data)
    {
        return $this->response($data, 200, '操作成功');
    }

    /**
     * 失败返回
     * @param $message
     * @param int $code
     * @return mixed
     */
    public function errorResponse($message, $code = 422)
    {
        return $this->response('', $code, $message);
    }
}