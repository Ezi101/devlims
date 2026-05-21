<!-- resources/views/delivery_persons/edit.blade.php -->

@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>@lang('method.edit_source')</h1>
        <form action="{{ route('sources_custom.update', $sourceCustomer->id) }}" method="POST"
            enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label for="name">@lang('method.name')</label>
                <input type="text" name="name" class="form-control" value="{{ $sourceCustomer->name }}" required>
            </div>
            {{-- <div class="form-group">
                <label for="cnic">CNIC:</label>
                <input type="text" name="cnic" class="form-control" value="{{ $sourceCustomer->cnic }}" required>
            </div> --}}
            <div class="form-group">
                <label for="phone">@lang('method.phone')</label>
                <input type="text" name="phone" class="form-control" value="{{ $sourceCustomer->phone }}" required>
            </div>
           
            <button type="submit" class="btn btn-primary">@lang('messages.update')</button>
        </form>
    </div>
@endsection
