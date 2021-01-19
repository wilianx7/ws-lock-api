<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeed extends Seeder
{
    public function run()
    {
        User::create([
            'name' => 'admin',
            'email' => 'admin@admin.com',
            'login' => 'admin',
            'password' => bcrypt('p#mB2%f;<cnc(Vx:'),
        ]);
    }
}
