<?php

namespace App\Console\Commands;

class AddSubscriptions extends BaseGitHubPull
{
    protected $signature = 'add:subscriptions {--branch=main}';
    protected $description = 'Add subscription functionality from the laravel-subscriptions repository';

    public function handle()
    {
        $this->argument['repo'] = 'https://github.com/Amaan630/laravel-subscriptions.git';
        return parent::handle();
    }
}
