@extends('layouts.app')

@section('title', __('essentials::lang.knowledge_base'))

@section('content')
    @include('essentials::layouts.nav_essentials')
    <section class="content">
        <div class="box box-primary">
            <div class="box-header">
                <h3 class="box-title">@lang('essentials::lang.knowledge_base')</h3>
                <small> @lang('essentials::lang.manage_kb')</small>

                <div class="box-tools pull-right">
                    <a href="{{ action([\Modules\Essentials\Http\Controllers\KnowledgeBaseController::class, 'create']) }}"
                        class="btn btn-sm btn-primary">
                        <i class="fa fa-plus"></i> @lang('messages.add')
                    </a>
                </div>
            </div>
            <div class="box-body">
                <table class="table table-bordered">
                    <thead style="background-color:#d4d4d471;">
                        <tr>
                            <th>@lang('essentials::lang.title')</th>
                            <th>@lang('essentials::lang.content')</th>
                            <th>@lang('messages.actions')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($knowledge_bases as $kb)
                            <tr>
                                <td><strong><a
                                            href="{{ action([\Modules\Essentials\Http\Controllers\KnowledgeBaseController::class, 'show'], [$kb->id]) }}">{{ $kb->title }}</a></strong>
                                </td>
                                <td>{{ strip_tags($kb->content) }}</td>
                                <td>
                                    <a href="{{ action([\Modules\Essentials\Http\Controllers\KnowledgeBaseController::class, 'edit'], [$kb->id]) }}"
                                        class="btn btn-sm btn-primary" title="@lang('messages.edit')" data-toggle="tooltip"><i
                                            class="fas fa-edit"></i></a>
                                    <a href="{{ action([\Modules\Essentials\Http\Controllers\KnowledgeBaseController::class, 'destroy'], [$kb->id]) }}"
                                        class="btn btn-sm btn-danger delete-kb" title="@lang('messages.delete')"
                                        data-toggle="tooltip"><i class="fas fa-trash"></i></a>
                                    <a href="{{ action([\Modules\Essentials\Http\Controllers\KnowledgeBaseController::class, 'create']) }}?parent={{ $kb->id }}"
                                        class="btn btn-sm btn-success" title="@lang('essentials::lang.add_section')" data-toggle="tooltip"><i
                                            class="fas fa-plus"></i></a>
                                </td>
                            </tr>
                            @foreach ($kb->children as $section)
                                {{-- main entry of the knowledge base  --}}
                                <tr>
                                    <td style="border-bottom: 1px solid #c7c8cc;"><a style="margin-left: 20px;"
                                            href="{{ action([\Modules\Essentials\Http\Controllers\KnowledgeBaseController::class, 'show'], [$section->id]) }}"><i
                                                class="fa-solid fa-arrow-right"></i> {{ $section->title }}</a>
                                    </td>
                                    <td style="border-bottom: 1px solid #c7c8cc">{{ strip_tags($section->content) }}</td>
                                    <td style="border: 1px solid #C7C8CC;">
                                        <a style="margin-right: 10px;"
                                            href="{{ action([\Modules\Essentials\Http\Controllers\KnowledgeBaseController::class, 'edit'], [$section->id]) }}"
                                            class="text-primary" title="@lang('messages.edit')" data-toggle="tooltip"><i
                                                class="fas fa-edit"></i></a>
                                        <a style="margin-right: 10px;"href="{{ action([\Modules\Essentials\Http\Controllers\KnowledgeBaseController::class, 'destroy'], [$section->id]) }}"
                                            class="text-danger delete-kb" title="@lang('messages.delete')"
                                            data-toggle="tooltip"><i class="fas fa-trash"></i></a>
                                        <a style="margin-right: 10px;"href="{{ action([\Modules\Essentials\Http\Controllers\KnowledgeBaseController::class, 'create']) }}?parent={{ $section->id }}"
                                            class="text-success" title="@lang('essentials::lang.add_article')" data-toggle="tooltip"><i
                                                class="fas fa-plus"></i></a>
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </section>
@endsection

@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            $('.delete-kb').click(function(e) {
                e.preventDefault();
                swal({
                    title: LANG.sure,
                    icon: 'warning',
                    buttons: true,
                    dangerMode: true,
                }).then(willDelete => {
                    if (willDelete) {
                        var href = $(this).attr('href');
                        var data = $(this).serialize();

                        $.ajax({
                            method: 'DELETE',
                            url: href,
                            dataType: 'json',
                            data: data,
                            success: function(result) {
                                if (result.success == true) {
                                    toastr.success(result.msg);
                                } else {
                                    toastr.error(result.msg);
                                }

                                location.reload();
                            },
                        });
                    }
                });
            })
        });
    </script>
@endsection
