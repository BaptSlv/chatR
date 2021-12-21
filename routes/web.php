<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/home', 'HomeController@index')->name('home');
    Route::get('/contacts', 'ContactController@index')->name('manageContacts');
    Route::get('/contacts//getInvitations', 'ContactController@getInvitations')->name('contact.invitations');
    Route::get('/contacts/getUserContacts', 'ContactController@getUserContacts')->name('contact.data');
    Route::post('/contacts/add', 'ContactController@add')->name('contact.add');
    Route::post('/contacts/{contact}/accept', 'ContactController@acceptInvitation')->middleware('can_accept_invitation')->name('acceptInvitation');
    Route::post('/contacts/{contact}/remove', 'ContactController@remove')->middleware('can_remove_contact')->name('removeContact');
});
