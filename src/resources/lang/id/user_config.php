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
    ],

    // dashboard
    'form_dashboard' => [
        'name' => 'Dashboard Config',
        'label' => [
            'add_config' => 'Tambah Config',
            'feature' => 'Feature',
            'add_feature' => 'Tambah Feature',
            'content' => 'Content',
        ],
        'input_caption' => [
            'name' => 'Nama Dashboard',
            'description' => 'Deskripsi',
            'template' => 'Template',
            'template_code' => 'Template Code',
            'tenant' => 'Tenant',
            // 
            'type' => 'Tipe',
            'type_config' => 'Tipe Config',
            'type_config_options' => [
                'type_1' => 'Row',
                'type_2' => 'Column',
                'type_3' => 'Content Row',
                'type_4' => 'Content Column',
            ],
            'type_content' => 'Tipe Content',
            'type_content_options' => [
                'type_1' => 'Content Row',
                'type_2' => 'Content Column',
            ],
            //
            'content' => 'Content',
            'feature' => 'Feature',
        ],
    ],
];