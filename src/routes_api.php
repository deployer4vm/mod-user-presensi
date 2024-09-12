<?php

use Illuminate\Support\Facades\Route;

/**
 * Route API feature Auth
 */
$groupAuth = [
    'prefix' => config('AppConfig.endpoint.api.auth')
];
Route::group($groupAuth,function(){
    //Auth/LoginController
    Route::post('/login', 'Auth\LoginController@apiLogin')->name('auth.api.login');

    //Auth/RegisterController
    Route::post('/register', 'Auth\RegisterController@apiRegister')->name('auth.api.register');

    //Auth/ForgotPasswordController
    Route::post('/forgotpassword', 'Auth\ForgotPasswordController@doForgotPassword')->name('auth.api.forgotpassword');

    //Auth/TokenApiController - generate token akses tanpa user
    // Route::post('/token', 'Auth\TokenApiController@generateToken')->name('auth.api.generatetoken');

    Route::middleware('auth:api')->group(function(){
        //Auth/LoginController
        Route::get('/logout', 'Auth\LoginController@apiLogout')->name('auth.api.logout');
        //TokenApiController
        Route::post('/token/validate/{token}', 'Auth\TokenApiController@validateToken')->name('auth.api.validatetoken');
        //ubah role user yang sedang loign
        Route::get('/change_role/{role_code}', 'UserController@changeRole')->name('auth.api.changerole');
        //
        Route::post('/pin/validate/{encryptedPin}', 'UserController@validatePin')->name('auth.api.validatePin');
    });

    /**
     * SSO
     */

});

/**
 * Get Auth Config
 */
Route::group(['prefix'=>config('AppConfig.endpoint.api.moduser').'/auth-config'],function(){
    Route::get('/', 'config\AuthConfigController@index')->name('user.api.authConfig');
    Route::get('/registration', 'config\AuthConfigController@registrationConfig')->name('user.api.authConfig.registration');
    Route::get('/login', 'config\AuthConfigController@loginConfig')->name('user.api.authConfig.login');
    Route::get('/password', 'config\AuthConfigController@passwordConfig')->name('user.api.authConfig.password');
    Route::get('/otp', 'config\AuthConfigController@otpConfig')->name('user.api.authConfig.otp');
    Route::get('/pin', 'config\AuthConfigController@pinConfig')->name('user.api.authConfig.pin');
});
/**
 * Route API feature User Management
 */
$groupUser = [
    'prefix' => config('AppConfig.endpoint.api.moduser'),
    'middleware' => 'auth:api'
];
Route::get('/notification/setnotif', 'NotificationController@setnotif')->name('user.notification.setnotif');
Route::group($groupUser,function(){
    /**
     * test auth
     */
    Route::group(['prefix'=>'check-auth'],function(){
        Route::group(['middleware'=>'auth.useronly'],function(){
            Route::get('/user', 'AuthTestController@checkUser')->name('user.api.authTest.checkUser');
        });
    });
    
    /**
     * Role
     */
    Route::group(['prefix'=>'role'],function(){
        
        /**
         * Role Group
         */
        Route::group(['prefix'=>'group'],function(){
            //read list resource
            Route::get('/', 'role\RoleGroupController@readList')->name('user.role.group.readList');
            //read one resource
            Route::get('/{id}', 'role\RoleGroupController@readOne')->name('user.role.group.readOne');

            //create resource
            Route::post('/', 'role\RoleGroupController@create')->name('user.role.group.create');
            //update resource
            Route::put('/{id}', 'role\RoleGroupController@update')->name('user.role.group.update');
            //delete resource
            Route::delete('/{id}', 'role\RoleGroupController@delete')->name('user.role.group.delete');
        });

        /**
         * Role Level Group
         */
        Route::group(['prefix'=>'level-group'],function(){
            //read list resource
            Route::get('/', 'role\RoleLevelGroupController@readList')->name('user.role.levelGroup.readList');
            //read one resource
            Route::get('/{id}', 'role\RoleLevelGroupController@readOne')->name('user.role.levelGroup.readOne');

            //create resource
            Route::post('/', 'role\RoleLevelGroupController@create')->name('user.role.levelGroup.create');
            //update resource
            Route::put('/{id}', 'role\RoleLevelGroupController@update')->name('user.role.levelGroup.update');
            //delete resource
            Route::delete('/{id}', 'role\RoleLevelGroupController@delete')->name('user.role.levelGroup.delete');
        });
        
        /**
         * Datarule
         */
        Route::group(['prefix'=>'datarule'],function(){
            //read list resource
            Route::get('/', 'role\DataruleController@readList')->name('user.role.datarule.readList');
            //read one resource
            Route::get('/{id}', 'role\DataruleController@readOne')->name('user.role.datarule.readOne');

            //create resource
            Route::post('/', 'role\DataruleController@create')->name('user.role.datarule.create');
            //update resource
            Route::put('/{id}', 'role\DataruleController@update')->name('user.role.datarule.update');
            //delete resource
            Route::delete('/{id}', 'role\DataruleController@delete')->name('user.role.datarule.delete');
        });

        
        //read list resource
        Route::get('/', 'role\RoleController@readList')->name('user.role.readList');
        //read one resource
        Route::get('/{id}', 'role\RoleController@readOne')->name('user.role.readOne');

        //create resource
        Route::post('/', 'role\RoleController@create')->name('user.role.create');
        //update resource
        Route::put('/{id}', 'role\RoleController@update')->name('user.role.update');
        //delete resource
        Route::delete('/{id}', 'role\RoleController@delete')->name('user.role.delete');
    });

    /**
     * Module User System
     */
    Route::group(['prefix'=>'usersystem'],function(){
        Route::get('/', 'UserSystemController@readList')->name('user.usersystem.readList');
        Route::get('/{id}', 'UserSystemController@readOne')->name('user.usersystem.readOne');
        Route::match(['put','post'],'/', 'UserSystemController@create')->name('user.usersystem.create');
        Route::match(['put','post'],'/{id}', 'UserSystemController@update')->name('user.usersystem.update');
        Route::delete('/{id}', 'UserSystemController@delete')->name('user.usersystem.delete');
        Route::match(['put','post'],'/{id}/generate_token', 'UserSystemController@generateToken')->name('user.usersystem.generateToken');
    });

    /**
     * Module Role System
     */
    Route::group(['prefix'=>'rolesystem'],function(){
        Route::get('/', 'RoleSystemController@readList')->name('user.rolesystem.readList');
        Route::get('/{id}', 'RoleSystemController@readOne')->name('user.rolesystem.readOne');
        Route::post('/', 'RoleSystemController@create')->name('user.rolesystem.create');
        Route::put('/{id}', 'RoleSystemController@update')->name('user.rolesystem.update');
        Route::delete('/{id}', 'RoleSystemController@delete')->name('user.rolesystem.delete');
    });

    /**
     * Config
     */
    Route::group(['prefix'=>'config'],function(){
        Route::get('/tenant', 'config\DataMasterController@tenantList')->name('user.config.tenant.readList');
        /**
         * Config Dashboard
         */
        Route::group(['prefix'=>'dashboard'],function(){
            //read list resource
            Route::get('/', 'config\DashboardController@getConfigDashboard')->name('user.config.dashboard.readList');
            //create resource
            Route::post('/', 'config\DashboardController@updateConfigDashboard')->name('user.config.dashboard.create');
            //update resource
            Route::put('/{id}', 'config\DashboardController@updateConfigDashboard')->name('user.config.dashboard.update');

            //----------

            //read list resource
            Route::get('/', 'config\DashboardController@readList')->name('user.config.dashboard.readList');
            //read one resource
            Route::get('/{id}', 'config\DashboardController@readOne')->name('user.config.dashboard.readOne');

            //create resource
            Route::post('/', 'config\DashboardController@create')->name('user.config.dashboard.create');
            //update resource
            Route::put('/{id}', 'config\DashboardController@update')->name('user.config.dashboard.update');
            //delete resource
            Route::delete('/{id}', 'config\DashboardController@delete')->name('user.config.dashboard.delete');
        });
    });

    /**
     * fitur notif
     */
    Route::group(['middleware'=>'auth.useronly'],function(){
        Route::get('/notification', 'NotificationController@index')->name('user.api.notification');
        Route::get('/notification/type', 'NotificationController@listType')->name('user.api.notification.listType');
        Route::get('/notification/{notificationId}', 'NotificationController@detail')->name('user.api.notification.detail');
        Route::delete('/notification/{notificationId}', 'NotificationController@deleteNotif')->name('user.api.notification.delete');
        Route::post('/notification/read', 'NotificationController@setRead')->name('user.api.notification.setReadBulk');
        Route::post('/notification/{notificationId}/read', 'NotificationController@setRead')->name('user.api.notification.setRead');
        Route::post('/notification/unread', 'NotificationController@setUnread')->name('user.api.notification.setUnreadBulk');
        Route::post('/notification/{notificationId}/unread', 'NotificationController@setUnread')->name('user.api.notification.setUnread');
    });
    
    // --- authenticator /api/user/authenticator-*
    Route::group(['middleware'=>'auth.useronly'],function(){

        // LIST client yg request
        Route::get('/authenticator-request', 'Auth\AuthenticatorController@authRequestGet')
            ->name('user.authenticator.getRequest');

        // CREATE new request
        Route::match(['put','post'],'/authenticator-request', 'Auth\AuthenticatorController@authRequestCreate')
            ->name('user.authenticator.requestCreate');

        // GRANT access client yg sebelumnya oleh 
        Route::match(['put','post'],'/authenticator-request/{featureCode}/{requestCode}/grant', 'Auth\AuthenticatorController@authRequestGrant')
            ->name('user.authenticator.grantRequest');
            
        // REJECT client yg request
        Route::match(['put','post'], '/authenticator-request/{featureCode}/{requestCode}/reject', 'Auth\AuthenticatorController@authRequestReject')
            ->name('user.authenticator.requestReject');
            
        // VERIFY apakah client sudah di grand access
        Route::get('/authenticator-request/{featureCode}/{requestCode}', 'Auth\AuthenticatorController@authGrantVerify')
            ->name('user.authenticator.requestVerify');

    });
    // ---

    /**
     * fitur Broadcast notif
     */
    Route::get('/broadcast', 'BroadcastController@index')->name('user.broadcast');
    Route::post('/broadcast', 'BroadcastController@sendBroadcast')->name('user.broadcast.send');

    /**
     * fitur User
     */

    /**
     * User Group
     */
    Route::group(['prefix'=>'group'],function(){
        //read list resource
        Route::get('/', 'UserGroupController@readList')->name('user.group.readList');
        //read one resource
        Route::get('/{id}', 'UserGroupController@readOne')->name('user.group.readOne');

        //create resource
        Route::post('/', 'UserGroupController@create')->name('user.group.create');
        //update resource
        Route::put('/{id}', 'UserGroupController@update')->name('user.group.update');
        //delete resource
        Route::delete('/{id}', 'UserGroupController@delete')->name('user.group.delete');
    });

    // Export User
    Route::group(['prefix' => 'export', 'as' => 'download.'], function () {
        Route::get('/', 'UserController@downloadDataUser')->name('dataUser');
        Route::get('/status', 'UserController@downloadDataUserStatus')->name('dataUserStatus');
    });

    //read list resource
    Route::get('/', 'UserController@readList')->name('user.readList');
    //read one resource
    Route::get('/{id}', 'UserController@readOne')->name('user.readOne');

    //create resource
    Route::match(['put','post'],'/', 'UserController@create')->name('user.create');
    //update resource
    Route::middleware(['auth.useronly'])->match(['put','post'],'/profile', 'UserController@updateProfile')->name('user.update');
    // generate and send OTP
    Route::middleware(['auth.useronly'])->match(['put','post'],'/send-otp', 'UserController@sendOtp')->name('user.sendOtp');
    Route::middleware(['auth.useronly'])->match(['put','post'],'/validate-otp', 'UserController@validateOtp')->name('user.validateOtp');

    Route::match(['put','post'],'/{id}', 'UserController@update')->name('user.update');
    Route::put('/{id}/ban', 'UserController@ban')->name('user.ban');
    Route::put('/{id}/unban', 'UserController@unban')->name('user.unban');
    Route::put('/{id}/resent-verification-mail', 'UserController@resentVerificationMail')->name('user.resentVerificationMail');
    Route::put('/{id}/updatepassword', 'UserController@updatePassword')->name('user.updatePassword');
    Route::post('/{id}/upload-avatar', 'UserController@uploadAvatar')->name('user.uploadAvatar');
    Route::delete('/{id}/delete-avatar', 'UserController@deleteAvatar')->name('user.deleteAvatar');
    //delete resource
    Route::delete('/{id}', 'UserController@delete')->name('user.delete');

    // kirim otp via wa
    // Route::post('/profile/pin/send-otp', 'UserController@sendOtpViaWa')->name('user.send.pin')->middleware(['auth.useronly']);

    // update profile pin yang sudah di kirimkan otp nya
    // Route::match(['put','post'], '/profile/pin/update', 'UserController@updateProfilePin')->name('user.update.pin')->middleware(['auth.useronly']);

});
