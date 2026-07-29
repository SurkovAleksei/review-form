<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'min:2',
                'max:100',
            ],
            'email' => [
                'required',
                'email',
                'max:255'
            ],
            'phone' => [
                'required',
                'string',
                'max:20',
                'regex:/^\+[0-9]{10,15}$/'
            ],
            'comment' => [
                'required',
                'string',
                'min:10',
                'max:5000'
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Поле "Имя" обязательно для заполнения',
            'name.min' => 'Имя должно содержать минимум 2 символа',
            'name.max' => 'Имя не должно превышать 100 символов',

            'email.required' => 'Поле "Email" обязательно для заполнения',
            'email.email' => 'Введите корректный email адрес',
            'email.max' => 'Email не должен превышать 255 символов',

            'phone.required' => 'Поле "Телефон" обязательно для заполнения',
            'phone.max' => 'Телефон не должен превышать 20 символов',
            'phone.regex' => 'Телефон должен содержать 10-15 цифр и начинаться с +',

            'comment.required' => 'Поле "Сообщение" обязательно для заполнения',
            'comment.min' => 'Сообщение должно содержать минимум 10 символов',
            'comment.max' => 'Сообщение не должно превышать 5000 символов'
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => $this->sanitizeName($this->name),
            'email' => strtolower(trim($this->email)),
            'phone' => $this->sanitizePhone($this->phone),
            'comment' => trim(strip_tags($this->comment))
        ]);
    }

    private function sanitizeName(?string $name): ?string
    {
        if (empty($name)) {
            return null;
        }

        $name = preg_replace('/<[^>]*>.*?<\/[^>]*>/', '', $name);

        $name = strip_tags($name);

        $name = trim($name);
        $name = preg_replace('/\s+/', ' ', $name);

        $name = preg_replace('/[^a-zA-Zа-яА-Я\s\-]/u', '', $name);

        if (empty($name) || trim($name) === '') {
            return null;
        }

        return $name;
    }

    private function sanitizePhone(?string $phone): ?string
    {
        if (empty($phone)) {
            return null;
        }

        $phone = preg_replace('/[^0-9+]/', '', $phone);

        if (str_starts_with($phone, '8')) {
            $phone = '+7' . substr($phone, 1);
        }

        if (!str_starts_with($phone, '+')) {
            $phone = '+' . $phone;
        }

        return $phone;
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $name = $this->input('name');

            if (empty($name)) {
                $validator->errors()->add('name', 'Имя обязательно для заполнения');
                return;
            }

            if (!preg_match('/^[a-zA-Zа-яА-Я\s\-]+$/u', $name)) {
                $validator->errors()->add('name', 'Имя может содержать только буквы и пробелы');
            }

            if (strlen($name) < 2) {
                $validator->errors()->add('name', 'Имя должно содержать минимум 2 символа');
            }
        });
    }
}
