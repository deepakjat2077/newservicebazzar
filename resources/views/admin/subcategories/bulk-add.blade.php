@extends('admin.layouts.master')

@section('title', 'Bulk Add Subcategories')

@section('content')

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">🚀 Bulk Add Subcategories with AI</h5>
                <div>
                    @if($aiConfigured)
                        <span class="badge badge-success mr-2">AI Enabled</span>
                    @else
                        <span class="badge badge-warning mr-2">AI Not Configured</span>
                    @endif
                    <a href="{{ route('admin.subcategories.index') }}" class="btn btn-dark btn-sm">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                </div>
            </div>

            <form action="{{ route('admin.subcategories.bulk.store') }}" method="POST">
                @csrf

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <strong>Errors:</strong>
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            @if (session('success'))
                                <div class="alert alert-success">
                                    {{ session('success') }}
                                    @if (session('errors'))
                                        <hr>
                                        <strong>Issues:</strong>
                                        <ul>
                                            @foreach (session('errors') as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="form-group">
                        <label><strong>Category</strong> <span class="text-danger">*</span></label>
                        <select name="category_id" class="form-control" id="category_id" required>
                            <option value="">Select Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">Choose the category these subcategories belong to</small>
                    </div>

                    <div class="form-group">
                        <label><strong>Subcategory Names</strong> <span class="text-danger">*</span></label>
                        <textarea name="names" class="form-control" rows="12" placeholder="Enter one subcategory name per line:

Plumbing Repair
Pipe Installation
Water Heater Service
Drain Cleaning
Emergency Plumbing
Faucet Repair

AI will auto-generate:
• Unique slug
• SEO title (from name)
• SEO keywords
• SEO description" required>{{ old('names') }}</textarea>
                        <small class="form-text text-muted">
                            <strong>Format:</strong> One subcategory name per line.
                            @if($aiConfigured)
                                ✓ AI will generate SEO fields automatically
                            @else
                                ⚠ AI not configured; default SEO will be used
                            @endif
                        </small>
                    </div>

                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle"></i> How It Works</h6>
                        <ul class="mb-0">
                            <li>Enter subcategory names (one per line)</li>
                            <li>System checks for duplicates</li>
                            <li>AI generates SEO title, keywords, and description</li>
                            <li>All new subcategories created with status = Active</li>
                            <li>Duplicate names are skipped (not duplicated)</li>
                        </ul>
                    </div>
                </div>

                <div class="card-footer text-right">
                    <a href="{{ route('admin.subcategories.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-magic"></i> Generate & Create Subcategories
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">💡 Examples</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>For "Plumbing" Category:</h6>
                        <pre style="background: #f5f5f5; padding: 10px; border-radius: 4px;">Pipe Repair
Water Heater Installation
Drain Cleaning
Faucet Replacement
Leak Detection
Bathroom Plumbing</pre>
                    </div>
                    <div class="col-md-6">
                        <h6>For "Electrician" Category:</h6>
                        <pre style="background: #f5f5f5; padding: 10px; border-radius: 4px;">Wiring Installation
Electrical Panel Upgrade
Light Fixture Installation
Power Outlet Repair
Circuit Breaker Issues
Emergency Electrical Service</pre>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
