@extends('layouts.app')

@section('title', __('CAPA Details'))

@section('content')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>@lang('devices.device')
            <small>@lang('lang_v1.manage_equipment')</small>
        </h1>
    </section>
    <!-- Main content -->
    <section class="content">
        @include('instrument.partials.device_nav', ['id' => $id])


        <div class="row">
            <div class="col-12">
                    <div class="col-md-4 col-sm-6 col-xs-12 col-custom ">
                        <div class="info-box info-box-new-style">
                            <span class="info-box-icon bg-aqua"><i class="fa-solid fa-share-from-square"
                                    style="font-size: 2rem"></i></span>

                            <div class="info-box-content3 card">
                                <h5 class=""> {{ __('capa.capa_issue') }}</h5>
                                <h4 class=""> {{ $total_capa }}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-6 col-xs-12 col-custom">
                        <div class="info-box info-box-new-style">
                            <span class="info-box-icon bg-aqua"><i class="fa-solid fa-bars-progress"
                                    style="font-size: 2rem"></i></span>

                            <div class="info-box-content3 card">
                                <p class="info-box-number2">
                                    <span class="">
                                        <h5 class="">{{ __('capa.capa_progress') }}</h5>
                                        <h4>{{ $progress_capa }}</h4>

                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 col-sm-6 col-xs-12 col-custom">
                        <div class="info-box info-box-new-style">
                            <span class="info-box-icon bg-aqua"><i class="fa-solid fa-check" style="font-size: 2rem"></i></span>

                            <div class="info-box-content3" style="position: relative; right: -10px">
                                <p class="info-box-number2 card">
                                    <span>
                                        <h5 class="">{{ __('capa.capa_completed') }}</h5>
                                        <h4 class=""> {{ $completed_capa }} </h4>
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table dataTable table-striped ajax_view hide-footer">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ __('capa.capa_date') }}</th>
                        <th>{{ __('capa.capa_type') }}</th>
                        <th>{{ __('capa.capa_desc') }}</th>
                        <th>{{ __('capa.capa_assign') }}</th>
                        <th>{{ __('capa.capa_status') }}</th>
                        @can('capa.delete')
                            <th class="no-print">Action</th>
                        @endcan
                    </tr>
                </thead>
                <tbody>

                    @foreach ($capa as $key => $remark)
                        @if ($remark->status == 'completed')
                            @php
                                $bg = 'label-success';
                            @endphp
                        @endif

                        @if ($remark->status == 'pending')
                            @php
                                $bg = 'label-warning';
                            @endphp
                        @endif
                        <tr>
                            <td>{{ $key + 1 }}</td>
                            <td>{{ $remark->created_at->format('d M,Y') }}</td>
                            <td>{{ $remark->type }}</td>
                            <td>{{ $remark->remarks }}</td>
                            <td>{{ optional($remark->user)->username }}</td>
                            <td>
                                <div class="label  {{ $bg }}">{{ $remark->status }}</div>
                            </td>
                            <td style="padding: 10px; text-align: left;">
                                <div class="dropdown">

                                    <button class="btn btn-primary btn-xs dropdown-toggle" type="button"
                                        id="actionDropdown" data-toggle="dropdown" aria-haspopup="true"
                                        aria-expanded="false">
                                        Actions <span class="caret"></span>
                                    </button>
                                    <div class="dropdown-menu" aria-labelledby="actionDropdown">
                                        @can('capa.delete')
                                            <a class="dropdown-item btn deleteCapa"
                                                href="{{ action([\App\Http\Controllers\CapaController::class, 'destroy'], [$remark->id]) }}">
                                                <i class="fas fa-trash"></i> Delete
                                            </a>
                                        @endcan
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
@endsection
