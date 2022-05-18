<?php

class Validator
{
    /**
     * The validation error messages.
     *
     * @var string[]
     */
    private $messages = [
        'required' => 'The :attribute field is required.',
        'string' => 'The :attribute must be a string.',
        'strong' => 'The :attribute is not strong enough. Try a combination of letters, numbers and symbols.',
        'min' => 'The :attribute must be at least :min characters.',
        'max' => 'The :attribute must be less :max characters.',
        'email' => 'The :attribute must be a valid email address.',
        'alpha_num' => 'The :attribute may only contain letters and numbers.',
        'confirmed' => 'The :attribute confirmation does not match.',
        'same' => 'The :attribute and :other must match.',
        'accepted' => 'The :attribute must be accepted.',
        'url' => 'The :attribute format is invalid.',
        'regex' => 'The :attribute format is invalid.',
        'ip' => 'The :attribute must be a valid IP address.',
        'boolean' => 'The :attribute field must be true or false.',
    ];

    /**
     * The data under validation.
     *
     * @var array
     */
    protected $data;

    /**
     * Validation errors
     * @var array
     */
    private $errors = array();

    /**
     * Create a new Validator instance.
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Run the validator's rules against its data.
     *
     * @param array $rules
     */
    public function validate(array $rules): void
    {
        foreach ($rules as $attribute_key => $attribute_value) {
            $rule = explode('|', $attribute_value);
            foreach ($rule as $_rule) {
                if (strpos($_rule, ':') !== false) {
                    [$rule, $value] = explode(':', $_rule);
                    call_user_func(array(__CLASS__, $rule), $attribute_key, $value);
                } else {
                    call_user_func(array(__CLASS__, $_rule), $attribute_key);
                }
            }
        }
    }

    /**
     * Replace the placeholders used in data keys.
     *
     * @param $attribute
     * @param $message
     * @param null $value
     * @return string
     */
    private function replacePlaceholders($attribute, $message, $value = null): string
    {
        preg_match_all('/:([^:\s]+)/', $message, $matches);
        return str_replace($matches[0], [$attribute, $value], $message);
    }

    /**
     * @param $attribute
     * @param $message
     * @param null $value
     */
    private function setError($attribute, $message, $value = null): void
    {
        $this->errors[$attribute][] = $this->replacePlaceholders($attribute, $message, $value);
    }

    /**
     * Validate that a required attribute exists.
     *
     * @param string $attribute
     */
    private function required(string $attribute): void
    {
        if (empty($this->data[$attribute])) {
            $this->setError($attribute, $this->messages['required']);
        }
    }

    /**
     * Validate the size of an attribute is greater than a minimum value.
     *
     * @param string $attribute
     * @param $value
     */
    private function min(string $attribute, $value): void
    {
        $input = $this->data[$attribute];
        if (!empty($input) && strlen($input) < $value) {
            $this->setError($attribute, $this->messages['min'], $value);
        }
    }

    /**
     * Validate the size of an attribute is less than a maximum value.
     *
     * @param string $attribute
     * @param $value
     */
    private function max(string $attribute, $value): void
    {
        $input = $this->data[$attribute];
        if (!empty($input) && strlen($input) >= $value) {
            $this->setError($attribute, $this->messages['max'], $value);
        }
    }

    /**
     * Validate that an attribute is a valid e-mail address.
     *
     * @param string $attribute
     */
    private function email(string $attribute): void
    {
        $input = $this->data[$attribute];
        if (!empty($input) && !filter_var($this->data[$attribute], FILTER_VALIDATE_EMAIL)) {
            $this->setError($attribute, $this->messages['email']);
        }
    }

    /**
     * Validate that an attribute contains only alphabetic characters.
     *
     * @param string $attribute
     */
    private function alfa(string $attribute): void
    {
        if (preg_match('/[^a-zA-Z]/', $this->data[$attribute])) {
            $this->setError($attribute, $this->messages['string']);
        }
    }

    /**
     * Validate that an attribute contains only alpha-numeric characters.
     *
     * @param string $attribute
     */
    private function alfa_num(string $attribute): void
    {
        if (!ctype_alnum($this->data[$attribute])) {
            $this->setError($attribute, $this->messages['alpha_num']);
        }
    }

    /**
     * Validate that an attribute contains al least one uppercase letter at least one lowercase letter and number
     *
     * @param string $attribute
     */
    private function strong(string $attribute): void
    {
        if (!preg_match('/^\S*(?=\S{8,})(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[\d])\S*$/', $this->data[$attribute])) {
            $this->setError($attribute, $this->messages['strong']);
        }
    }

    /**
     * Validate that an attribute has a matching confirmation.
     *
     * @param string $attribute
     */
    private function confirmed(string $attribute): void
    {
        if ($this->data[$attribute] !== $this->data['password_confirmation']) {
            $this->setError($attribute, $this->messages['confirmed']);
        }
    }

    /**
     * Validate that two attributes match.
     *
     * @param string $attribute
     * @param $value
     */
    private function same(string $attribute, $value): void
    {
        if ($this->data[$attribute] !== $this->data[$value]) {
            $this->setError($attribute, $this->messages['same'], $value);
        }
    }

    /**
     * Validate that an attribute was "accepted".
     *
     * This validation rule implies the attribute is "required".
     *
     * @param string $attribute
     */
    private function accepted(string $attribute): void
    {
        $haystack = ['1', 'yes', true, 'on'];
        if (!in_array($this->data[$attribute], $haystack)) {
            $this->setError($attribute, $this->messages['accepted']);
        }
    }

    /**
     * Validate that an attribute is a valid url.
     *
     * @param string $attribute
     */
    private function url(string $attribute): void
    {
        if (!filter_var($this->data[$attribute], FILTER_VALIDATE_URL)) {
            $this->setError($attribute, $this->messages['url']);
        }
    }

    /**
     * Validate that an attribute passes a regular expression check.
     *
     * @param string $attribute
     * @param string $pattern
     */
    private function regex(string $attribute, string $pattern): void
    {
        if (!filter_var($this->data[$attribute], FILTER_VALIDATE_REGEXP, array('options' => array('regexp' => $pattern)))) {
            $this->setError($attribute, $this->messages['regex']);
        }
    }

    /**
     * Validate that an attribute is a valid IP.
     *
     * @param string $attribute
     */
    private function ip(string $attribute): void
    {
        if (!filter_var($this->data[$attribute], FILTER_VALIDATE_IP)) {
            $this->setError($attribute, $this->messages['ip']);
        }
    }

    /**
     * Validate that an attribute is a boolean.
     *
     * @param string $attribute
     */
    private function boolean(string $attribute): void
    {
        if (!filter_var($this->data[$attribute], FILTER_VALIDATE_BOOLEAN)) {
            $this->setError($attribute, $this->messages['boolean']);
        }
    }

    /**
     * Determine if the validation rule passes.
     *
     * @return bool
     */
    public function passed(): bool
    {
        return empty($this->errors);
    }

    /**
     * Returns the validation errors.
     *
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Returns the flatten validation errors.
     *
     * @return array
     */
    public function all(): array
    {
        $return = array();
        array_walk_recursive($this->errors, function ($a) use (&$return) {
            $return[] = $a;
        });
        return $return;
    }

}


