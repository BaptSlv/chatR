<form action="{{ route('removeContact', [$contact]) }}" method="post">
    @csrf
    <button type="submit">Remove</button>
</form>
