<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Base\Traits\MigrateDataTenant;

class RemoveEmailUniqueOnUsersTable extends Migration
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
            $schemaManager = Schema::connection(config('database.perTenant') . $tenant['id'])->getConnection()
                ->getDoctrineSchemaManager();
            $indexesFound  = $schemaManager->listTableIndexes('users');
            if (array_key_exists('users_email_unique', $indexesFound)) {
                Schema::table('users', function (Blueprint $table) {
                    $table->dropUnique('users_email_unique');
                });
            }
        }

        $this->tableIndexPerTenant('users', function (Blueprint $table) {
            $table->dropUnique('users_email_unique');
        }, 'email', true, true);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
}
