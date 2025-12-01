<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Pagination View
    |--------------------------------------------------------------------------
    |
    | This view will be used to render the pagination link output. You are
    | free to change this view to anything you like. However, it is important
    | to remember that the pagination views are rendered with the data
    | passed to them, so you should make sure that the views are compatible
    | with the data being passed to them.
    |
    */

    'view' => 'pagination::bootstrap-4',

    /*
    |--------------------------------------------------------------------------
    | Pagination Links
    |--------------------------------------------------------------------------
    |
    | The pagination links view will be used to render the pagination links
    | for the application. You are free to change this view to anything you
    | like. However, it is important to remember that the pagination views
    | are rendered with the data passed to them, so you should make sure that
    | the views are compatible with the data being passed to them.
    |
    */

    'links' => 'pagination::bootstrap-4',

    /*
    |--------------------------------------------------------------------------
    | Pagination View Presenter
    |--------------------------------------------------------------------------
    |
    | This view will be used to render the pagination view. You are free to
    | change this view to anything you like. However, it is important to
    | remember that the pagination views are rendered with the data passed
    | to them, so you should make sure that the views are compatible with
    | the data being passed to them.
    |
    */

    'presenter' => 'Illuminate\Pagination\BootstrapFourPresenter',

];























