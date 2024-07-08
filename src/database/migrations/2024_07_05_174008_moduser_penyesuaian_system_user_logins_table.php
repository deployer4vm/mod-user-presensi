<?php

use App\Base\Traits\MigrateDataTenant;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModuserPenyesuaianSystemUserLoginsTable extends Migration
{
    use MigrateDataTenant;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (config('AppConfig.system.multitenant.active', false) && $this->tenantMigrateMode() == false) {
            Schema::table('moduser_system_user_logins', function (Blueprint $table) {
                $table->dropColumn('user_id');
                $table->dropColumn('username');
                $table->dropColumn('password');
            });

            Schema::table('moduser_system_user_logins', function (Blueprint $table) {
                $table->integer('count')->after('ip_address')->default(0);
                $table->text('description')->after('status')->nullable();
            });
        }

        $this->tablePerTenant('moduser_system_user_logins', function (Blueprint $table) {
            $table->dropColumn('user_id');
            $table->dropColumn('username');
            $table->dropColumn('password');
        }, 'user_id', true);

        $this->tablePerTenant('moduser_system_user_logins', function (Blueprint $table) {
            $table->integer('count')->after('ip_address')->default(0);
            $table->text('description')->after('status')->nullable();
        }, 'count');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (config('AppConfig.system.multitenant.active', false) && $this->tenantMigrateMode() == false) {
            Schema::table('moduser_system_user_logins', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id');
                $table->string('username');
                $table->string('password');
            });

            Schema::table('moduser_system_user_logins', function (Blueprint $table) {
                $table->dropColumn('count');
                $table->dropColumn('description');
            });
        }

        $this->tablePerTenant('moduser_system_user_logins', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id');
            $table->string('username');
            $table->string('password');
        }, 'user_id', true);

        $this->tablePerTenant('moduser_system_user_logins', function (Blueprint $table) {
            $table->dropColumn('count');
            $table->dropColumn('description');
        }, 'count');
    }
};
