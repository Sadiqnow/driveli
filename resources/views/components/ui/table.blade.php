@props([
    'headers' => [],
    'data' => [],
    'actions' => [],
    'emptyMessage' => 'No data available',
    'striped' => true,
    'hover' => true,
    'responsive' => true,
    'sortable' => false,
    'searchable' => false
])

@php
$tableClasses = ['table'];
if ($striped) $tableClasses[] = 'table-striped';
if ($hover) $tableClasses[] = 'table-hover';
$tableClass = implode(' ', $tableClasses);
@endphp

@if($responsive)
<div class="table-responsive">
@endif
<table class="{{ $tableClass }}" role="table" aria-label="{{ $title ?? 'Data table' }}">
    @if($headers)
    <thead>
        <tr>
            @foreach($headers as $header)
            <th scope="col" @if(isset($header['sortable']) && $header['sortable']) class="sortable" @endif>
                {{ $header['label'] ?? $header }}
                @if(isset($header['sortable']) && $header['sortable'])
                <i class="bi bi-chevron-expand" aria-hidden="true"></i>
                @endif
            </th>
            @endforeach
            @if($actions)
            <th scope="col">Actions</th>
            @endif
        </tr>
    </thead>
    @endif
    <tbody>
        @if($data && count($data) > 0)
            @foreach($data as $row)
            <tr>
                @foreach($headers as $key => $header)
                <td>
                    @if(is_array($row))
                        {{ $row[$key] ?? '' }}
                    @else
                        {{ $row->{$key} ?? '' }}
                    @endif
                </td>
                @endforeach
                @if($actions)
                <td>
                    <div class="btn-group" role="group" aria-label="Row actions">
                        @foreach($actions as $action)
                        <button type="button"
                                class="btn btn-sm {{ $action['class'] ?? 'btn-outline-primary' }}"
                                @if(isset($action['onclick'])) onclick="{{ $action['onclick'] }}" @endif
                                @if(isset($action['href'])) onclick="window.location.href='{{ $action['href'] }}'" @endif
                                @if(isset($action['data'])) data-{{ key($action['data']) }}="{{ $action['data'][key($action['data'])] }}" @endif
                                aria-label="{{ $action['label'] ?? '' }}">
                            <i class="{{ $action['icon'] ?? 'bi bi-gear' }}" aria-hidden="true"></i>
                            @if(isset($action['text'])) {{ $action['text'] }} @endif
                        </button>
                        @endforeach
                    </div>
                </td>
                @endif
            </tr>
            @endforeach
        @else
        <tr>
            <td colspan="{{ count($headers) + ($actions ? 1 : 0) }}" class="text-center py-4">
                <i class="bi bi-info-circle" aria-hidden="true"></i> {{ $emptyMessage }}
            </td>
        </tr>
        @endif
    </tbody>
</table>
@if($responsive)
</div>
@endif
