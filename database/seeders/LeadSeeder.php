<?php

namespace Database\Seeders;

use App\Enums\LeadStatus;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Database\Seeder;

class LeadSeeder extends Seeder
{
    public function run(): void
    {
        User::all()->each(function (User $user) {
            foreach (LeadStatus::cases() as $status) {
                Lead::factory()
                    ->count(10)
                    ->for($user)
                    ->create(['status' => $status->value]);
            }
        });
    }
}
