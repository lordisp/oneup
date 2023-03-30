<div>
    <span class="flex items-center text-sm space-x-1">
        @if($row->newStatus==='review')
            <x-icon.info :class="$row->status_text"/>
        @elseif($row->newStatus==='deleted')
            <x-icon.ban :class="$row->status_text"/>
        @elseif($row->newStatus==='extended')
            <x-icon.check-circle :class="$row->status_text"/>
        @endif
        <span>{{$row->status_name}}</span>
    </span>
    <span class="flex items-center text-xs italic space-x-1">
        @if(isset($row->lastStatusName) && isset($row->last_review))
            <span class="truncate">{{$row->lastStatusName}} {{$row->last_review->diffForHumans()}}</span>
        @else
            <span class="truncate">Never reviewed</span>
        @endif
    </span>
    <span class="text-xs italic truncate">{{$row->request->created_at->format('d.m.Y')}}</span>
</div>