@extends('layouts.admin')

@section('title', 'Edit Product · Med Alert')
@section('heading', 'Edit Product')

@section('content')
    <div class="max-w-3xl">
        @include('admin.products._form', [
            'action' => route('admin.products.update', $product),
            'method' => 'PUT',
            'submitLabel' => 'Save Changes',
        ])
    </div>
@endsection
