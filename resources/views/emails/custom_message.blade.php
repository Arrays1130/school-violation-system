<x-mail::message>
# {{ $messageSubject }}

{{ $customMessageContent }}

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
