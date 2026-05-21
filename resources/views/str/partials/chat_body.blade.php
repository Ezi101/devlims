@php
    $authId = auth()->id();
    $firstRemark = $remarks->first();

    // Agar targetUser hai (new chat) toh seedha use karo
    if (isset($targetUser) && $targetUser) {
        $person = $targetUser;
    } else {
        $person = $firstRemark
            ? ($firstRemark->message_from == $authId
                ? $firstRemark->remarkTo
                : $firstRemark->remarkBy)
            : null;
    }

    $fullName = $person
        ? trim(($person->surname ?? '') . ' ' . ($person->first_name ?? '') . ' ' . ($person->last_name ?? ''))
        : 'Unknown';

    $initial = strtoupper(substr($person->first_name ?? 'U', 0, 1));

    // remarkTo value
    if (isset($targetUser) && $targetUser) {
        $toUserId = $targetUser->id;
    } elseif ($firstRemark) {
        $toUserId =
            auth()->id() != $firstRemark->remarkTo->id ? $firstRemark->remarkTo->id : $firstRemark->remarkBy->id;
    } else {
        $toUserId = '';
    }
@endphp

{{-- Header --}}
<div class="chat-header">
    <div class="chat-header-avatar">{{ $initial }}</div>
    <div>
        <div class="chat-header-name">{{ $fullName }}</div>
        <div class="chat-header-sub">AFMSL LIMS Messaging</div>
    </div>
</div>

{{-- Messages --}}
<div class="chat-messages" id="viewMessages">
    @forelse ($remarks as $remark)
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
    @empty
        <div style="text-align:center; color:#aaa; margin-top:60px;">
            <i class="fa fa-comments" style="font-size:50px; opacity:0.15; display:block; margin-bottom:12px;"></i>
            <p style="font-size:14px;">No messages yet. Say hello! 👋</p>
        </div>
    @endforelse
</div>

{{-- Input Area --}}
<div class="chat-input-area">
    <input type="hidden" id="remarkTo" value="{{ $toUserId }}">
    <textarea id="replyInput" placeholder="Type a message..." rows="1"></textarea>
    <button id="replySendBtn">
        <i class="fa fa-paper-plane"></i>
    </button>
</div>
