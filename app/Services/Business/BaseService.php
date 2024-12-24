<?php

namespace App\Services\Business;

use Illuminate\Foundation\Bus\DispatchesJobs;

class BaseService
{

    use DispatchesJobs;

    public $successMessage = '';

    public $errorMessage = '';

    public $errorCode = 422;

    public $app_id;
    public $version;

    public function __construct()
    {
        $this->app_id  = request()->header('ua-app-id', request()->input('app_id', ''));
        $this->version = request()->header('ua-app-version', request()->input('key', ''));
        if (empty($this->app_id)) {
            $this->app_id = strstr(request()->header('user-agent'), 'ios') !== false ? 20071 : 20071;
        }
    }

    public function getSuccessMessage()
    {
        return $this->successMessage;
    }

    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    public function getErrorCode()
    {
        return $this->errorCode;
    }

}