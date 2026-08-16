@extends('layouts.admin')

@section('title', 'Edit User · Med Alert')
@section('heading', 'Edit User')

@section('content')
    <div class="max-w-3xl">
        @include('admin.users._form', [
            'action' => route('admin.users.update', $user),
            'method' => 'PUT',
            'submitLabel' => 'Save Changes',
        ])
    </div>
@endsection
