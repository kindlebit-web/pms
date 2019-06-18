<?php
Route::get('admin', function(){
 return redirect('/');
});

Route::group(['middleware' => 'web'], function () {
    Voyager::routes();
});