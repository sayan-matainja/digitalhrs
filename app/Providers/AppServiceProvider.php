<?php

namespace App\Providers;

use App\Models\Task;
use App\Observers\TaskObserver;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;


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
        Schema::defaultStringLength(191);
        Paginator::useBootstrap();
//        JsonResource::withoutWrapping();
        Relation::enforceMorphMap([
            'project' => 'App\Models\Project',
            'task' => 'App\Models\Task',
            'comment' => 'App\Models\TaskComment',
            'reply' => 'App\Models\CommentReply',
        ]);

        Task::observe(TaskObserver::class);

        $locale = Cache::get('locale', 'en');
        App::setLocale($locale);

        // Override Blade's @can to respect the admin guard
        Blade::directive('can', function ($expression) {
            return "<?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::check({$expression})): ?>";
        });

        Blade::directive('endcan', function () {
            return '<?php endif; ?>';
        });

        // Override @canany for multiple permissions
        Blade::directive('canany', function ($expression) {
            return "<?php if (Auth::guard('admin')->check() || \Illuminate\Support\Facades\Gate::any({$expression})): ?>";
        });

        Blade::directive('endcanany', function () {
            return '<?php endif; ?>';
        });
    }
}
