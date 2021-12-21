<?php

namespace App\Http\Controllers;

use App\Chat;
use App\Message;
use App\User;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use SplObjectStorage;

/**
 * @author Rohit Dhiman | @aimflaiims
 */
class WebSocketController implements MessageComponentInterface
{
    protected $clients;
    private $wsUsers;
    private $wsChats;

    public function __construct()
    {
        $this->clients = new SplObjectStorage;
        $this->wsUsers = [];
        $this->wsChats = [];
    }

    private function registerUser(ConnectionInterface $conn, $data)
    {
        $this->wsUsers[$conn->resourceId] = [
            'conn' => $conn,
            'userId' => $data->from,
            'resourceId' => $conn->resourceId
        ];
    }

    private function joinChat(ConnectionInterface $conn, Chat $chat)
    {
        $conn->send(json_encode([
            'action' => 'join_chat',
            'chat' => $chat,
        ]));
    }

    private function registerMemberChat($websocketUser, Chat $chat)
    {
        if (!isset($this->wsChats[$chat->id])) {
            $this->wsChats[$chat->id] = [
                'members' => [],
            ];
        }

        $this->wsChats[$chat->id]['members'][$websocketUser['userId']] = &$websocketUser;
    }


    private function createChat($websocketUser, $data)
    {
        /** @var Chat $newChat */
        $newChat = Chat::query()->create([
            'title' => $data->title,
        ]);
        $newChat->load('messages');

        $members = [];
        if (!empty($data->members)) {
            $findUsers = User::query()->findMany($data->members);
            $members = $findUsers->pluck('id');
        }

        $members[] = $websocketUser['userId'];
        $newChat->users()->sync($members);
        $newChat->load('users');

        foreach ($members as $member) {
            foreach ($this->wsUsers as $user) {
                if ($user['userId'] === $member) {
                    $this->joinChat($user['conn'], $newChat);
                }
            }
        }
    }

    private function addNewUserToChat($data)
    {
        /** @var Chat $chat */
        $chat = Chat::query()->find($data->chat);

        foreach ($data->users as $newChatUser) {
            $findUser = $chat->users()->find($newChatUser);
            if (is_null($findUser)) {
                foreach ($this->wsUsers as $user) {
                    if ($user['userId'] === $newChatUser) {
                        $this->registerMemberChat($user, $chat);
                    }
                }
                $chat->users()->attach($newChatUser);
            }
        }

        $chat->load('users');

        $messageContent = '';
        $message = new Message();

        foreach ($this->wsChats[$chat->id]['members'] as $member) {
            if (!in_array($member['userId'], $data->users)) {
                $member['conn']->send(json_encode([
                    'action' => 'chat_notification',
                    'notificationType' => 'add_member',
                    'chatId' => $chat->id,
                    'users' => $chat->users,
                    'message' => $message,
                ]));
            } else {
                $this->joinChat($member['conn'], $chat);
            }
        }
    }

    private function createMessage($websocketUser, $data)
    {
        /** @var Chat $chat */
        $chat = Chat::query()->find($data->chat);
        if ($chat) {
            $message = $chat->messages()->create([
                'message' => $data->content,
                'user_id' => $websocketUser['userId'],
            ]);
            $message->load('user');

            foreach ($this->wsChats[$chat->id]['members'] as $member) {
                $member['conn']->send(json_encode([
                    'action' => 'receive_message',
                    'chat' => $chat->id,
                    'message' => $message,
                ]));
            }
        }
    }

    public function onOpen(ConnectionInterface $conn)
    {
        echo 'New Connection' . $conn->resourceId;
        $this->clients->attach($conn);
    }

    public function onMessage(ConnectionInterface $conn, $msg)
    {
        try {
            $data = json_decode($msg);
            dump($data);

            $websocketUser = null;
            if ($data->action !== 'register_user') {
                $websocketUser = $this->wsUsers[$conn->resourceId];
                if (is_null($websocketUser)) {
                    return;
                }
            }

            /** @var User $user */
            $user = User::query()->find($data->from);

            if ($user && $user->ws_token === $data->token) {
                switch ($data->action) {
                    case 'register_user':
                        $this->registerUser($conn, $data);
                        break;
                    case 'register_member_chat':
                        /** @var Chat $chat */
                        $chat = Chat::query()->find($data->chatId);
                        if (is_null($chat)) {
                            return;
                        }

                        $this->registerMemberChat($websocketUser, $chat);
                        break;
                    case 'create_chat':
                        $this->createChat($websocketUser, $data);
                        break;
                    case 'create_message':
                        $this->createMessage($websocketUser, $data);
                        break;
                    case 'add_contacts_to_chat':
                        $this->addNewUserToChat($data);
                        break;
                }
            }
        } catch (\Exception $e) {
            dd($e->getMessage());
        }
    }


    public function onClose(ConnectionInterface $conn)
    {
        $this->clients->detach($conn);
        echo "Connection {$conn->resourceId} has disconnected\n";

        //Vider $this->wsChats et $this->>wsUser
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        //
    }
}