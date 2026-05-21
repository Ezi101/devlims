@if (empty($notifications_data))
    <li style="padding: 5px;" class="notification-item">
        @lang('lang_v1.no_notifications_found')
    </li>
@else
    @foreach ($notifications_data as $notification)
        @php
            $data = $notification['data'] ?? [];
            $notificationType = $data['type'] ?? null;
            $notificationClass = $notification['read_at'] ? 'notification-read' : 'notification-unread';
            $message = $data['message'] ?? '';
        @endphp
        <li style="padding: 5px;" class="notification-item {{ $notificationClass }}">
            @if ($notificationType == 'inbox')
                <a
                    href="{{ route('viewMessage', [
                        'remark_to_id' => $data['remark_to_id'] ?? 0,
                        'remark_by_id' => $data['remark_by_id'] ?? 0,
                    ]) }}">
                    {!! $message !!}
                </a>
            @elseif ($notificationType == 'remark')
                <a href="{{ route('remarks', ['str_no' => $data['str_no'] ?? '']) }}">
                    {!! $message !!}
                </a>
            @elseif (in_array($notificationType, ['reject', 'approve', 'str_created']))
                <a href="{{ route('sample-testing-reports.index') }}">
                    {!! $message !!}
                </a>
            @elseif (in_array($notificationType, ['demand', 'demand approved', 'demand reject']))
                <a href="{{ route('demand.index') }}">
                    {!! $message !!}
                </a>
            @elseif ($notificationType == 'ptr_approved')
                @if (!empty($data['ptr_no']))
                    <a href="{{ route('view-pre-test-report', ['ptr_no' => $data['ptr_no']]) }}">
                        {!! $message !!}
                    </a>
                @else
                    <span>{!! $message !!}</span>
                @endif
            @else
                <span>{!! $message !!}</span>
            @endif
        </li>
    @endforeach
@endif
