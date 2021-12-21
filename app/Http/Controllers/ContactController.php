<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddContactRequest;
use App\Invitation;
use App\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;

class ContactController extends Controller
{
    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        return view('contacts.index');
    }

    public function acceptInvitation(Request $request, User $contact)
    {
        /** @var User $user */
        $user = Auth::user();
        $invitation = Invitation::getSpecificInvitation($user, $contact)->first();

        $user->contacts()->attach($contact->id);
        $contact->contacts()->attach($user->id);

        $invitation->delete();

        return redirect()->back();
    }

    public function remove(Request $request, User $contact)
    {
        /** @var User $user */
        $user = Auth::user();
        $chats = $user->chats()->whereHas('users', function(Builder $query) use($contact) {
            $query->where('user_id', '=', $contact->id);
        })->has('users', '=', 2)->get();
        dd($chats);

        $chats->each(function ($chat) {
            //Faire le softDelete des chats récupérés au dessus
        });

        $user->contacts()->detach($contact->id);
        $contact->contacts()->detach($user->id);

        return redirect()->back();
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function getInvitations()
    {
        /** @var User $user */
        $user = Auth::user();
        $invitationIds = Invitation::getUserInvitations($user)->pluck('from_id');

        $invitationUsers = User::query()->find($invitationIds);

        return Datatables::of($invitationUsers)
            ->addColumn('actions', function ($invitationUser) {
                return view('invitations.actions', ['invitationUser' => $invitationUser])->render();
            })
            ->rawColumns(['actions'])
            ->make(true);
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function getUserContacts()
    {
        /** @var User $user */
        $user = Auth::user();
        $contacts = $user->contacts;

        return Datatables::of($contacts)
            ->addColumn('actions', function ($contact) {
                return view('contacts.actions', ['contact' => $contact])->render();
            })
            ->rawColumns(['actions'])
            ->make(true);
    }

    /**
     * @param AddContactRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function add(AddContactRequest $request)
    {
        /** @var User $user */
        $user = Auth::user();
        $contact = User::getUserByEmail($request->input('email'))->first();
        $isAlreadyInvited = Invitation::query()->where('from_id', '=', $user->id)->where('to_id', '=', $contact->id)->exists();
        $hasAlreadyBeenInvited = Invitation::query()->where('from_id', '=', $contact->id)->where('to_id', '=', $user->id)->exists();
        $isAlreadyLinked = $user->contacts()->where('contact_id', '=', $contact->id)->exists();

        if (is_null($contact)) {

            //Todo message no contact found
            return redirect()->back();
        } elseif ($contact->id === $user->id) {
            return redirect()->back();
        } elseif ($isAlreadyInvited) {

            //Todo message invitation already sent
            return redirect()->back();
        } elseif ($isAlreadyLinked) {

            //Todo message already in contact
            return redirect()->back();
        } elseif ($hasAlreadyBeenInvited) {
            $this->acceptInvitation($request, $contact);

            //Todo message success you are now in contact with $contact
            return redirect()->back();
        }

        Invitation::query()->create([
            'from_id' => $user->id,
            'to_id' => $contact->id,
        ]);

        //Todo message success
        return redirect()->back();
    }
}
