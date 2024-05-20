<?php

use App\Base\Traits\MigrateDataTenant;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModuserAuthenticatorUpdateAuthLogsTable extends Migration
{
    use MigrateDataTenant;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // jika multitenant aktif dan sedang bukan eksekusi migration saat crate tenant
        if (config('AppConfig.system.multitenant.active', false) && $this->tenantMigrateMode() == false) {
            if (!Schema::hasColumn('moduser_auth_logs', 'type')) {
                Schema::table('moduser_auth_logs', function (Blueprint $table) {
                    $table->unsignedBigInteger('created_by')->default(0)->after('tenant_id');
                    $table->unsignedBigInteger('updated_by')->default(0)->after('created_by');
                    $table->unsignedBigInteger('request_id')->after('updated_by');
                    $table->unsignedTinyInteger('type')->after('request_id');
                    $table->text('data')->nullable()->change();

                    $table->dropColumn(['auth_by','user_id','rule_group','rule_key']);
                });
            }    
        }

        $this->tablePerTenant('moduser_auth_logs', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->default(0)->after('tenant_id');
            $table->unsignedBigInteger('updated_by')->default(0)->after('created_by');
            $table->unsignedBigInteger('request_id')->after('updated_by');
            $table->unsignedTinyInteger('type')->after('request_id');
            $table->text('data')->nullable()->change();

            $table->dropColumn(['auth_by','user_id','rule_group','rule_key']);
        }, 'type');
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        
    }
};
