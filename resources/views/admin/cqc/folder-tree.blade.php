<ul class="list-group ms-3">
@foreach($folders as $child)
    <li class="list-group-item">
        <a href="{{ url('cqc-vault/folder/'.$child->id) }}">
            📁 {{ $child->name }}
        </a>

        @if($child->children->count())
            @include('admin.cqc.folder-tree', ['folders' => $child->children])
        @endif
    </li>
@endforeach
</ul>
