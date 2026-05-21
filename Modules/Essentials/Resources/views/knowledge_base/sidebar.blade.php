@if (count($knowledge_base->children) > 0)

    <div class="box-group" id="accordian">
        @foreach ($knowledge_base->children as $section)
            <div class="panel box box-primary" style="margin-bottom: 0;">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        <a data-toggle="collapse" data-parent="#accordian" href="#collapse_{{ $section->id }}"
                            @if ($loop->index == 0) aria-expanded="true" @endif>{{ $section->title }}
                        </a>
                    </h3>

                </div>
                <div id="collapse_{{ $section->id }}"
                    class="panel-collapse collapse @if ($section_id == $section->id) in @endif"
                    aria-expanded="{{ $section_id == $section->id ? 'true' : 'false' }}">
                    <div class="box-body" style="padding: 1px 10px;">
                        @if (count($section->children) > 0)
                            <ul class="list-group">
                                @foreach ($section->children as $article)
                                    <li
                                        class="knowlede-base-inner-menu-side-li @if ($article_id == $article->id) bg-info @endif">
                                        <a class="text-default"
                                            href="{{ action([\Modules\Essentials\Http\Controllers\KnowledgeBaseController::class, 'show'], [$article->id]) }}">
                                            {{ $article->title }}
                                        </a>
                                        @can('knowledge-base.edit')
                                            <a class="text-default pull-right"
                                                href="{{ action([\Modules\Essentials\Http\Controllers\KnowledgeBaseController::class, 'edit'], [$article->id]) }}"
                                                title="@lang('messages.edit')" data-toggle="tooltip">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endcan
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>

            </div>
        @endforeach
    </div>

@endif
<style>
    .knowlede-base-inner-menu-side-li {
        padding: 5px 10px;
        list-style: none;
        border-radius: 4px;
        border: 0.5px solid #E7E7E7;
        margin: 2px;
    }
</style>
