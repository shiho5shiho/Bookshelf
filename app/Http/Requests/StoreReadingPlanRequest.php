<?php

namespace App\Http\Requests;

use App\Enums\ReadingPlanStatus;
use App\Models\ReadingPlan;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class StoreReadingPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'book_id' => ['required', 'integer', 'exists:books,id'],
            'target_date' => ['required', 'date', 'after_or_equal:today'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'book_id.required' => '書籍を選択してください',
            'book_id.exists' => '選択された書籍は存在しません',
            'target_date.required' => '期日を入力してください',
            'target_date.date' => '正しい日付を入力してください',
            'target_date.after_or_equal' => '期日は本日以降の日付を入力してください',
        ];
    }

    /**
     * 追加のバリデーション（重複制御）。
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $duplicated = ReadingPlan::query()
                ->where('user_id', $this->user()->id)
                ->where('book_id', $this->input('book_id'))
                ->where('status', ReadingPlanStatus::InProgress)
                ->exists();

            if ($duplicated) {
                $validator->errors()->add('book_id', 'この書籍は既に読書中の計画があります');
            }
        });
    }
}
