@php
    $all_notifications = auth()->user()->notifications;
    $unread_notifications = $all_notifications->where('read_at', null);
    $total_unread = count($unread_notifications);

@endphp

<li class="dropdown notifications-menu">
    <a href="#" class="dropdown-toggle load_notifications" data-toggle="dropdown" id="show_unread_notifications"
        data-loaded="false">
        <i class="fas fa-bell"></i>
        <span class="label label-warning notifications_count">{{ $total_unread > 0 ? $total_unread : '' }}</span>
    </a>
    <ul class="dropdown-menu">
        <li>
            <ul class="menu" id="notifications_list" style="border-radius: 15px;">
                @if ($unread_notifications->isEmpty())
                    <li style="padding: 5px;" class="notification-item notification-none">
                        @lang('lang_v1.no_notifications_found')</li>
                @else
                    @foreach ($unread_notifications as $notification)
                        @php
                            $notificationClass = $notification->read_at ? 'notification-read' : 'notification-unread';
                            $notificationType = $notification->data['type'] ?? null;
                            $notificationIssueId = $notification->data['issue_id'] ?? null;
                            $str_no = $notification->data['str_no'] ?? null;
                            $ptr_no = $notification->data['ptr_no'] ?? null;

                        @endphp

                        <li style="padding: 5px;" class="notification-item {{ $notificationClass }}"
                            data-notification-id="{{ $notification->id }}"
                            data-notification-message="{{ $notification->data['remark_message'] ?? '' }}"
                            data-issue-id="{{ $notification->data['issue_id'] ?? '' }}">
                            @if ($notificationType == 'remark')
                                <a href="{{ route('remarks', ['str_no' => $notification->data['str_no']]) }}">
                                    {!! $notification->data['message'] !!}
                                </a>
                            @elseif ($notificationType == 'inbox')
                                <a
                                    href="{{ route('viewMessage', ['remark_to_id' => $notification->data['remark_to_id'], 'remark_by_id' => $notification->data['remark_by_id']]) }}">
                                    {!! $notification->data['message'] !!}
                                </a>
                            @elseif ($notificationType == 'reject')
                                <a href="{{ route('sample-testing-reports.index') }}">
                                    {{ $notification->data['message'] }}
                                </a>
                            @elseif ($notificationType == 'approve')
                                <a href="{{ route('sample-testing-reports.index') }}">
                                    {!! $notification->data['message'] !!}
                                </a>
                            @elseif ($notificationType == 'reminder')
                                <a href="{{ route('essentials::reminder.index') }}">
                                    {{ $notification->data['message'] }}
                                </a>
                            @elseif ($notificationType == 'demand')
                                <a href="{{ route('demand.index') }}">
                                    {!! $notification->data['message'] !!}
                                </a>
                            @elseif ($notificationType == 'demand reject')
                                <a href="{{ route('demand.index') }}">
                                    {!! $notification->data['message'] !!}
                                </a>
                            @elseif ($notificationType == 'ptr_approved')
                                @php
                                    $ptr_no = $notification->data['ptr_no'] ?? null;

                                @endphp
                                @if ($ptr_no)
                                    <a href="{{ route('view-pre-test-report', ['ptr_no' => $ptr_no]) }}">
                                        {!! $notification->data['message'] !!}
                                    </a>
                                @else
                                    <span>{!! $notification->data['message'] !!}</span>
                                @endif
                            @elseif ($notificationType == 'str_created')
                                @php
                                    $str_no = $notification->data['str_no'] ?? null;
                                @endphp
                                @if ($str_no)
                                    <a
                                        href="{{ route('sample-testing-reports.show', ['sample_testing_report' => $str_no]) }}">
                                        {!! $notification->data['message'] !!}
                                    </a>
                                @else
                                    <span>{!! $notification->data['message'] !!}</span>
                                @endif
                            @elseif ($notificationType == 'demand approved')
                                <a href="{{ route('demand.index') }}">
                                    {!! $notification->data['message'] !!}
                                </a>
                            @elseif ($notificationType == 'assign_test')
                                <a href="javascript:void(0)" class="notification-link">
                                    {!! $notification->data['message'] !!}
                                </a>
                            @elseif ($notificationType == 'form_test')
                                <a href="{{ route('samplegroup.index') }}">
                                    {!! $notification->data['message'] !!}
                                </a>
                            @elseif ($notificationType == 'test_approve')
                                <a href="{{ route('samplegroup.index') }}">
                                    {!! $notification->data['message'] !!}
                                </a>
                            @elseif ($notificationType == 'batch_expiry')
                                <a href="{{ url('batch/expired') }}">
                                    {!! $notification->data['message'] !!}
                                </a>
                            @endif
                        </li>
                    @endforeach
                @endif
            </ul>
        </li>
        @if ($all_notifications->count() > 10)
            <li class="footer load_more_li">
                <a href="#" class="load_more_notifications">@lang('lang_v1.load_more')</a>
            </li>
        @endif
    </ul>
</li>

<input type="hidden" id="notification_page" value="1">
{{-- <script>
$(document).ready(function() {
    if ($('#notifications_list .notification-item').not('.notification-none').length > 0) {
        $('#notifications_list .notification-none').remove();
    }
    $('.load_more_notifications').on('click', function(e) {
        e.preventDefault();
    });
});
</script> --}}
