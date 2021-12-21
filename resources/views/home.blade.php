@extends('layouts.app')

@section('content')
    @auth()
        <conversations
            current-user-data="{{ auth()->user()->toJson() }}"
            contacts-data="{{ auth()->user()->contacts->toJson() }}"
            user-chats-data="{{ auth()->user()->chats->toJson() }}"
            web-route-data="{{ route('manageContacts') }}"
        ></conversations>
    @endauth
@endsection