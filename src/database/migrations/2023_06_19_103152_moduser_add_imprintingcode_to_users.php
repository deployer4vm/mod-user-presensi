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
            if (!Schema::hasColumn('moduser_users', 'imprintingcode')) {
                Schema::table('moduser_users', function (Blueprint $table) {
                    $table->string('imprintingcode')->nullable()->comment('Password synapse lama')->after('password');
                });
            }
        }

        $this->tablePerTenant('moduser_users', function (Blueprint $table) {
            $table->string('imprintingcode')->nullable()->comment('Password synapse lama')->after('password');
        }, 'imprintingcode');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('moduser_users', function (Blueprint $table) {
            //
        });
    }
};
