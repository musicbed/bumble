<?php

Route::middleware('bumble_auth', function()
{
    if (Auth::guest()) return Redirect::guest(route('bumble.login'));
});
