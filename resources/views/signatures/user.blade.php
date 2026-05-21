@extends('layouts.app')
@section('title', __('View Signatures'))

@section('content')
    <div class="container">
        <h1 class="mb-4">User Signature</h1>

        <div class="card">
            <div class="box box-primary">

                <div class="card-body mt-10">
                    @if ($userSignature)
                        <p class="card-text"><strong>Name:</strong> {{ $userSignature->name }}</p>
                        <p class="card-text"><strong>Employee ID:</strong> {{ $userSignature->employee_id }}</p>
                        <p class="card-text"><strong>Designation:</strong> {{ $userSignature->designation }}</p>
                        <p class="card-text"><strong>E-Signature:</strong> {{ $userSignature->unique_signature }}</p>
                    @else
                        <p class="card-text">No signature found for this user.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
