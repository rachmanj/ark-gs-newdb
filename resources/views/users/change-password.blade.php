@extends('templates.main')

@section('title_page')
    Users
@endsection

@section('breadcrumb_title')
    users
@endsection

@section('content')
    <div class="row">
        <div class="col-7">
            <div class="card">

                <div class="card-header">
                    <div class="card-title">Change Password</div>
                    <a href="{{ route('dashboard.daily.index') }}" class="btn btn-sm btn-primary float-right"><i
                            class="fas fa-arrow-left"></i> Back</a>
                </div>

                <form action="{{ route('users.password_update', $user->id) }}" method="POST">
                    @csrf @method('PUT')
                    <div class="card-body">

                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="name">Name</label>
                                    <input type="text" name="name" value="{{ $user->name }}" class="form-control"
                                        readonly>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="username">Username</label>
                                    <input type="text" name="username" value="{{ $user->username }}" class="form-control"
                                        readonly>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input type="text" name="email" value="{{ $user->email }}" class="form-control"
                                        readonly>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for='project'>Project</label>
                            <input type="text" name="project" value="{{ $user->project_code }}" class="form-control"
                                readonly>
                        </div>

                        {{-- <div class="form-group">
                <label for='department'>Department</label>
                <input type="text" name="department" value="{{ $user->department->department_name }}" class="form-control" readonly>
                </div> --}}

                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="password">Password</label>
                                    <input type="password" name="password"
                                        class="form-control @error('password') is-invalid @enderror">
                                    @error('password')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="password_confirmation">Password Confirmation</label>
                                    <input type="password" name="password_confirmation"
                                        class="form-control @error('password_confirmation') is-invalid @enderror">
                                    @error('password_confirmation')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="card-footer">
                            <button type="submit" class="btn btn-sm btn-primary btn-block"><i class="fas fa-save"></i>
                                Save</button>
                        </div>
                </form>

            </div> {{-- card --}}
        </div> {{-- col-7 --}}
    </div> {{-- row --}}
@endsection
