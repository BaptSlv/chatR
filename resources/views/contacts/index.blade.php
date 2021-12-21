@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <form action="{{ route('contact.add') }}" method="post">
                @csrf
                <input type="email" name="email" value="">
                <button type="submit">Add contact</button>
            </form>
        </div>
        <div class="col-md-6">
            <table id="table-invitations">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
        <div class="col-md-6">
            <table id="table-contacts">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
@endsection

@section('js')
    <script>
        $(document).ready(function () {
            $('#table-invitations').DataTable({
                ajax: {
                    url: "{{ route('contact.invitations') }}",
                },
                columns: [
                    {data: 'name'},
                    {data: 'email'},
                    {data: 'actions'},
                ],
                dom: 'rtSi',
                scrollY: '370',
                pageLength: 10,
                processing: true,
                serverSide: true,
                stateSave: true,
                searching: true,
            });

            $('#table-contacts').DataTable({
                ajax: {
                    url: "{{ route('contact.data') }}",
                },
                columns: [
                    {data: 'name'},
                    {data: 'email'},
                    {data: 'actions'},
                ],
                dom: 'rtSi',
                scrollY: '370',
                pageLength: 10,
                processing: true,
                serverSide: true,
                stateSave: true,
                searching: true,
            });
        });
    </script>
@endsection
