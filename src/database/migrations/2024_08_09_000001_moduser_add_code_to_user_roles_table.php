<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Base\Traits\MigrateDataTenant;

class ModuserAddCodeToUserRolesTable extends Migration
{
    use MigrateDataTenant;
    
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        if (config('AppConfig.system.multitenant.active', false) && $this->tenantMigrateMode() == false) {
            if (!Schema::hasColumn('moduser_datarules', 'locked_data_mode')) {
                Schema::table('moduser_datarules', function (Blueprint $table) {
                    $table->unsignedTinyInteger('locked_data_mode')->default(0)->after('type_value');
                    $table->unsignedTinyInteger('is_global')->default(0)->after('locked_data_mode');
                });
            }
        }

        $this->tablePerTenant('moduser_datarules', function (Blueprint $table) {
            $table->unsignedTinyInteger('locked_data_mode')->default(0)->after('type_value');
            $table->unsignedTinyInteger('is_global')->default(0)->after('locked_data_mode');
        }, 'locked_data_mode');
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('moduser_datarules', 'locked_data_mode')) {
            Schema::table('moduser_datarules', function (Blueprint $table) {
                $table->dropColumn(['locked_data_mode']);
            });
        }

        $this->tablePerTenant('moduser_datarules', function (Blueprint $table) {
            $table->dropColumn(['locked_data_mode']);
        });
    }
}
