<?php
Route::group(['prefix' => Config::get('bumble::admin_prefix')], function()
{
    Route::get(Config::get('bumble::admin.login'), ['as' => 'bumble_login', 'uses' => 'Monarkee\Bumble\Controllers\LoginController@getLogin']);
    Route::post(Config::get('bumble::admin.login'), ['as' => 'bumble.login.post', 'uses' => 'Monarkee\Bumble\Controllers\LoginController@postLogin']);
    Route::get(Config::get('bumble::admin.logout'), ['as' => 'bumble_logout', 'uses' => 'Monarkee\Bumble\Controllers\LoginController@getLogout']);

    Route::group(['before' => 'bumble_auth'], function()
    {
        Route::get('/', ['as' => 'bumble_index', 'uses' => 'Monarkee\Bumble\Controllers\DashboardController@redirectToIndex']);
        Route::get(Config::get('bumble::admin.dashboard'), ['as' => 'bumble_dashboard', 'uses' => 'Monarkee\Bumble\Controllers\DashboardController@getIndex']);

        $modelRepo = App::make('Monarkee\Bumble\Repositories\ModelRepository');

        foreach ($modelRepo->getModels() as $modelName)
        {
            Route::resource(resource_name($modelName->getPluralSlug()), 'Monarkee\Bumble\Controllers\PostController');
        }
        });
});
