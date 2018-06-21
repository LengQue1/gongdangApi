<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
 */


$app->get('/', function () use ($app) {
    return $app->version();
});

$app->get('/phpinfo', function () use ($app) {
    var_dump(phpinfo());
});

$api = app('Dingo\Api\Routing\Router');

// v1 version API
// choose version add this in header    Accept:application/vnd.lumen.v1+json
$api->version('v1', [
        'namespace' => 'App\Http\Controllers\Api\V1',
        'middleware' => ['cors']
    ], function ($api) {

    // Auth
    // login
//    $api->post('user/login', [
//        'as' => 'auth.login',
//        'uses' => 'AuthController@login',
//    ]);
//    //unionLogin
//    $api->get('user/ypLogin', [
//        'uses' => 'AuthController@ypLogin',
//    ]);
//
//    //logout
//    $api->post('user/logout', [
//        'as' => 'auth.logout',
//        'uses' => 'AuthController@logout'
//    ]);
//
//    // AUTH
//    // refresh jwt token
//    $api->post('auth/token/new', [
//        'as' => 'auth.token.new',
//        'uses' => 'AuthController@refreshToken',
//    ]);
//
//    //声卡序列号api
//    $api->post('serial/save',[
//        'uses' => 'SerialController@save'
//    ]);
//    $api->get('serial/validate',[
//        'uses' => 'SerialController@checkToken'
//    ]);
//    $api->get('dserial/save',[
//        'uses' => 'DealerSerialController@save'
//    ]);
//
//    // USER
//    $api->get('users', [
//        'uses' => 'UserController@index',
//    ]);
//    $api->post('user/save', [
//        'uses' => 'UserController@store',
//    ]);
//    $api->get('user/verify', [
//        'uses' => 'UserController@verify',
//    ]);
//    $api->post('user/bind', [
//        'uses' => 'UserController@bindingPhone',
//    ]);

    //gongdan router
    $api->get('WorkOrders', [
        'uses' => 'WorkOrderController@index',
    ]);
    $api->post('WorkOrders/add', [
        'uses' => 'WorkOrderController@store',
    ]);
    $api->post('WorkOrders/update', [
        'uses' => 'WorkOrderController@update',
    ]);
    $api->post('WorkOrders/del', [
        'uses' => 'WorkOrderController@destroy',
    ]);

    // need authentication
//    $api->group(['middleware' => 'api.auth'], function ($api) {
//
//        //获取 用户信息 token
//        $api->get('user/getUserInfo', [
//            'uses' => 'AuthController@getUserInfo'
//        ]);
//        // 广告
//        $api->get('advertisement/get', [
//            'uses' => 'AdvertisementController@get'
//        ]);
//        $api->get('advertisement/getImgList', [
//            'uses' => 'AdvertisementController@getImgList'
//        ]);
//        $api->post('advertisement/uploadImg', [
//            'uses' => 'AdvertisementController@uploadImg'
//        ]);
//        $api->post('advertisement/add', [
//            'uses' => 'AdvertisementController@add'
//        ]);
//        $api->post('advertisement/del', [
//            'uses' => '\AdvertisementController@del'
//        ]);
//        $api->post('advertisement/delImg', [
//            'uses' => 'AdvertisementController@delImg'
//        ]);
//        $api->get('advertisement/list', [
//            'uses' => 'AdvertisementController@getList'
//        ]);
//        $api->get('advertisementCategory/get', [
//            'uses' => 'AdvertisementCategoryController@get'
//        ]);
//        $api->post('advertisementCategory/add', [
//            'uses' => 'AdvertisementCategoryController@add'
//        ]);
//        $api->post('advertisementCategory/del', [
//            'uses' => 'AdvertisementCategoryController@del'
//        ]);
//        $api->get('advertisementCategory/list', [
//            'uses' => 'AdvertisementCategoryController@getList'
//        ]);
//
//        // 软件
//        //    $api->post('software/upload',[
//        //        `
//        //        'uses' => 'SoftwareController@upload'
//        //    ]);
//        //    $api->get('software/getUpdate',[
//        //        'uses' => 'SoftwareController@getUpdate'
//        //    ]);
//        $api->get('software/getUpdateList',[
//            'uses' => 'SoftwareController@getUpdateList'
//        ]);
//        $api->get('software/getSetupList',[
//            'uses' => 'SoftwareController@getSetupList'
//        ]);
//        $api->post('software/uploadUpdatePackage',[
//            'uses' => 'SoftwareController@uploadUpdatePackage'
//        ]);
//        $api->post('software/uploadSetupPackage',[
//            'uses' => 'SoftwareController@uploadSetupPackage'
//        ]);
//        $api->post('software/delUpdatePackage',[
//            'uses' => 'SoftwareController@delUpdatePackage'
//        ]);
//        $api->post('software/delSetupPackage',[
//            'uses' => 'SoftwareController@delSetupPackage'
//        ]);
//        $api->get('software/getUploadToken',[
//            'uses' => 'SoftwareController@getUploadToken'
//        ]);
//
//        //驱动列表
//        $api->get('drivers', [
//            'uses' => 'DriverController@index',
//        ]);
//        $api->post('driver/upload', [
//            'uses' => 'DriverController@store',
//        ]);
//        $api->post('driver/del', [
//            'uses' => 'DriverController@destroy',
//        ]);
//
//        //图片列表
//        $api->get('images', [
//            'uses' => 'ImagesController@index',
//        ]);
//        $api->post('image/upload', [
//            'uses' => 'ImagesController@store',
//        ]);
//        $api->post('image/del', [
//            'uses' => 'ImagesController@destroy',
//        ]);
//
//        //管理员
//        $api->get('operator/getOperatorList',[
//            'uses' => 'OperatorController@getOperatorList'
//        ]);
//        $api->post('operator/saveOperator', [
//            'uses' => 'OperatorController@saveOperator'
//        ]);
//        $api->get('operator/del/{ypOperatorIds:\d+}', [
//            'uses' => 'OperatorController@del'
//        ]);
//        $api->get('operator/getItem', [
//            'uses' => 'OperatorController@getItem'
//        ]);
//        $api->post('operator/saveOperatorRole', [
//            'uses' => 'OperatorController@saveOperatorRole'
//        ]);
//        $api->get('role/getRoleList', [
//            'uses' => 'RoleController@getRoleList'
//        ]);
//        $api->post('role/saveRole', [
//            'uses' => 'RoleController@saveRole'
//        ]);
//        $api->get('role/getItem', [
//            'uses' => 'RoleController@getItem'
//        ]);
//        $api->get('role/del/{id:\d+}', [
//            'uses' => 'RoleController@del'
//        ]);
//        $api->get('requestMap/getRequestMapList', [
//            'uses' => 'RequestMapController@getRequestMapList'
//        ]);
//        $api->post('requestMap/saveRequestMap', [
//            'uses' => 'RequestMapController@saveRequestMap'
//        ]);
//        $api->get('requestMap/delRequestMap/{id}', [
//            'uses' => 'RequestMapController@delRequestMap'
//        ]);
//        $api->get('requestMap/getItem', [
//            'uses' => 'RequestMapController@getItem'
//        ]);
//
//        //设备
//        $api->get('devices', [
//            'uses' => 'DeviceController@index',
//        ]);
//        $api->post('device/add', [
//            'uses' => 'DeviceController@store',
//        ]);
//        $api->post('device/update', [
//            'uses' => 'DeviceController@update',
//        ]);
//        $api->post('device/del', [
//            'uses' => 'DeviceController@destroy',
//        ]);
//        // 序列号列表
//        $api->get('serials',[
//            'uses' => 'SerialController@index'
//        ]);
//        $api->post('serial/del',[
//            'uses' => 'SerialController@destroy'
//        ]);
//        // 序列号列表（经销商）
//        $api->get('dserials',[
//            'uses' => 'DealerSerialController@index'
//        ]);
//        $api->post('dserial/del',[
//            'uses' => 'DealerSerialController@destroy'
//        ]);
//
//        // 系统设置
//        $api->get('SystemSettings', [
//            'uses' => 'SystemSettingsController@index',
//        ]);
//        $api->post('SystemSetting/add', [
//            'uses' => 'SystemSettingsController@store',
//        ]);
//        $api->post('SystemSetting/update', [
//            'uses' => 'SystemSettingsController@update',
//        ]);
//        $api->post('SystemSetting/del', [
//            'uses' => 'SystemSettingsController@destroy',
//        ]);
//    });
});

// v2 version API
// add in header    Accept:application/vnd.lumen.v2+json
//$api->version('v2', function ($api) {
//    $api->get('foos', 'App\Http\Controllers\Api\V2\FooController@index');
//});
