<?php

use App\Chat;
use Illuminate\Database\Seeder;

class ChatsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Chat::firstOrCreate([
            'title' => 'Conversation de plow',
        ]);
    }
}
