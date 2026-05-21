@php
    $firstRemark = $remarks->first();
    $authId = auth()->id();
    $person = $firstRemark
        ? ($firstRemark->message_from == $authId
            ? $firstRemark->remarkTo
            : $firstRemark->remarkBy)
        : null;
    $fullName = $person
        ? trim(($person->surname ?? '') . ' ' . ($person->first_name ?? '') . ' ' . ($person->last_name ?? ''))
        : 'Unknown';
    $initial = strtoupper(substr($person->first_name ?? 'U', 0, 1));
@endphp

<div class="chat-header">
    <div class="chat-header-avatar">{{ $initial }}</div>
    <div>
        <div class="chat-header-name">{{ $fullName }}</div>
        <div class="chat-header-sub">AFMSL LIMS Messaging</div>
    </div>
</div>

<div class="chat-messages" id="viewMessages">
    @foreach ($remarks as $remark)
        @php $isMine = $remark->message_from == $authId; @endphp
        <div class="msg-row {{ $isMine ? 'msg-mine' : 'msg-theirs' }}">
            @if (!$isMine)
                <div class="msg-avatar">
                    {{ strtoupper(substr(optional($remark->remarkBy)->first_name ?? 'U', 0, 1)) }}
                </div>
            @endif
            <div class="msg-bubble {{ $isMine ? 'bubble-mine' : 'bubble-theirs' }}">
                @if (!$isMine)
                    <div class="msg-sender">{{ optional($remark->remarkBy)->first_name }}</div>
                @endif
                <div class="msg-text">{{ $remark->message }}</div>
                <div class="msg-time">
                    {{ $remark->created_at->format('d M Y') }} &bull; {{ $remark->created_at->format('h:i A') }}
                    @if ($isMine)
                        <i class="fa fa-check-double" style="margin-left:4px; opacity:0.6;"></i>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="chat-input-area">
    <input type="hidden" id="remarkTo"
        value="{{ $remarks->first() ? (auth()->id() != $remarks->first()->remarkTo->id ? $remarks->first()->remarkTo->id : $remarks->first()->remarkBy->id) : '' }}">
    <textarea id="replyInput" placeholder="Type a message..." rows="1"></textarea>
    <button id="replySendBtn">
        <i class="fa fa-paper-plane"></i>
    </button>
</div>
