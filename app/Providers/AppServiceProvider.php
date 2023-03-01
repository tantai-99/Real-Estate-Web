<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Http\UploadedFile;
use App\Http\Middleware\TrimStrings;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if (config('app.env') == 'production' || config('app.env') == 'staging' || config('app.env') == 'testing') {
            \URL::forceScheme('https');
        }
        TrimStrings::skipWhen(function ($request) {
            return $request->is('*');
        });
        
        \Validator::extend('file_extension', function ($attribute, $value, $parameters, $validator) {
            if (!$value instanceof UploadedFile) {
                return false;
            }
    
            $extensions = implode(',', $parameters);
            $validator->addReplacer('file_extension', function (
                $message,
                $attribute,
                $rule,
                $parameters
            ) use ($extensions) {
                return \str_replace(':values', $extensions, $message);
            });
    
            $extension = strtolower($value->getClientOriginalExtension());
    
            return $extension !== '' && in_array($extension, $parameters);
        });
    }
}