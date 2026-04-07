<?php

class FormValidator {
    private array $rules = [];

    /**
     * Adds validation rules for a field
     *
     * @param string $field The name of the field
     * @param array $rules An array of validation rule functions
     */
    public function addRules(string $field, array $rules) {
        $this->rules[$field] = $rules;
    }

    /**
     * Validates form data using the defined rules
     *
     * @param array $formData The form data
     * @return array An array with validation results
     */
    public function validate(array $formData): array
    {
        $errors = [];

        foreach ($this->rules as $field => $rules) {
            $value = $formData[$field] ?? '';

            foreach ($rules as $rule) {
                $error = $rule($value);

                if ($error) {
                    $errors[$field] = $error;
                    break;
                }
            }
        }

        if (!empty($errors)) {
            return [
                'success' => false,
                'message' => 'Validation errors.',
                'errors' => $errors
            ];
        }

        return [
            'success' => true,
            'message' => 'The form is successfully processed.'
        ];
    }

    public static function rules(): array
    {
        return [
            'isNotEmpty' => function($value) {
                return empty($value) ? __('Обов’язкове поле.', 'panterrea_v1') : null;
            },
            'isEmail' => function($value) {
                return !filter_var($value, FILTER_VALIDATE_EMAIL) ? __('Неправильний email формат.', 'panterrea_v1') : null;
            },
            'isPhone' => function($value) {
                return !preg_match('/^\+38\(0\d{2}\)\d{3}-\d{2}-\d{2}$/', $value)
                    ? __('Некоректний формат введеного контактного номера.', 'panterrea_v1')
                    : null;
            },
            'minLength' => function($min) {
                return function($value) use ($min) {
                    return strlen($value) < $min ? sprintf(__('Мінімальна довжина: %d символів.', 'panterrea_v1'), $min) : null;
                };
            },
            'maxLength' => function($max) {
                return function($value) use ($max) {
                    return mb_strlen($value, 'UTF-8') > $max ? sprintf(__('Максимальна довжина: %d символів.', 'panterrea_v1'), $max) : null;
                };
            },
            'isAlpha' => function($value) {
                return !preg_match('/^\p{L}+(?:[ -]\p{L}+)*$/u', $value) ? __('Допустимі лише літери, дефіс або пробіл між словами.', 'panterrea_v1') : null;
            },
            'isOptionalAlpha' => function($value) {
                if (is_null($value) || trim($value) === '') {
                    return null;
                }

                return !preg_match('/^\p{L}+(?:[ -]\p{L}+)*$/u', $value)
                    ? __('Допустимі лише літери, дефіс або пробіл між словами.', 'panterrea_v1')
                    : null;
            },
            'isNumber' => function($value) {
                return !is_numeric($value) ? __('Допустимі лише цифри.', 'panterrea_v1') : null;
            },
            'isStrongPassword' => function($value) {
                $hasUpperCase = preg_match('/\p{Lu}/u', $value);
                $hasLowerCase = preg_match('/\p{Ll}/u', $value);
                $hasSpecialChar = preg_match('/[!@#$%^&*(),.?":{}|<>]/', $value);

                if (!$hasUpperCase) {
                    return __('Повинен містити принаймні одну велику літеру.', 'panterrea_v1');
                }
                if (!$hasLowerCase) {
                    return __('Повинен містити хоча б одну малу літеру.', 'panterrea_v1');
                }
                if (!$hasSpecialChar) {
                    return __('Повинен містити хоча б один спеціальний символ.', 'panterrea_v1');
                }

                return null;
            },
        ];
    }
}