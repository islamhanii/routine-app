<?php

namespace App\Http\Requests\Tasks;

use App\Http\Helpers\ApiResponse;
use App\Http\Helpers\CustomFailedValidation;
use App\Models\Task;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ReorderTaskRequest extends FormRequest
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
            'order' => 'required|array',
            'order.*.id' => [
                'required',
                Rule::exists('tasks', 'id')->where('user_id', Auth::id())
            ],
            'order.*.priority' => 'required|integer|min:1|max:' . Task::where('user_id', Auth::id())->count()
        ];
    }
}
