@component('mail::message')

{{-- Greeting --}}
@if (! empty($greeting))
# {{ $greeting }}
@else
@if ($level === 'error')
# @lang('Whoops!')
@else
# @lang('Hello!')
@endif
@endif

{{-- Intro Lines --}}
@foreach ($introLines as $line)
{{ $line }}

@endforeach

{{-- Action Button --}}
@isset($actionText)
<?php
    $color = match ($level) {
        'success', 'error' => $level,
        default => 'primary',
    };
?>
@component('mail::button', ['url' => $actionUrl, 'color' => $color])
{{ $actionText }}
@endcomponent
@endisset

{{-- Outro Lines --}}
@foreach ($outroLines as $line)
{{ $line }}

@endforeach

{{-- Salutation --}}
@if (! empty($salutation))
{{ $salutation }}
@else
@lang(
"<p style=\margin: 0; margin-bottom: 24px;\>Best regards,</p>
<p style=\"margin: 0; font-size: 14px; font-weight: 600; color: #374151;\">GI/TI Cloud Team</p>
<p style=\"margin: 0; margin-bottom: 24px; font-size: 14px; font-weight: 600; color: #374151;\">FRA GI/XI</p>
<p style=\"margin: 0; font-size: 14px; color: #6b7280;\">Deutsche Lufthansa AG</p>
<p style=\"margin: 0; font-size: 14px; color: #6b7280;\">Lufthansa Aviation Center</p>
<p style=\"margin: 0; font-size: 14px; color: #6b7280;\">Airportring 60549 Frankfurt am Main</p>"
)
@endif

{{-- Subcopy --}}
@isset($actionText)
@slot('subcopy')
@lang(
    "If you're having trouble clicking the \":actionText\" button, copy and paste the URL below\n".
    'into your web browser:',
    [
        'actionText' => $actionText,
    ]
) <span class="break-all">[{{ $displayableActionUrl }}]({{ $actionUrl }})</span>
@endslot
@endisset
@endcomponent
