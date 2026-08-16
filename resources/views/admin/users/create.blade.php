@extends('layouts.admin')

@section('title', 'Create User · Med Alert')
@section('heading', 'Create User')

@section('content')
    <div class="max-w-3xl">
        @include('admin.users._form', [
            'action' => route('admin.users.store'),
            'method' => 'POST',
            'submitLabel' => 'Create Account',
        ])
    </div>
@endsection
