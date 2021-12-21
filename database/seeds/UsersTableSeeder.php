<?php

use App\User;
use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = [
            [
                'name' => 'Baptiste Salvi',
                'email' => 'baptiste.salvi@gmail.com',
                'password' => bcrypt('trololo'),
                'ws_token' => 'blabla',
            ],
            [
                'name' => 'Alexis Salvador',
                'email' => 'alexis.salvador@gmail.com',
                'password' => bcrypt('trololo'),
                'ws_token' => 'blibli',
            ],
            [
                'name' => 'Florian Labare',
                'email' => 'florian.labare@gmail.com',
                'password' => bcrypt('trololo'),
                'ws_token' => 'bloblo',
            ],
            [
                'name' => 'Romain Chipon',
                'email' => 'romain.chipon@gmail.com',
                'password' => bcrypt('trololo'),
                'ws_token' => 'bleble',
            ],

        ];

        $instanceUsers = [];
        foreach ($users as $user) {
            $instanceUsers[] = User::create($user);
        }

        foreach ($instanceUsers as $user1) {
            foreach ($instanceUsers as $user2) {
                if ($user1 !== $user2) {
                    $user1->contacts()->attach($user2);
                }
            }
        }
    }
}
