<li class="nav-item dropdown">
    <a id="dropdownSubMenu1" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
        class="nav-link dropdown-toggle">Budget</a>
    <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
        <li><a href="{{ route('budget.index') }}" class="dropdown-item">Input Budget</a></li>
        <li><a href="{{ route('production-plan.index') }}" class="dropdown-item">Production Plan</a></li>
        @hasanyrole('superadmin')
            <li><a href="{{ route('budget_type.index') }}" class="dropdown-item">Budget Type</a></li>
            <li><a href="{{ route('history.index') }}" class="dropdown-item">Histories</a></li>
        @endhasanyrole
    </ul>
</li>
