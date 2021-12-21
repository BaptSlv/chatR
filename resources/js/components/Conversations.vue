<template>
    <div id="frame" v-if="ws && ready">
        <div id="sidepanel">
            <div id="profile">
                <div class="wrap">
                    <img id="profile-img" src="http://emilcarlsson.se/assets/mikeross.png" class="online" alt=""/>
                    <p>{{ currentUser.name }}</p>
                </div>
            </div>
            <div id="search">
                <label for=""><i class="fa fa-search" aria-hidden="true"></i></label>
                <input type="text" placeholder="Search contacts..."/>
            </div>
            <div id="contacts">
                <ul>
                    <li v-for="userChat in userChats" @click.prevent="showChat(userChat)" class="contact active">
                        <div class="wrap">
                            <span class="contact-status busy"></span>
                            <img src="http://emilcarlsson.se/assets/harveyspecter.png" alt=""/>
                            <div class="meta">
                                <p class="name">{{ getChatTitle(userChat) }}</p>
                                <p class="preview"></p>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
            <div id="bottom-bar">
                <form :action="webRoute" method="get">
                    <button id="addcontact" type="submit"><i class="fa fa-user-plus fa-fw" aria-hidden="true"></i> <span>Manage contacts</span></button>
                </form>
                <button id="settings" data-toggle="modal" data-target="#createChatModal"><i class="fa fa-plus-circle" aria-hidden="true"></i> <span>Create a chat</span></button>
            </div>
        </div>
        <component v-for="userChat in userChats" :is="userChat.model" :key="userChat.id" :chat="userChat" :current-user="currentUser" :contacts="contacts" :ws="ws" v-show="currentChat && userChat.id === currentChat.id" @on-mounted="userChat.instance = $event"></component>
        <div class="modal fade" id="createChatModal" tabindex="-1" role="dialog" aria-labelledby="createChatModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="createChatModalLabel">Create a new chat</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="chat-title">Chat title</label>
                            <input v-model="chatTitle" type="text" class="form-control" id="chat-title">
                        </div>
                        <div class="form-group">
                            <label>Select some users</label>
                            <v-select multiple v-model="chatUsers" :options="contacts.filter(contact => contact.id !== currentUser.id)" :reduce="contact => contact.id" @input="" label="name"></v-select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" @click="createChat()">Create chat</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import Chat from './Chat';
    import vSelect from 'vue-select';

    export default {
        name: "Conversations",
        components: {
            Chat,
            vSelect,
        },
        props: {
            currentUserData: {
                type: String,
                required: true,
            },
            contactsData: {
                type: String,
                required: true,
            },
            userChatsData: {
                type: String,
                required: true,
            },
            webRouteData: {
                type: String,
                required: true,
            }
        },
        data: function () {
            return {
                currentChat: null,
                ws: null,
                ready: false,
                currentUser: JSON.parse(this.currentUserData),
                contacts: JSON.parse(this.contactsData),
                userChats: JSON.parse(this.userChatsData),
                webRoute: this.webRouteData,
                chatTitle: '',
                chatUsers: [],
            };
        },
        mounted() {
            this.ws = new WebSocket('ws://localhost:8090');
            this.ws.onopen = this.onOpen;
            this.ws.onmessage = this.onMessage;

            this.userChats = this.userChats.map((userChat) => {
                userChat.instance = null;
                userChat.model = Chat;

                return userChat;
            });
        },
        methods: {
            onOpen() {
                this.ws.send(JSON.stringify({
                    action: 'register_user',
                    from: this.currentUser.id,
                    token: this.currentUser.ws_token,
                }));

                this.ready = true;
            },
            onMessage(e) {
                let data = JSON.parse(e.data);

                switch (data.action) {
                    case 'join_chat':
                        let chat = data.chat;
                        chat.instance = null;
                        chat.model = Chat;

                        this.userChats.unshift(chat);
                        break;
                    case 'receive_message':
                        if (this.currentChat && this.currentChat.id === data.chat) {
                            this.currentChat.instance.appendMessage(data.message);
                        } else {
                            this.userChats.forEach(function (userChat) {
                                if (userChat.id === data.chat) {
                                    userChat.instance.appendMessage(data.message);
                                }
                            })
                        }

                        break;
                    case 'chat_notification':
                        this.userChats.forEach(function (userChat) {
                            if (userChat.id === data.chatId) {
                                userChat.instance.onNotification(data);
                            }
                        });

                        break
                }
            },
            showChat(userChat) {
                this.currentChat = userChat;
            },
            createChat() {
                if (this.chatTitle) {
                    let newChatDatas = JSON.stringify({
                        action: 'create_chat',
                        from: this.currentUser.id,
                        token: this.currentUser.ws_token,
                        title: this.chatTitle,
                        members: this.chatUsers,
                    });

                    this.ws.send(newChatDatas);
                    this.chatTitle = '';

                    $('#createChatModal').modal('hide');
                }
            },
            getChatTitle(userChat) {
                if (userChat.users.length === 2) {
                    return userChat.users.find(user => user.id !== this.currentUser.id).name;
                }

                return userChat.title;
            }
        },
    }
</script>