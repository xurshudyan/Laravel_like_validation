<?php

require_once 'Validator/Validator.php';

$data = [
    'user_name' => '',
    'email' => 'test@gmail.com',
    'password' => '123456789',
    'password_confirm' => '123456789'
];

$validator = new Validator($data);


$validator->validate([
    'user_name' => 'required|alfa|min:2|max:20',
    'email' => 'required|email',
    'password' => 'required|min:8|max:50|strong|same:password_confirm',
    "name" => 'required'
]);

print_r($validator->getErrors());

