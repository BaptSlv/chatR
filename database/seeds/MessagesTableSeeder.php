<?php

use App\Message;
use Illuminate\Database\Seeder;

class MessagesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $messages = [
            [
                'message' => 'Salut les plow',
                'chat_id' => 1,
                'user_id' => 1,
            ],
            [
                'message' => 'Salut bandes de batards',
                'chat_id' => 1,
                'user_id' => 2,
            ],
            [
                'message' => 'Wesh les morray',
                'chat_id' => 1,
                'user_id' => 3,
            ],
            [
                'message' => 'Salut mes petits shurikens d’amour',
                'chat_id' => 1,
                'user_id' => 4,
            ],
        ];

        foreach ($messages as $message) {
            Message::firstOrCreate($message);
        }
    }
}
