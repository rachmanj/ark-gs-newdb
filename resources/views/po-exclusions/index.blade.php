@extends('templates.main')

@section('title_page')
    <h1>PO Exclusions</h1>
@endsection

@section('breadcrumb_title')
    <a href="{{ route('dashboard.daily.index') }}">Dashboard</a> / PO Exclusions
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                @if (Session::has('success'))
                    <div class="alert alert-success alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        {{ Session::get('success') }}
                    </div>
                @endif
                @if (Session::has('error'))
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        {{ Session::get('error') }}
                    </div>
                @endif
                <a href="{{ route('po-exclusions.create') }}" class="btn btn-sm btn-success">
                    <i class="fas fa-plus"></i> Add PO Exclusions
                </a>
                <span class="text-muted ml-2">Excluded POs are not counted in dashboard, reports, or exports.</span>
            </div>
            <div class="card-body">
                <table class="table table-bordered table-striped table-hover">
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th>PO Number</th>
                            <th>Reason</th>
                            <th width="15%">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($exclusions as $index => $exclusion)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td><strong>{{ $exclusion->po_no }}</strong></td>
                                <td>{{ $exclusion->reason ?? '-' }}</td>
                                <td>
                                    <form action="{{ route('po-exclusions.destroy', $exclusion) }}" method="POST" class="d-inline" onsubmit="return confirm('Remove this PO from exclusions?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-xs btn-danger">
                                            <i class="fas fa-trash"></i> Remove
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">No PO exclusions. Add POs to exclude them from filters.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
