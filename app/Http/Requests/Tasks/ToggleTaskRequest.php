<?php

namespace App\Http\Requests\Tasks;

use App\Http\Helpers\ApiResponse;
use App\Http\Helpers\CustomFailedValidation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ToggleTaskRequest extends FormRequest
{
    use ApiResponse, CustomFailedValidation;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'task_id' => [
                'required',
                Rule::exists('tasks', 'id')->where('user_id', Auth::id())
            ],
            'date' => 'required|date'
        ];
    }
}
