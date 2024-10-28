<?php

return [
    'field_caption' => [
        'name' => 'Nama',
        'tenant' => 'Tenant',
        'tenant_group' => 'Tenant Group',
        'role_code' => 'Role Code',
        'level' => 'Level',
        'locked_data_mode' => 'Accessibility',
        'role_group_id' => 'Role Group',
        'role_type' => 'Role Type',
        'dashboard_type' => 'Dashboard',
        'datarule_id' => 'Datarule',
        'tenant_id' => 'Tenant',
        'is_global' => 'Global Config',
        'global_bypass_rule' => 'Rule bisa diedit di tenant',
        'global_bypass_datarule' => 'Datarule bisa diedit di tenant',
        'global_bypass_dashboard' => 'Dashboard bisa diedit di tenant',
        'global_bypass_notification' => 'Notification bisa diedit di tenant',
        
        'bypass_rule' => 'Replace Rule di tenant',
        'bypass_datarule' => 'Replace Pilihan Datarule di tenant',
        'bypass_dashboard' => 'Replace Pilihan Dashboard di tenant',
    ],
    'rolelist' => [
        'add_new_role' => 'Tambah Role'
    ],
    'roleform' => [
        'form_add_caption' => 'Tambah role baru',
        'tenant_group_select_all_group' => 'All tenant group',//text value 0 pada input select tenant_group
        'module' => 'Module',
        'rule' => 'Rule',
        'feature' => 'Feature',
        'create' => 'Create',
        'read' => 'Read',
        'update' => 'Update',
        'delete' => 'Delete',
        'has_access' => 'Has Access',
        'check_all' => 'Check All',
        'uncheck_all' => 'Uncheck All',
    ],
    'role'=>[
        'name'=>'Role',        
        'label' => [
            // 'global_config'=>'Global Config',
            // 'locked_data_mode_0' => 'Public',
            // 'locked_data_mode_1' => 'Tidak bisa didelete',
            // 'locked_data_mode_2' => 'Tidak bisa diedit dan didelete',
        ],
    ],

    // general lavel
    'label' => [
        'locked_data_mode_0' => 'Public',
        'locked_data_mode_1' => 'Tidak bisa didelete',
        'locked_data_mode_2' => 'Tidak bisa diedit dan didelete',
    ],

    // rule group
    'group' => [
        'name' => 'Manage Role Group',
        'data_group' => [
            'name' => 'Role Group',
            'field_name' => [
                'code' => 'Kode',
                'name' => 'Nama',
                'has_model' => 'Has Model',
                'model' => 'Model',
                'dashboard_type' => 'Tipe Dashboard',
                'can_selected_on_create' => 'Bisa Dipilih Saat Create User',
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
            'name' => 'List Role Group'
        ],
        'form_group' => [
            'name' => 'Form Role Group',
            'input_description' => [
                'code' => 'Masukan kode...',
                'name' => 'Masukan nama...',
                'has_model' => 'Masukan namespace model...',
                'description' => 'Masukan keterangan (opsional)...'
            ],
        ],
    ],

    // role level group
    'level_group' => [
        'name' => 'Manage Role Level Group',
        'data_level' => [
            'name' => 'Role Level Group',
            'field_name' => [
                'code' => 'Kode',
                'level_start' => 'Level Awal',
                'level_end' => 'Level Akhir',
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
        'table_level' => [
            'name' => 'List Role Level Group',
            'column_name' => [
                'level' => 'Level'
            ],
        ],
        'form_level' => [
            'name' => 'FormRole Level Group',
            'input_description' => [
                'code' => 'Masukan kode...',
                'level_start' => '',
                'level_end' => '',
                'name' => 'Masukan nama...',
                'description' => 'Masukan keterangan (opsional)...'
            ],
        ],
    ],
    
    'datarule'=>[
        'name'=>'Manage Data Rule',
        'data_datarule' => [
            'name' => 'Data Rule',
            'field_name' => [
                'code' => 'Kode',
                'name' => 'Nama',
                // 'has_model' => 'Has Model',
                // 'model' => 'Model',
                // 'dashboard_type' => 'Tipe Dashboard',
                // 'can_selected_on_create' => 'Bisa Dipilih Saat Create User',
                // 'description' => 'Keterangan',
                'locked_data_mode' => 'Accessibility',
            ],
            'label' => [
                // 'locked_data_mode_0' => 'Public',
                // 'locked_data_mode_1' => 'Tidak bisa didelete',
                // 'locked_data_mode_2' => 'Tidak bisa diedit dan didelete',
            ],
        ],
    ],
];