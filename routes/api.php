<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('login', 'Auth\LoginController@login');
Route::post('register', 'Auth\RegisterController@register');
Route::post('register/verification', 'Auth\RegisterController@verification');
Route::get('register/verification/{code}', 'Auth\RegisterController@verificationCode');
Route::post('/password/email', 'Auth\ResetPasswordController@emailVerification');
Route::get('/password/reset/{token}', 'Auth\ResetPasswordController@find');
Route::post('/password/reset', 'Auth\ResetPasswordController@reset');
Route::get('createtoken', 'Auth\RegisterController@createToken');
Route::get('plans', 'MasterController@getPlans');
Route::get('jobs', 'MasterController@getJobs');
Route::get('settings', 'MasterController@getSettings');
Route::post('email/verify/{id}', 'Auth\VerificationController@verify')->name('verification.verify');
Route::group(['middleware' => array('auth:api', 'verified')], function()
{
    Route::get('/user/details', 'UserController@details');
    Route::patch('/user/details', 'UserController@update');
    Route::get('/bars/{bar_id}/customer-settings', 'CustomerSettingController@getCustomerSetting');
    Route::post('/user/customer-settings', 'CustomerSettingController@updateCustomerSetting');
    Route::get('staffs','StaffController@getListStaff');
    Route::post('staffs','StaffController@create');
    Route::get('staffs/{staffId}','StaffController@detail');
    Route::patch('staffs/{staffId}','StaffController@update');
    Route::delete('staffs/{staffId}','StaffController@delete');
    Route::get('customers','CustomerController@getCustomerList');
    Route::post('customers','CustomerController@create');
    Route::get('customers/{customerId}','CustomerController@getCustomerDetail');
    Route::get('customers/{customerId}/report','CustomerController@getCustomerReport');
    Route::get('bars/{barId}/customers/report', 'CustomerController@getCustomerReportByBar');
    Route::get('customers/{customerId}/bottles','CustomerController@listKeepBottle');
    Route::post('customers/{customerId}/bottles','CustomerController@modifyKeepBottle');
    Route::patch('customers/{customerId}','CustomerController@updateCustomerDetail');
    Route::get('customers/{customerId}/bars','CustomerController@getDropDownBarsByCustomer');
    Route::get('customers/{customerId}/bars/bottles','CustomerController@getListBottlesBarsByCustomer');

    Route::get('customerdata','CustomerController@getCustomerData');
    Route::patch('customerdata/remove-restore','CustomerController@updateCustomerData');
    
    Route::get('bars','BarController@getListBar');
    Route::get('dropdown/bars','BarController@getDropDownBars');
    Route::get('bars/{barId}','BarController@getDetailBar');
    Route::post('bars','BarController@createBar');
    Route::patch('bars/{barId}','BarController@updateBar');
    Route::delete('bars/{barId}','BarController@deleteBar');
    Route::get('bars/{barId}/bottles','BottleController@getListBottleByBarId');
    Route::get('bottles','BottleController@getListBottle');
    Route::get('bars/{barId}/categories','BottleCategoryController@getListBottleCategoryByBarId');
    Route::get('categories','BottleCategoryController@getListBottleCategory');
    Route::get('visits','OrderHistoryController@getListVisit');
    Route::post('visits','OrderHistoryController@create');
    Route::get('visits/{visitId}','OrderHistoryController@getVisitDetail');
    Route::put('visits/{visitId}','OrderHistoryController@update');
    Route::delete('visits/{visitId}','OrderHistoryController@delete');
    Route::get('customers/{customerId}/visits','CustomerController@getListVisitByCustomer');
    Route::get('bars/{barId}/casts','CastController@getListCastByBarId');
    Route::get('casts','CastController@getListCast');
    Route::get('autocomplete/customers','AutocompleteController@getCustomers');
    Route::post('casts','CastController@createCast');
    Route::post('categories','BottleCategoryController@modifyBottleCategory');
    Route::post('bottles','BottleController@modifyBottle');
    Route::delete('categories','BottleCategoryController@deleteListBottleCategory');
    Route::delete('bottles','BottleController@deleteListBottle');
    Route::get('casts/{castId}','CastController@detail');
    Route::patch('casts/{castId}','CastController@update');
    Route::delete('casts/{castId}','CastController@delete');
    Route::post('customers/importCsv', 'CustomerController@importCSVCustomers');
    Route::get('bars/{barId}/staffs', 'StaffController@getListStaffByBarId');
    Route::post('customers/{customerId}/uploadAvatar', 'CustomerController@uploadAvatar');
    Route::get('bars/{barId}/customers/statistic-birthday','CustomerController@getBirthdayCustomerStatisticByBar');
    Route::get('bars/{barId}/customers/statistic-keepBottles', 'CustomerController@getKeepBottleCustomerStatisticByBar');
    Route::get('bars/{barId}/customers/statistic-revenue', 'CustomerController@statisticRevenueCustomersByBar');
    Route::get('bars/{barId}/customers/statistic-visit', 'CustomerController@statisticCountVisitCustomersByBar');
    Route::get('bars/{barId}/customers/statistic-shimei', 'CustomerController@statisticCountShimeiCustomersByBar');
    Route::get('dropdown/staffs', 'StaffController@getDropDownStaffs');
    Route::get('visits/{visitId}/debit', 'DebitHistoryController@getListDebitHistoryByVisit');
    Route::post('visits/{visitId}/debit', 'DebitHistoryController@modifyDebitHistoryListByVisit');
    Route::get('user/bars', 'BarController@getListBarByUserLogin');

});

Route::group(['middleware' => 'auth:api'], function()
{
    Route::post('logout','Auth\LoginController@logout');
    Route::post('email/resend', 'Auth\VerificationController@resend')->name('verification.resend');
    Route::post('refresh-token','Auth\LoginController@refreshToken');
});
