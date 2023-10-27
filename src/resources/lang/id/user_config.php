<?php

return [
    'name' => 'Config',

    // auth
    'form_auth' => [
        'name' => 'Auth Config',
        'label' => [
            'password' => 'Password',
            'otp' => 'OTP',
            'pin' => 'PIN',
            'login' => 'Login',
            'minute' => 'Menit'
        ],
        'input_caption' => [
            'password' => [
                'password_minlength' => 'Minimal Panjang Password'
            ],
            'otp' => [
                'otp_enabled' => 'Aktivasi OTP',
                'otp_digit' => 'Minimal Panjang Digit',
                'otp_timeout' => 'Masa Aktif OTP',
                'otp_channel' => 'Channel :',
                'otp_channel_email' => 'Email',
                'otp_channel_sms' => 'SMS',
                'otp_channel_wa' => 'Whatsapp',
            ],
            'pin' => [
                'pin' => 'PIN',
                'pin_digit' => 'Minimal Panjang Digit'
            ],
            'login' => [
                'login_rememberme' =>  'Remember Me',
                'login_forgotpassword' =>  'Forgot Password'
            ],
        ],
        'input_description' => [
            'password' => [
                'password_minlength' => 'Masukan minimal panjang password...',
            ],
            'otp' => [
                'otp_digit' => 'Masukan minimal panjang digit otp...',
                'otp_timeout' => 'Masukan masa aktif otp...'
            ],
            'pin' => [
                'pin_digit' => 'Masukan minimal panjang digit pin...'
            ],
        ],
    ],

    // registration
    'form_registration' => [
        'name' => 'Registration Config',
        'label' => [
            'general' => 'General',
            'tos' => 'Term Of Service',
        ],
        'input_caption' => [
            'general' => [
                'enable' => 'Self Registration',
                'auto_active' => 'Self Registration Auto Activate',
                'admin_send_activation_email' => 'Send Email Activation',
                'tos_confirm' => 'Required TOS (term of service)',
                'default_role_code' => 'Default Role Registrasi'
            ],
            'tos' => [
                'tos_content' => 'Konten'
            ],
        ],
        'input_description' => [
            'general' => [
                'default_role_code' => 'Pilih Role'
            ],
            'tos' => [
                'tos_content' => 'Masukan konten term of service...'
            ],
        ],
    ]
];