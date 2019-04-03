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


/*
Route::get('/books', function () {
    return '{"test": 1}';
});
*/



// Route::get('/books','BooksController@getBooks');





Route::get('/','FrontController@allDocuments');

Route::get('/view-document/{id}','FrontController@viewDocument');


/*
	Посредник adminlogin создается а потом декларируется в - App\Http\kernel
	В посреднике просто проверяется сессия и происходит перенаправление на админскую авторизацию
*/

// Route::group(['middleware' => ['adminlogin']], function () {	


Route::group(['prefix' => 'admin',  'middleware' => 'adminlogin'], function()
{
	Route::match(['get', 'post'], '/documents','DocumentController@allDocuments');
	Route::match(['get', 'post'], '/view-document/{id}','DocumentController@viewDocument');
	Route::match(['get', 'post'], '/change-document/{id}','DocumentController@changeDocument');
	Route::delete('/delete-document/{id}','DocumentController@deleteDocument');
	
	
	Route::match(['get', 'post'], '/add-file/{id}','FileController@addFile');
	Route::delete('/delete-pdf/{id}','FileController@deleteFile');
});



/*
Route::prefix('admin')->group( function () {
	
	Route::match(['get', 'post'], '/documents','DocumentController@allDocuments');
	Route::match(['get', 'post'], '/view-document/{id}','DocumentController@viewDocument');
	Route::match(['get', 'post'], '/change-document/{id}','DocumentController@changeDocument');
	Route::delete('/delete-document/{id}','DocumentController@deleteDocument');
	
	
	Route::match(['get', 'post'], '/add-file/{id}','FileController@addFile');
	Route::delete('/delete-pdf/{id}','FileController@deleteFile');
	
	
    // Route::get('/documents','DocumentController@allDocuments');
    // Route::get('/create-document','DocumentController@createDocument');
});
*/




Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');



























