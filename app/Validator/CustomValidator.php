<?php
namespace App\Validator;

use Illuminate\Validation\Validator;
use Jxlwqq\IdValidator\IdValidator;

class CustomValidator extends Validator
{

    /**
     * 手机号验证
     */
    public function validateMobile($attribute, $value, $parameters)
    {
        if (preg_match('/^1[0-9]{10}$/', $value)) {
            return true;
        }
        return false;
    }

    /**
     * 密码验证
     */
    public function validatePwd($attribute, $value, $parameters)
    {
        //if (preg_match('/^[A-Za-z0-9_]{6,16}$/', $value)) {
        //20181022 修改为支持特殊字符
        if (preg_match('/^[A-Za-z0-9_#@!~%^&*\.()-="\':;|?<>]{6,16}$/', $value)) {
            return true;
        }
        return false;
    }

    /**
     * 姓名验证
     */
    public function validateName($attribute, $value, $parameters)
    {
        if (preg_match('/^[\x{4e00}-\x{9fa5}·]{2,20}$/u', $value)) {
            return true;
        }
        return false;
    }

    /**
     * 身份证
     */
    public function validateIdNumber($attribute, $value, $parameters)
    {
        $idValidator = new IdValidator();
        return $idValidator->isValid($value);
    }

}