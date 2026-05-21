<!-- resources/views/delivery_persons/edit.blade.php -->

@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Edit Delivery Person</h1>
        <form action="{{ route('delivery_persons.update', $deliveryPerson->id) }}" method="POST"
            enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label for="name">@lang('messages.name')</label>
                <input type="text" name="name" class="form-control" value="{{ $deliveryPerson->name }}" required>
            </div>
            <div class="form-group">
                <label for="cnic">@lang('messages.cnic')</label>
                <input type="text" name="cnic" class="form-control" value="{{ $deliveryPerson->cnic }}" required>
            </div>
            <div class="form-group">
                <label for="phone">@lang('messages.phone')</label>
                <input type="text" name="phone" class="form-control" value="{{ $deliveryPerson->phone }}" required>
            </div>
            <div class="form-group">
                <label for="picture">@lang('messages.picture')</label>
                <input type="file" name="picture" class="form-control">
                @if ($deliveryPerson->picture)
                    <img src="{{ asset('storage/' . $deliveryPerson->picture) }}" width="100">
                @endif
            </div>
            <button type="submit" class="btn btn-primary">@lang('messages.update')</button>
        </form>
    </div>
@endsection
