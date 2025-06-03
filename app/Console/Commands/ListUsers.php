<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class ListUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:list-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all users for testing purposes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $users = User::all();

        $this->info("Found {$users->count()} users:");
        $this->info("");

        foreach ($users as $user) {
            $this->info("ID: {$user->id}");
            $this->info("Name: {$user->name}");
            $this->info("Email: {$user->email}");
            $this->info("Type: {$user->user_type}");
            $this->info("Company: " . ($user->company ? $user->company->name : 'None'));
            $this->info("---");
        }

        return 0;
    }
}
