<thead>
    <tr>
        <th>#</th>
        {{-- <th>@lang('business.product')</th> --}}
        {{-- <th>@lang('method.formula')</th> --}}
        <th>@lang('method.test_id')</th>
        <th>@lang('messages.action')</th>
    </tr>
</thead>
<tbody>
    @foreach ($method as $m)
        <tr>
            <td>{{ $loop->iteration }}</td>
            {{-- <td>{{ $m->samples->name }}</td> --}}
            {{-- <td>{{ $m->formulas->formula }}</td> --}}
            <td>{{ @$m->test }}</td>
            <td>
                <a href="{{ action([\App\Http\Controllers\TestController::class, 'show'], ['test' => $m->test]) }}"
                    class="btn btn-primary btn-sm">
                    <i class="fa fa-eye fa-sm"></i> <!-- Added 'fa-sm' class here -->
                </a>
            </td>
        </tr>
    @endforeach
</tbody>
