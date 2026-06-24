<x-mail::message>
# New contact form submission

You received a new message{{ $site->name ? ' on '.$site->name : '' }}.

- **Name:** {{ $message->name }}
- **Email:** {{ $message->email }}
@if (filled($message->phone))
- **Phone:** {{ $message->phone }}
@endif
@if (filled($message->subject))
- **Subject:** {{ $message->subject }}
@endif

**Message**

{{ $message->message }}

@if ($dashboardUrl)
<x-mail::button :url="$dashboardUrl">
View in dashboard
</x-mail::button>
@endif

Reply to this email to respond directly to {{ $message->name }}.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
