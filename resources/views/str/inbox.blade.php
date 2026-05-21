@extends('layouts.app')
@section('title', __('lang_v1.inbox'))

@section('content')
    <section class="content-header">
        <h1>@lang('lang_v1.inbox') <small>@lang('lang_v1.manage_inbox')</small></h1>
    </section>

    <section class="content">
        <div class="wa-wrapper">

            {{-- LEFT SIDEBAR - Conversations --}}
            <div class="wa-sidebar">
                <div class="wa-sidebar-header">
                    <span>Chats</span>
                    @can('inbox.send_message')
                        <a class="inbox-model wa-new-chat" title="New Message">
                            <i class="fa fa-edit"></i>
                        </a>
                    @endcan
                </div>

                {{-- New Chat Panel --}}
                <div class="wa-new-panel" id="newChatPanel" style="display:none; flex-direction:column; flex:1;">
                    <div class="wa-new-panel-header">
                        <button id="closeNewPanel"><i class="fa fa-arrow-left"></i></button>
                        <span>New Chat</span>
                    </div>
                    <div
                        style="padding:8px 14px; background:#f0f2f5; border-bottom:1px solid #e9ecef; display:flex; align-items:center; gap:10px;">
                        <i class="fa fa-search" style="color:#aaa; font-size:13px;"></i>
                        <input type="text" id="userSearch" placeholder="Search by name..."
                            style="border:none; background:transparent; outline:none; font-size:14px; width:100%;">
                    </div>
                    <div class="wa-user-list" id="userSearchResults" style="flex:1; overflow-y:auto;"></div>
                </div>

                {{-- Existing Contacts Search --}}
                <div class="wa-search" id="contactSearchBar">
                    <i class="fa fa-search"></i>
                    <input type="text" id="contactSearch" placeholder="Search or start new chat">
                </div>

                {{-- Contacts List --}}
                <div class="wa-contacts" id="contactsList">
                    @foreach ($remarks as $key => $remark)
                        @php
                            $authId = auth()->id();
                            $person = $remark->message_from == $authId ? $remark->remarkTo : $remark->remarkBy;
                            $fullName = trim(
                                ($person->surname ?? '') .
                                    ' ' .
                                    ($person->first_name ?? '') .
                                    ' ' .
                                    ($person->last_name ?? ''),
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
                </div>
            </div>

            {{-- RIGHT - Chat Area --}}
            <div class="wa-main" id="waChatArea">

                {{-- Empty State --}}
                <div class="wa-empty" id="waEmpty">
                    <div class="wa-empty-icon"><i class="fa fa-comments"></i></div>
                    <h3>AFMSL LIMS Messaging</h3>
                    <p>Select a conversation from the left to start chatting</p>
                </div>

                {{-- Chat Content loads here --}}
                <div id="waChatContent" style="display:none; flex-direction:column; width:100%; height:100%;"></div>

            </div>

        </div>
    </section>

    <style>
        .wa-wrapper {
            display: flex;
            height: calc(100vh - 160px);
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
            margin: 0 5px;
        }

        /* SIDEBAR */
        .wa-sidebar {
            width: 340px;
            min-width: 340px;
            display: flex;
            flex-direction: column;
            border-right: 1px solid #e9ecef;
            background: #fff;
            overflow: hidden;
            position: relative;
        }

        .wa-sidebar-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px 18px;
            background-color: #2b80ec;
            background-image: linear-gradient(to right, #2b80ec, #1d1f33);
            color: white;
            font-size: 18px;
            font-weight: 700;
            flex-shrink: 0;
        }

        .wa-new-chat {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            cursor: pointer;
            font-size: 15px;
            transition: background 0.2s;
            text-decoration: none;
        }

        .wa-new-chat:hover {
            background: rgba(255, 255, 255, 0.35);
            color: white;
        }

        /* New Chat Panel */
        .wa-new-panel {
            position: absolute;
            top: 0;
            left: 0;
            width: 340px;
            height: 100%;
            background: #fff;
            z-index: 10;
            display: flex;
            flex-direction: column;
        }

        .wa-new-panel-header {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 14px 16px;
            background-color: #2b80ec;
            background-image: linear-gradient(to right, #2b80ec, #1d1f33);
            color: white;
            font-size: 16px;
            font-weight: 600;
            flex-shrink: 0;
        }

        .wa-new-panel-header button {
            background: none;
            border: none;
            color: white;
            font-size: 16px;
            cursor: pointer;
            padding: 0;
            line-height: 1;
        }

        .wa-user-list .wa-contact {
            border-bottom: 1px solid #f0f0f0;
        }

        /* Search */
        .wa-search {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 14px;
            background: #f0f2f5;
            border-bottom: 1px solid #e9ecef;
            flex-shrink: 0;
        }

        .wa-search i {
            color: #aaa;
            font-size: 13px;
        }

        .wa-search input {
            border: none;
            background: transparent;
            outline: none;
            font-size: 14px;
            width: 100%;
            color: #333;
        }

        /* Contacts */
        .wa-contacts {
            flex: 1;
            overflow-y: auto;
        }

        .wa-contact {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 13px 16px;
            cursor: pointer;
            border-bottom: 1px solid #f5f5f5;
            transition: background 0.15s;
            text-decoration: none;
            color: inherit;
        }

        .wa-contact:hover {
            background: #f0f4f8;
            text-decoration: none;
            color: inherit;
        }

        .wa-contact.active {
            background: #e8f0fe;
            border-left: 3px solid #2b80ec;
        }

        .wa-avatar {
            width: 46px;
            height: 46px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 18px;
            color: white;
            flex-shrink: 0;
        }

        .wa-contact-info {
            flex: 1;
            min-width: 0;
        }

        .wa-contact-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 3px;
        }

        .wa-contact-name {
            font-size: 14px;
            font-weight: 600;
            color: #1a1a1a;
        }

        .wa-contact-time {
            font-size: 11px;
            color: #999;
            flex-shrink: 0;
        }

        .wa-contact-preview {
            font-size: 12px;
            color: #999;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* MAIN AREA */
        .wa-main {
            flex: 1;
            background: #f0f2f5;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .wa-empty {
            text-align: center;
            color: #aaa;
            padding: 40px;
        }

        .wa-empty-icon {
            font-size: 70px;
            margin-bottom: 20px;
            opacity: 0.2;
            color: #2b80ec;
        }

        .wa-empty h3 {
            color: #666;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .wa-empty p {
            font-size: 14px;
        }

        /* CHAT CONTENT */
        #waChatContent {
            display: flex;
            flex-direction: column;
            width: 100%;
            height: 100%;
        }

        .chat-header {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 12px 20px;
            background: #fff;
            border-bottom: 1px solid #e0e0e0;
            flex-shrink: 0;
        }

        .chat-header-avatar {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: #25a244;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 17px;
            color: white;
            flex-shrink: 0;
        }

        .chat-header-name {
            font-size: 15px;
            font-weight: 600;
            color: #1c2e4a;
        }

        .chat-header-sub {
            font-size: 11px;
            color: #888;
        }

        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 20px 24px;
            /* background: #e5ddd5; */
            display: flex;
            flex-direction: column;
            gap: 8px;
            min-height: 0;
        }

        .chat-input-area {
            display: flex;
            align-items: flex-end;
            gap: 10px;
            padding: 12px 16px;
            background: #f0f0f0;
            border-top: 1px solid #ddd;
            flex-shrink: 0;
        }

        #replyInput {
            flex: 1;
            border-radius: 22px;
            border: none;
            padding: 10px 18px;
            resize: none;
            font-size: 14px;
            outline: none;
            background: #fff;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            max-height: 100px;
            font-family: inherit;
        }

        #replySendBtn {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            border: none;
            background: #1c2e4a;
            color: white;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 15px;
        }

        #replySendBtn:hover {
            background: #25a244;
        }

        /* MSG BUBBLES */
        .msg-row {
            display: flex;
            align-items: flex-end;
            gap: 8px;
        }

        .msg-mine {
            justify-content: flex-end;
        }

        .msg-theirs {
            justify-content: flex-start;
        }

        .msg-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #1c2e4a;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 13px;
            color: white;
            flex-shrink: 0;
        }

        .msg-bubble {
            max-width: 60%;
            padding: 9px 13px 6px;
            border-radius: 10px;
            font-size: 14px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.12);
        }

        .bubble-mine {
            background: #1361df;
            color: #fff;
            border-bottom-right-radius: 3px;
        }

        .bubble-theirs {
            background: #ffffff;
            color: #333;
            border-bottom-left-radius: 3px;
        }

        .msg-sender {
            font-size: 11px;
            font-weight: 700;
            color: #25a244;
            margin-bottom: 3px;
        }

        .msg-text {
            line-height: 1.5;
            word-break: break-word;
            white-space: pre-wrap;
        }

        .msg-time {
            font-size: 10px;
            opacity: 0.55;
            margin-top: 4px;
            text-align: right;
        }

        .bubble-theirs .msg-time {
            text-align: left;
        }

        .pace-active {
            display: none !important;
        }

        .pace-progress,
        .pace {
            display: none !important;
        }
    </style>
@endsection

@section('javascript')
    <script>
        if (typeof Pace !== 'undefined') {
            Pace.stop();
            Pace.options = {
                restartOnRequestAfter: false
            };
        }

        var currentChatUrl = null;
        var pollingInterval = null;
        var lastMessageId = 0;

        $(document).ready(function() {

            // ── Notification se aaya → auto chat open ──
            var urlParams = new URLSearchParams(window.location.search);
            var openTo = urlParams.get('open_to');
            var openBy = urlParams.get('open_by');
            if (openTo && openBy) {
                var chatUrl = '/view/inbox/message/to/' + openTo + '/by/' + openBy;
                currentChatUrl = chatUrl;
                loadChat(chatUrl);
                $('.wa-contact[data-url*="/to/' + openTo + '/"]').addClass('active');
            }

            // ── Existing contact click ──
            $(document).on('click', '.wa-contact[data-url]', function(e) {
                e.preventDefault();
                var url = $(this).data('url');
                currentChatUrl = url;
                lastMessageId = 0; // reset for new chat
                $('.wa-contact').removeClass('active');
                $(this).addClass('active');
                loadChat(url);
                $('#newChatPanel').hide();
            });

            // ── New user click ──
            $(document).on('click', '.new-user-contact', function(e) {
                e.preventDefault();
                var userId = $(this).data('id');
                var url = '/view/inbox/new/' + userId;
                currentChatUrl = url;
                lastMessageId = 0;
                loadChat(url);
                $('#newChatPanel').hide();
                $('#userSearch').val('');
            });

            // ── Edit icon: show new chat panel ──
            $('.inbox-model').on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $('#newChatPanel').show();
                $('#userSearch').focus();
                loadUsers('');
            });

            // ── Close new chat panel ──
            $('#closeNewPanel').on('click', function() {
                $('#newChatPanel').hide();
                $('#userSearch').val('');
            });

            // ── User search ──
            $('#userSearch').on('input', function() {
                loadUsers($(this).val());
            });

            // ── Existing contacts search ──
            $('#contactSearch').on('input', function() {
                var q = $(this).val().toLowerCase();
                $('.wa-contact').each(function() {
                    var name = $(this).find('.wa-contact-name').text().toLowerCase();
                    $(this).toggle(name.includes(q));
                });
            });

            // ── Send on Enter ──
            $(document).on('keydown', '#replyInput', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    sendReply();
                }
            });

            // ── Send button ──
            $(document).on('click', '#replySendBtn', function() {
                sendReply();
            });
        });

        // ── Load chat via AJAX (with spinner) ──
        function loadChat(url) {
            stopPolling();
            $('#waEmpty').hide();
            $('#waChatContent').css('display', 'flex').html(
                '<div style="display:flex;align-items:center;justify-content:center;height:100%;width:100%;">' +
                '<i class="fa fa-spinner fa-spin fa-2x" style="color:#aaa"></i></div>'
            );

            $.ajax({
                url: url,
                type: 'GET',
                global: false,
                data: {
                    ajax: 1
                },
                success: function(response) {
                    $('#waChatContent').html(response);
                    scrollToBottom();
                    initLastMessageId();
                    startPolling();
                },
                error: function() {
                    $('#waChatContent').html('<div style="padding:20px;color:red;">Error loading chat.</div>');
                }
            });
        }

        // ── Silent reload (no spinner) ──
        function loadChatSilent(url) {
            $.ajax({
                url: url,
                type: 'GET',
                global: false,
                data: {
                    ajax: 1
                },
                success: function(response) {
                    $('#waChatContent').html(response);
                    scrollToBottom();
                }
            });
        }

        // ── lastMessageId initialize karo ──
        function initLastMessageId() {
            $.ajax({
                url: '/inbox/check-new',
                type: 'GET',
                global: false,
                data: {
                    last_id: 0,
                    chat_url: currentChatUrl
                },
                success: function(response) {
                    lastMessageId = response.last_id;
                }
            });
        }

        // ── Polling start ──
        function startPolling() {
            stopPolling();
            pollingInterval = setInterval(function() {
                if (!currentChatUrl) return;
                checkNewMessages();
            }, 3000); // har 3 second
        }

        // ── Polling stop ──
        function stopPolling() {
            if (pollingInterval) {
                clearInterval(pollingInterval);
                pollingInterval = null;
            }
        }

        // ── Naye messages check karo ──
        function checkNewMessages() {
            $.ajax({
                url: '/inbox/check-new',
                type: 'GET',
                global: false,
                data: {
                    last_id: lastMessageId,
                    chat_url: currentChatUrl
                },
                success: function(response) {
                    if (response.has_new) {
                        lastMessageId = response.last_id;
                        loadChatSilent(currentChatUrl); // chat update
                        loadSidebar(); // sidebar update
                    }
                }
            });
        }

        // ── Load users for new chat ──
        function loadUsers(query) {
            $.ajax({
                url: '/inbox/search-users',
                global: false,
                data: {
                    q: query
                },
                success: function(users) {
                    var html = '';
                    users.forEach(function(u) {
                        var initial = u.full_name.trim().charAt(0).toUpperCase();
                        html += '<a href="#" class="wa-contact new-user-contact" data-id="' + u.id +
                            '">' +
                            '<div class="wa-avatar" style="background:#2b80ec">' + initial + '</div>' +
                            '<div class="wa-contact-info">' +
                            '<div class="wa-contact-name">' + u.full_name.trim() + '</div>' +
                            '<div class="wa-contact-preview">Click to start chat</div>' +
                            '</div></a>';
                    });
                    $('#userSearchResults').html(html ||
                        '<p style="padding:16px;color:#aaa;">No users found</p>');
                }
            });
        }

        // ── Send reply ──
        function sendReply() {
            var message = $('#replyInput').val().trim();
            var toUser = $('#remarkTo').val();
            if (!message || !toUser) return;

            $('#replySendBtn').prop('disabled', true);

            $.ajax({
                url: '/inbox/store',
                method: 'POST',
                global: false,
                data: {
                    _token: '{{ csrf_token() }}',
                    'remarks_to[]': [toUser],
                    remarks_description: message
                },
                success: function() {
                    $('#replyInput').val('');
                    loadChatSilent(currentChatUrl); // no spinner, no reload
                    loadSidebar();
                },
                complete: function() {
                    $('#replySendBtn').prop('disabled', false);
                }
            });
        }

        // ── Sidebar refresh ──
        function loadSidebar() {
            $.ajax({
                url: '/inbox/sidebar',
                type: 'GET',
                global: false,
                success: function(html) {
                    $('#contactsList').html(html);
                    // Active class wapas lagao
                    if (currentChatUrl) {
                        $('.wa-contact[data-url="' + currentChatUrl + '"]').addClass('active');
                    }
                }
            });
        }

        // ── Scroll to bottom ──
        function scrollToBottom() {
            setTimeout(function() {
                var msgs = document.getElementById('viewMessages');
                if (msgs) msgs.scrollTop = msgs.scrollHeight;
            }, 50);
        }
    </script>
@endsection
