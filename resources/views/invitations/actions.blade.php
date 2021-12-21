<form action="{{ route('acceptInvitation', [$invitationUser]) }}" method="post">
    @csrf
    <button type="submit">Validate</button>
</form>
