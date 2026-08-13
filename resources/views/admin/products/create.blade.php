@extends('layouts.admin')

@section('title', 'Add Product · Med Alert')
@section('heading', 'Add Product')

@section('content')
    <div class="max-w-3xl">
        @include('admin.products._form', [
            'action' => route('admin.products.store'),
            'method' => 'POST',
            'submitLabel' => 'Create Product',
        ])
    </div>
@endsection
