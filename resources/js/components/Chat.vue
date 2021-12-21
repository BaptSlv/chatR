<template>
    <div class="content">
        <div class="contact-profile">
            <img src="http://emilcarlsson.se/assets/harveyspecter.png" alt=""/>
            <p>{{ chatTitle }}</p>
            <div class="social-media">
                <div class="dropdown">
                    <button class="btn btn-sm" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-expanded="false">
                        <i class="fa fa-cog fa-fw"></i>
                    </button>
                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                        <a class="dropdown-item btn-sm" :href="'#addUsersModal' + chat.id" data-toggle="modal">
                            <i class="fa fa-user-plus fa-fw"></i> Add users to group
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="messages" ref="scroll">
            <ul>
                <li :class="{'sent' : currentUser.id !== message.user.id, 'replies' : currentUser.id === message.user.id}" v-for="message in chat.messages">
                    <img src="http://emilcarlsson.se/assets/mikeross.png" v-if="currentUser.id !== message.user.id"/>
                    <img src="http://emilcarlsson.se/assets/mikeross.png" v-if="currentUser.id === message.user.id"/>
                    <p>
                        {{ message.message }}<br>
                        <small><u>{{ message.created_at }}</u></small>
                    </p>
                </li>
            </ul>
        </div>
        <div class="message-input">
            <div class="wrap">
                <input type="text" placeholder="Write your message..." v-model="messageContent"/>
                <i class="fa fa-paperclip attachment" aria-hidden="true"></i>
                <button class="submit" @click.prevent="sendMessage()">
                    <i class="fa fa-paper-plane" aria-hidden="true"></i>
                </button>
            </div>
        </div>
        <div class="modal fade" :id="'addUsersModal' + chat.id" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Add users to chat {{ chat.title }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <v-select multiple v-model="selected" :options="contacts.filter(contact => !chat.users.map(u => u.id).includes(contact.id))" :reduce="contact => contact.id" label="name"></v-select>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" @click="addUserToChat()">Add user</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import vSelect from 'vue-select';

    export default {
        name: "Chat",
        components: {
            vSelect,
        },
        props: {
            ws: {
                type: WebSocket,
                required: true,
            },
            currentUser: {
                type: Object,
                required: true,
            },
            chat: {
                type: Object,
                required: true,
            },
            contacts: {
                type: Array,
                required: true,
            },
        },
        data() {
            return {
                messageContent: '',
                selected: [],
            };
        },
        mounted() {
            this.ws.send(JSON.stringify({
                action: 'register_member_chat',
                from: this.currentUser.id,
                token: this.currentUser.ws_token,
                chatId: this.chat.id
            }));

            this.scrollToBottom();
            this.$emit('on-mounted', this);
        },
        methods: {
            sendMessage() {
                let datas = JSON.stringify({
                    action: 'create_message',
                    from: this.currentUser.id,
                    token: this.currentUser.ws_token,
                    chat: this.chat.id,
                    content: this.messageContent,
                });

                if (this.messageContent) {
                    this.ws.send(datas);
                    this.messageContent = '';
                }
            },
            appendMessage(data) {
                this.chat.messages.push(data);
                this.scrollToBottom();
            },
            onNotification(data) {
                switch (data.notificationType) {
                    case 'add_member':
                        this.chat.users = data.users;

                        break;
                }
            },
            addUserToChat() {
                let datas = JSON.stringify({
                    action: 'add_contacts_to_chat',
                    from: this.currentUser.id,
                    token: this.currentUser.ws_token,
                    chat: this.chat.id,
                    users: this.selected,
                });

                if (this.selected) {
                    this.ws.send(datas);
                    this.selected = [];
                }

                $('#addUsersModal' + this.chat.id).modal('hide');
            },
            scrollToBottom() {
                this.$nextTick(() => {
                    this.$refs.scroll.scrollTop = this.$refs.scroll.scrollHeight;
                });
            },
        },
        computed: {
            chatTitle() {
                if (this.chat.users.length === 2) {
                    return this.chat.users.find(user => user.id !== this.currentUser.id).name;
                }

                return this.chat.title;
            }
        },
    }
</script>