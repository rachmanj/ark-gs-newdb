<li class="nav-item dropdown">
    <a id="dropdownSubMenu1" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
        class="nav-link dropdown-toggle">Upload</a>
    <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
        <li><a href="{{ route('powitheta.index') }}" class="dropdown-item">PO With ETA </a></li>
        <li><a href="{{ route('grpo.index') }}" class="dropdown-item">GRPO History</a></li>
        <li><a href="{{ route('migi.index') }}" class="dropdown-item">MIGI</a></li>
        <li><a href="{{ route('incoming.index') }}" class="dropdown-item">Incoming Inventory</a></li>
        @can('upload_data')
            <li><a href="{{ route('daily-production.index') }}" class="dropdown-item">Daily Production</a></li>
        @endcan
    </ul>
</li>
