<?php

return [
    'name' => 'User Management',
    'my_profile' => 'My Profile',
    'field_caption' => [ //digunakan di form dan list (kolom) manage user
        'avatar' => 'Avatar',
        'name' => 'Nama',
        'username' => 'Username',
        'email' => 'Email',
        'phone' => 'Phone',
        'pin' => 'PIN',
        'confirm_pin' => 'Konfirmasi PIN',
        'password' => 'Password',
        'confirm_password' => 'Konfirmasi Password',
        'role' => 'Role',
        'status' => 'Status',
        'user_type' => 'Tipe User',
        'user_group_id' => 'User Group',
        'otp_channel' => 'Pengiriman OTP',
        'otp_channel_select_1' => 'Email',
        'otp_channel_select_2' => 'SMS',
        'otp_channel_select_3' => 'WhatsApp',
        'status_item' => [
            'guest' => 'User Baru',
            'active' => 'Aktif',
            'banned' => 'Diblokir'
        ],
        'generate_token' => 'Generate Token',
        'h2h_key' => 'API H2H Key',
        'client_key' => 'API Client Key',
        'client_secret' => 'API Client Secret',
        'copy_key' => 'Salin :key'
    ],
    'field_description' => [
        'password' => 'Password minimal 8 karakter',
        'pin' => '(6 Digit)',
    ],
    'userlist' => [
        'add_new_user' => 'Tambah User'
    ],
    'userform' => [
        'form_add_caption' => 'Tambah user baru',
        'tab_account_caption' => 'Account',
        'tab_profile_caption' => 'Profile'
    ],
    'alert' => [
        'password_not_match' => 'Password tidak sama',
        'pin_not_match' => 'PIN tidak sama',
        'email_invalid' => 'Email tidak valid',
    ],
    'systemuser' => [
        'role_list' => 'Manage Role System ',
    ],
    'form_profile' => [
        'label' => [
            'tab' => [
                'data' => 'Data',
                'password' => 'Change password',
                'pin' => 'Set Pin',
            ],
        ],
        'input_caption' => [
            
        ],
        'field_description' => [
            
        ],
        'alert' => [ 

        ],
    ],
    
    // user group
    'user_group' => [
        'name' => 'Manage User Group',
        'data_group' => [
            'name' => 'User Group',
            'field_name' => [
                'code' => 'Kode',
                'name' => 'Nama',
                'description' => 'Keterangan',
                'locked_data_mode' => 'Accessibility',
            ],
            'label' => [
                'locked_data_mode_0' => 'Public',
                'locked_data_mode_1' => 'Tidak bisa didelete',
                'locked_data_mode_2' => 'Tidak bisa diedit dan didelete',
            ],
        ],
        'table_group' => [
            'name' => 'List User Group'
        ],
        'form_group' => [
            'name' => 'Form User Group',
            'input_description' => [
                'code' => 'Masukan kode...',
                'name' => 'Masukan nama...',
                'description' => 'Masukan keterangan (opsional)...'
            ],
        ],
    ]
];