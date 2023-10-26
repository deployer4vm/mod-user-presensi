<?php

use App\Base\Traits\MigrateDataTenant;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModuserUpdateOtpRelatedFieldToRelatedTable extends Migration
{
    use MigrateDataTenant;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (config('AppConfig.system.multitenant.active', false) && $this->tenantMigrateMode() == false) {
            if (!Schema::hasColumn('moduser_users', 'otp_channel')) {
                Schema::table('moduser_users', function (Blueprint $table) {
                    $table->unsignedTinyInteger('otp_channel')->default(1)->after('auth_password');
                });
            }
            if (!Schema::hasColumn('moduser_user_otp', 'channel')) {
                Schema::table('moduser_user_otp', function (Blueprint $table) {
                    $table->unsignedTinyInteger('channel')->default(1)->after('user_id');
                    $table->renameColumn('phone','recipient');
                });
            }         
        }

        $this->tablePerTenant('moduser_users', function (Blueprint $table) {
            $table->unsignedTinyInteger('otp_channel')->default(1)->after('auth_password');
        }, 'otp_channel');
        
        $this->tablePerTenant('moduser_user_otp', function (Blueprint $table) {
            $table->unsignedTinyInteger('channel')->default(1)->after('user_id');
            $table->renameColumn('phone','recipient');
        }, 'channel');

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        
    }
};
