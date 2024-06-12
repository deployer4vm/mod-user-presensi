<?php

use App\Base\Traits\MigrateDataTenant;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    use MigrateDataTenant;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (config('AppConfig.system.multitenant.active', false) && $this->tenantMigrateMode() == false) {
            if (!Schema::hasColumn('moduser_users', 'role_level')) {
                Schema::table('moduser_users', function (Blueprint $table) {
                    $table->text('role_level')->after('role')->nullable();
                });
            }
        }

        $this->tablePerTenant('moduser_users', function (Blueprint $table) {
            $table->text('role_level')->after('role')->nullable();
        }, 'role_level');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('moduser_users', 'role_level')) {
            Schema::table('moduser_users', function (Blueprint $table) {
                $table->dropColumn('role_level');
            });
        }

        $this->tablePerTenant('moduser_users', function (Blueprint $table) {
            $table->dropColumn('role_level');
        });
    }
};
