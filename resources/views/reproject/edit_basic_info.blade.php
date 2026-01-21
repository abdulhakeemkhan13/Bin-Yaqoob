@extends('layouts.admin')

@section('page-title')
    {{ __('Edit Project Basic Info') }} - {{ $project->name }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('re-projects.index') }}">{{ __('Real Estate Projects') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('re-projects.show', $project->id) }}">{{ $project->name }}</a></li>
    <li class="breadcrumb-item">{{ __('Edit Basic Info') }}</li>
@endsection

@section('action-btn')
    <div class="float-end">
        <a href="{{ route('re-projects.show', $project->id) }}" class="btn btn-sm btn-secondary">
            <i class="ti ti-arrow-left"></i> {{ __('Back') }}
        </a>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5><i class="ti ti-building me-2"></i>{{ __('Edit Basic Information') }}</h5>
                    <p class="text-muted mb-0">
                        {{ __('Update project basic details. Towers, floors, and units cannot be modified for active projects.') }}
                    </p>
                </div>
                <div class="card-body">
                    <form action="{{ route('re-projects.update-basic-info', $project->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label class="form-label">{{ __('Project Name') }} <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                                        name="name" value="{{ old('name', $project->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group mb-3">
                                    <label class="form-label">{{ __('Project Code') }}</label>
                                    <input type="text" class="form-control" value="{{ $project->code }}" disabled>
                                    <small class="text-muted">{{ __('Cannot be changed') }}</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-3">
                                    <label class="form-label">{{ __('City') }} <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('city') is-invalid @enderror"
                                        name="city" value="{{ old('city', $project->city) }}" required>
                                    @error('city')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-3">
                                    <label class="form-label">{{ __('Area') }}</label>
                                    <input type="text" class="form-control @error('area') is-invalid @enderror"
                                        name="area" value="{{ old('area', $project->area) }}">
                                    @error('area')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group mb-3">
                                    <label class="form-label">{{ __('Address') }}</label>
                                    <textarea class="form-control @error('address') is-invalid @enderror" name="address" rows="2">{{ old('address', $project->address) }}</textarea>
                                    @error('address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-3">
                                    <label class="form-label">{{ __('Total Towers / Blocks') }}</label>
                                    <input type="number" class="form-control" value="{{ $project->total_towers }}"
                                        disabled>
                                    <small class="text-muted">{{ __('Cannot be changed') }}</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-3">
                                    <label class="form-label">{{ __('Project Type') }} <span
                                            class="text-danger">*</span></label>
                                    <select class="form-control @error('type') is-invalid @enderror" name="type"
                                        required>
                                        <option value="Residential"
                                            {{ old('type', $project->type) == 'Residential' ? 'selected' : '' }}>
                                            {{ __('Residential') }}</option>
                                        <option value="Commercial"
                                            {{ old('type', $project->type) == 'Commercial' ? 'selected' : '' }}>
                                            {{ __('Commercial') }}</option>
                                        <option value="Mixed"
                                            {{ old('type', $project->type) == 'Mixed' ? 'selected' : '' }}>
                                            {{ __('Mixed') }}</option>
                                    </select>
                                    @error('type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-3">
                                    <label class="form-label">{{ __('Start Date') }}</label>
                                    <input type="date" class="form-control @error('start_date') is-invalid @enderror"
                                        name="start_date"
                                        value="{{ old('start_date', $project->start_date?->format('Y-m-d')) }}">
                                    @error('start_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-3">
                                    <label class="form-label">{{ __('Expected Completion') }}</label>
                                    <input type="date"
                                        class="form-control @error('expected_completion') is-invalid @enderror"
                                        name="expected_completion"
                                        value="{{ old('expected_completion', $project->expected_completion?->format('Y-m-d')) }}">
                                    @error('expected_completion')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group mb-3">
                                    <label class="form-label">{{ __('Description') }}</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" name="description" rows="2">{{ old('description', $project->description) }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-12">
                                <hr>
                                <h6 class="text-primary">{{ __('Approval & Accounts') }}</h6>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group mb-3">
                                    <label class="form-label">{{ __('Approval Authority') }}</label>
                                    <select class="form-control @error('approval_authority') is-invalid @enderror"
                                        name="approval_authority">
                                        <option value="">{{ __('Select Authority') }}</option>
                                        @foreach (['LDA', 'CDA', 'SBCA', 'RDA', 'KDA', 'TMA', 'Other'] as $auth)
                                            <option value="{{ $auth }}"
                                                {{ old('approval_authority', $project->approval_authority) == $auth ? 'selected' : '' }}>
                                                {{ $auth }}</option>
                                        @endforeach
                                    </select>
                                    @error('approval_authority')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-3">
                                    <label class="form-label">{{ __('NOC Number') }}</label>
                                    <input type="text" class="form-control @error('noc_number') is-invalid @enderror"
                                        name="noc_number" value="{{ old('noc_number', $project->noc_number) }}"
                                        placeholder="e.g. NOC-2026-001">
                                    @error('noc_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-3">
                                    <label class="form-label">{{ __('Approval Date') }}</label>
                                    <input type="date"
                                        class="form-control @error('approval_date') is-invalid @enderror"
                                        name="approval_date"
                                        value="{{ old('approval_date', $project->approval_date?->format('Y-m-d')) }}">
                                    @error('approval_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-3">
                                    <label class="form-label">{{ __('Income Account (COA)') }}</label>
                                    <select class="form-control @error('income_account_id') is-invalid @enderror"
                                        name="income_account_id">
                                        <option value="">{{ __('Select Account') }}</option>
                                        @foreach ($chartOfAccounts as $id => $name)
                                            <option value="{{ $id }}"
                                                {{ old('income_account_id', $project->income_account_id) == $id ? 'selected' : '' }}>
                                                {{ $name }}</option>
                                        @endforeach
                                    </select>
                                    @error('income_account_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-3">
                                    <label class="form-label">{{ __('Receivable Account (COA)') }}</label>
                                    <select class="form-control @error('receivable_account_id') is-invalid @enderror"
                                        name="receivable_account_id">
                                        <option value="">{{ __('Select Account') }}</option>
                                        @foreach ($chartOfAccounts as $id => $name)
                                            <option value="{{ $id }}"
                                                {{ old('receivable_account_id', $project->receivable_account_id) == $id ? 'selected' : '' }}>
                                                {{ $name }}</option>
                                        @endforeach
                                    </select>
                                    @error('receivable_account_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12 text-end">
                                <a href="{{ route('re-projects.show', $project->id) }}" class="btn btn-secondary me-2">
                                    {{ __('Cancel') }}
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="ti ti-device-floppy me-1"></i> {{ __('Save Changes') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
