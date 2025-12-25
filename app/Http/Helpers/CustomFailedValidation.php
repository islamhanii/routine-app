<?php

namespace App\Http\Helpers;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

trait CustomFailedValidation
{
    protected function failedValidation(Validator $validator)
    {
        $response = $this->apiResponse(400, $this->getFirstError($validator));

        throw new HttpResponseException($response);
    }

    /*----------------------------------------------------------------------------------------------------*/

    private function getFirstError($validator)
    {
        $errors = $validator->errors()->messages();
        $value = array_key_first($errors);
        return $errors[$value][0];
    }
}
