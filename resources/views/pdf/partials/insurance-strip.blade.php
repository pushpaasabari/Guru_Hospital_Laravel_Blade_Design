@php
    $insItems = $admission->insurance_details_list;
    $tblClass = $tableClass ?? 'info-table';
@endphp
@if(!empty($insItems))
    <table class="{{ $tblClass }}" style="background-color: #f8fafc; margin-bottom: 4px;">
        @foreach(array_chunk($insItems, 3) as $rowChunk)
            <tr>
                @foreach($rowChunk as $item)
                    <td style="padding: 2px 5px; vertical-align: top;">
                        <span style="font-size: 6.5pt; font-weight: bold; color: #475569; text-transform: uppercase; letter-spacing: 0.3px; display: block;">{{ $item['label'] }}</span>
                        <span style="font-size: 8.2pt; font-weight: bold; color: #000000; display: block;">{{ $item['value'] }}</span>
                    </td>
                @endforeach
                @for($emptyCell = count($rowChunk); $emptyCell < 3; $emptyCell++)
                    <td style="padding: 2px 5px;"></td>
                @endfor
            </tr>
        @endforeach
    </table>
@endif

