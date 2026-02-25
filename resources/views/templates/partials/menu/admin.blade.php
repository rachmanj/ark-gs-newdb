<li class="nav-item dropdown">
  <a id="dropdownSubMenu1" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="nav-link dropdown-toggle">Admin</a>
  <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
    {{-- <li><a href="#" class="dropdown-item">Activate User </a></li> --}}
    <li><a href="{{ route('users.index') }}" class="dropdown-item">User List</a></li>
    <li><a href="{{ route('roles.index') }}" class="dropdown-item">Roles</a></li>
    <li><a href="{{ route('permissions.index') }}" class="dropdown-item">Permission</a></li>
    <li><a href="{{ route('po-exclusions.index') }}" class="dropdown-item">PO Exclusions</a></li>
  </ul>
</li>