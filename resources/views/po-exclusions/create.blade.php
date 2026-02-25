@extends('templates.main')

@section('title_page')
    <h1>Add PO Exclusions</h1>
@endsection

@section('breadcrumb_title')
    <a href="{{ route('dashboard.daily.index') }}">Dashboard</a> / <a href="{{ route('po-exclusions.index') }}">PO Exclusions</a> / Add
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Add PO numbers to exclude from filters</h3>
            </div>
            <form action="{{ route('po-exclusions.store') }}" method="POST">
                @csrf
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <div class="form-group">
                        <label for="po_nos">PO Numbers <span class="text-danger">*</span></label>
                        <textarea name="po_nos" id="po_nos" class="form-control @error('po_nos') is-invalid @enderror" rows="6" placeholder="Enter PO numbers, one per line or separated by comma&#10;Example:&#10;4500123456&#10;4500123457, 4500123458"></textarea>
                        <small class="form-text text-muted">Enter one or more PO numbers. Separate by comma, semicolon, or newline.</small>
                        @error('po_nos')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="reason">Reason (optional)</label>
                        <input type="text" name="reason" id="reason" class="form-control" value="{{ old('reason') }}" placeholder="e.g. Duplicate, Cancelled, Wrong project">
                    </div>
                </div>
                <div class="card-footer">
                    <a href="{{ route('po-exclusions.index') }}" class="btn btn-default">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Add Exclusions
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
