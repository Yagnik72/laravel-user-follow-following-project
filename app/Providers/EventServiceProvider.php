<?php

namespace App\Providers;

use App\Events\OurExampleEvent;
use App\Listeners\OurExampleListener;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;


class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        OurExampleEvent::class => [
            OurExampleListener::class,
            // OurExampleListener12332::class,
        ],
        // PS F:\wamp64\www\laravel\udemy\Learn Web Code - Course\OurApp> php artisan event:generate
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
