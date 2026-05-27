@if($header)
<div style="border-bottom: 3px double #333; padding-bottom: 10px; margin-bottom: 15px;">
    <table style="width: 100%;">
        <tr>
            @if($header->logo_path)
            <td style="width: 80px; vertical-align: middle;">
                <img src="{{ storage_path('app/public/' . $header->logo_path) }}" style="max-height: 60px;">
            </td>
            @endif
            <td style="text-align: center; vertical-align: middle;">
                <div style="font-size: 16px; font-weight: bold; text-transform: uppercase;">{{ $header->institution_name }}</div>
                @if($header->tagline)<div style="font-size: 10px; font-style: italic;">{{ $header->tagline }}</div>@endif
                @if($header->address)<div style="font-size: 9px; margin-top: 3px;">{{ $header->address }}</div>@endif
                <div style="font-size: 9px;">
                    @if($header->phone)Telp: {{ $header->phone }}@endif
                    @if($header->email) | Email: {{ $header->email }}@endif
                    @if($header->website) | {{ $header->website }}@endif
                </div>
            </td>
            @if($header->secondary_logo_path)
            <td style="width: 80px; vertical-align: middle; text-align: right;">
                <img src="{{ storage_path('app/public/' . $header->secondary_logo_path) }}" style="max-height: 60px;">
            </td>
            @endif
        </tr>
    </table>
</div>
@endif
