<template>
    <div v-if="ws && ready" class="row justify-content-center h-100">
        <div class="col-md-4 col-xl-3">
            <div class="card mb-sm-3 mb-md-0 contacts_card">
                <div class="card-header">
                    <div class="input-group text-white">
                        <h5>Chats
                            <button class="btn btn-light btn-sm" data-toggle="modal" data-target="#createChatModal"><i class="fa fa-plus-circle" aria-hidden="true"></i></button>
                        </h5>
                    </div>
                </div>
                <div class="card-body contacts_body">
                    <ul class="contacts">
                        <li v-for="userChat in userChats" @click.prevent="showChat(userChat)" class="active">
                            <div class="d-flex bd-highlight">
                                <div class="img_cont">
                                    <img src="https://2.bp.blogspot.com/-8ytYF7cfPkQ/WkPe1-rtrcI/AAAAAAAAGqU/FGfTDVgkcIwmOTtjLka51vineFBExJuSACLcBGAs/s320/31.jpg" class="rounded-circle user_img">
                                    <span class="online_icon"></span>
                                </div>
                                <div class="user_info">
                                    <span> {{ userChat.title }}</span>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-8 col-xl-6 chat">
            <component v-for="userChat in userChats" :is="userChat.model" :key="userChat.id" :chat="userChat" :current-user="currentUser" :contacts="contacts" :ws="ws" v-show="currentChat && userChat.id === currentChat.id" @on-mounted="userChat.instance = $event"></component>
        </div>
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





    <div class="card scroll">
        <div class="card-header msg_head">
            <div class="d-flex bd-highlight">
                <div class="img_cont">
                    <img src="https://2.bp.blogspot.com/-8ytYF7cfPkQ/WkPe1-rtrcI/AAAAAAAAGqU/FGfTDVgkcIwmOTtjLka51vineFBExJuSACLcBGAs/s320/31.jpg" class="rounded-circle user_img">
                    <span class="online_icon"></span>
                </div>
                <div class="user_info">
                    <span>{{ chat.title }}</span>
                    <p class="text-white">{{ chat.messages.length }} messages - <a class="text-white" href="">{{ chat.users.length }} users</a></p>
                </div>
                <div class="dropdown">
                    <button class="btn text-white" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-expanded="false">
                        <i class="fa fa-ellipsis-v"></i>
                    </button>
                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                        <a class="dropdown-item" :href="'#addUsersModal' + chat.id" data-toggle="modal"><i class="fas fa-users"></i> Add users to group</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body msg_card_body" ref="scroll">

            <div class="d-flex mb-4" :class="{'justify-content-start' : currentUser.id !== message.user.id, 'justify-content-end' : currentUser.id === message.user.id}" v-for="message in chat.messages">
                <div class="img_cont_msg" v-if="currentUser.id !== message.user.id">
                    <img src="https://2.bp.blogspot.com/-8ytYF7cfPkQ/WkPe1-rtrcI/AAAAAAAAGqU/FGfTDVgkcIwmOTtjLka51vineFBExJuSACLcBGAs/s320/31.jpg" class="rounded-circle user_img_msg">
                </div>
                <div :class="{'msg_cotainer' : currentUser.id !== message.user.id, 'msg_cotainer_send' : currentUser.id === message.user.id}">
                    <p class="user-name"><a href="#">{{ message.user.name }}</a></p>
                    {{ message.message }}
                    <span :class="{'msg_time' : currentUser.id !== message.user.id, 'msg_time_send' : currentUser.id === message.user.id}">
                        {{ message.created_at }}
                    </span>
                </div>
                <div class="img_cont_msg" v-if="currentUser.id === message.user.id">
                    <img src="https://2.bp.blogspot.com/-8ytYF7cfPkQ/WkPe1-rtrcI/AAAAAAAAGqU/FGfTDVgkcIwmOTtjLka51vineFBExJuSACLcBGAs/s320/31.jpg" class="rounded-circle user_img_msg">
                </div>
            </div>
        </div>
        <div class="card-footer">
            <div class="input-group">
                <textarea v-model="messageContent" class="form-control type_msg" placeholder="Type your message..."></textarea>
                <div class="input-group-append" @click.prevent="sendMessage()">
                    <span class="input-group-text send_btn"><i class="fas fa-location-arrow"></i></span>
                </div>
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
    export default {
        name: "test"
    }
</script>

<style scoped conversation>
    .contacts_card {
        margin-bottom: 5%;
    }

    .actions {
        margin-left: 10%;
    }
</style>

<style scoped chat>
    .scroll {
        height: 45rem;
        background: linear-gradient(270deg, #000000, #a52424, #0017d5);
        background-size: 600% 600%;
        -webkit-animation: ChatBackground 60s ease infinite;
        -moz-animation: ChatBackground 60s ease infinite;
        -o-animation: ChatBackground 60s ease infinite;
        animation: ChatBackground 60s ease infinite;
    }

    .timeline-panel {
        background-color: #999999;
        border-color: #999999 !important;
        font-weight: bold;
        border-radius: 0.5rem !important;
        color: white;
    }

    .white-background {
        background-color: white !important;
        color: #212529 !important;
    }

    .msg_time, .msg_time_send {
        min-width: 10rem;
    }
</style>