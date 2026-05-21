@foreach ($remarks as $remark)
    @php
        $authId = auth()->id();
        $person = $remark->message_from == $authId ? $remark->remarkTo : $remark->remarkBy;
        $fullName = trim(
            ($person->surname ?? '') . ' ' . ($person->first_name ?? '') . ' ' . ($person->last_name ?? ''),
        );
        $initial = strtoupper(substr($person->first_name ?? 'U', 0, 1));
        $colors = ['#1c2e4a', '#25a244', '#e67e22', '#8e44ad', '#2980b9', '#c0392b', '#16a085'];
        $color = $colors[$remark->id % count($colors)];
        $time = $remark->updated_at->format('h:i A');
    @endphp
    <a href="{{ url('/view/inbox/message/to/' . $remark->message_to . '/by/' . $remark->message_from) }}"
        class="wa-contact"
        data-url="{{ url('/view/inbox/message/to/' . $remark->message_to . '/by/' . $remark->message_from) }}">
        <div class="wa-avatar" style="background: {{ $color }}">{{ $initial }}</div>
        <div class="wa-contact-info">
            <div class="wa-contact-top">
                <span class="wa-contact-name">{{ $fullName ?: 'Unknown' }}</span>
                <span class="wa-contact-time">{{ $time }}</span>
            </div>
            <div class="wa-contact-preview">
                <i class="fa fa-comments" style="font-size:11px; opacity:0.5;"></i>
                Click to open chat
            </div>
        </div>
    </a>
@endforeach
