@forelse($messages as $msg)
    <div class="mb-2">
        <strong>{{ $msg->sender->name }}:</strong>

        @if ($msg->message)
            <p>{{ $msg->message }}</p>
        @endif

        @if ($msg->attachments && count($msg->attachments))
            @foreach ($msg->attachments as $attachment)
                <a href="{{ asset($attachment->file_path) }}" target="_blank">{{ $attachment->file_name }}</a><br>
            @endforeach
        @endif

        <small class="text-muted">{{ $msg->created_at->diffForHumans() }}</small>
    </div>
@empty
    <p class="text-muted">No messages yet.</p>
@endforelse
