<?php

namespace App\Providers;

use App\Models\PublicDownloadRequest;
use App\Models\PublicContact;
use App\View\Components\Ui\Alert;
use App\View\Components\Ui\Avatar;
use App\View\Components\Ui\Badge;
use App\View\Components\Ui\Button;
use App\View\Components\Ui\Card;
use App\View\Components\Ui\Input;
use App\View\Components\Ui\Label;
use App\View\Components\Ui\Table;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View as ViewFacade;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register Blade components following SPEC §10.2 & §13 convention
        Blade::component(Button::class, 'ui-button');
        Blade::component(Card::class, 'ui-card');
        Blade::component(Badge::class, 'ui-badge');
        Blade::component(Input::class, 'ui-input');
        Blade::component(Label::class, 'ui-label');
        Blade::component(Alert::class, 'ui-alert');
        Blade::component(Table::class, 'ui-table');
        Blade::component(Avatar::class, 'ui-avatar');

        ViewFacade::composer('layouts.app', function (View $view): void {
            $user = auth()->user();
            $view->with(
                'unreadNotificationCount',
                $user?->appNotifications()->whereNull('read_at')->count() ?? 0,
            );
            $view->with(
                'pendingDownloadRequestCount',
                $user?->isAdmin()
                    ? PublicDownloadRequest::query()->where('status', 'pending')->count()
                    : 0,
            );
            $view->with(
                'pendingSupportConversationCount',
                $user?->isAdmin()
                    ? $user->appNotifications()->whereNotNull('support_conversation_id')->whereNull('read_at')->count()
                    : 0,
            );
        });

        ViewFacade::composer('components.support-chat-widget', function (View $view): void {
            $view->with('publicContacts', PublicContact::query()->orderBy('id')->get());
        });
    }
}
