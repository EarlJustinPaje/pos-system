@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Start New Product Import</h1>
    
    <div class="card">
        <div class="card-body">
            <p class="alert alert-info">
                <strong>Instructions:</strong> Please ensure your file is a valid **.xlsx** or **.csv** file. 
                Use the <a href="{{ route('products.imports.download-template') }}" class="alert-link">official template</a> 
                and map the columns correctly below. Importing may take several minutes for large files.
            </p>

            <form action="{{ route('products.imports.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <div class="form-group">
                    <label for="import_file">Upload File (Excel or CSV)</label>
                    <input type="file" name="import_file" id="import_file" class="form-control-file @error('import_file') is-invalid @enderror" required>
                    @error('import_file')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="branch_id">Target Branch</label>
                    <select name="branch_id" id="branch_id" class="form-control @error('branch_id') is-invalid @enderror" required>
                        <option value="">Select Branch</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ old('branch_id', auth()->user()->branch_id) == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                    @error('branch_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label for="notes">Notes/Reference</label>
                    <textarea name="notes" id="notes" class="form-control @error('notes') is-invalid @enderror">{{ old('notes') }}</textarea>
                    <small class="form-text text-muted">A description for this import job (e.g., "Q4 2024 Inventory from Acme Supplier").</small>
                    @error('notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <p class="text-danger">**Note:** Column mapping is currently disabled and assumes your file matches the template structure. (You can add mapping logic here if needed later).</p>

                <button type="submit" class="btn btn-primary mt-3">Start Import Job</button>
                <a href="{{ route('products.imports.index') }}" class="btn btn-secondary mt-3">Cancel</a>
            </form>
        </div>
    </div>
</div>
@endsection