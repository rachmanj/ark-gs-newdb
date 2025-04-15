<li class="nav-item dropdown">
    <a id="dropdownSubMenu1" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
        class="nav-link dropdown-toggle">Dashboard</a>
    <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
        <li><a href="{{ route('dashboard.daily.index') }}" class="dropdown-item">Daily</a></li>
        <li><a href="{{ route('dashboard.monthly.index') }}" class="dropdown-item">Monthly</a></li>
        <li><a href="{{ route('dashboard.yearly.index') }}" class="dropdown-item">Yearly</a></li>
        <li><a href="{{ route('dashboard.summary-by-unit') }}" class="dropdown-item">Summary by Unit</a></li>
        <li><a href="{{ route('dashboard.search.po') }}" class="dropdown-item">Search PO</a></li>
        <li><a href="{{ route('dashboard.item.price.history') }}" class="dropdown-item">Item Price History <span
                    class="badge badge-danger ml-1">New</span></a></li>
        {{-- <li><a href="{{ route('dashboard.other.index') }}" class="dropdown-item">Other</a></li> --}}
    </ul>
</li>
