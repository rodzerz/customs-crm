<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Documents & Declarations</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f5f5f5; }
        .container { max-width: 700px; margin: 0 auto; }
        form { background-color: white; padding: 20px; border-radius: 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        label { display: block; margin-top: 15px; font-weight: bold; }
        input, select, textarea { width: 100%; padding: 8px; margin-top: 5px; border: 1px solid #ddd; border-radius: 3px; box-sizing: border-box; }
        button { margin-top: 20px; padding: 10px 20px; background-color: #4CAF50; color: white; border: none; border-radius: 3px; cursor: pointer; font-size: 16px; }
        button:hover { background-color: #45a049; }
        .back-link { display: inline-block; margin-bottom: 20px; color: #0066cc; text-decoration: none; }
        .back-link:hover { text-decoration: underline; }
        .info { background-color: #e3f2fd; border-left: 4px solid #2196F3; padding: 10px; margin-bottom: 20px; border-radius: 3px; }
        .success { background-color: #c8e6c9; border-left: 4px solid #4CAF50; padding: 10px; margin-bottom: 20px; border-radius: 3px; color: #2e7d32; }
        .error { color: red; font-size: 12px; }
        .required { color: red; }
        .file-input-wrapper { 
            position: relative; 
            overflow: hidden; 
            display: inline-block; 
            width: 100%;
        }
        .file-input-wrapper input[type=file] {
            position: absolute;
            left: -9999px;
        }
        .file-input-label {
            display: block;
            padding: 8px;
            background-color: #f0f0f0;
            border: 2px dashed #ddd;
            border-radius: 3px;
            cursor: pointer;
            text-align: center;
            color: #666;
            font-size: 14px;
        }
        .file-input-label:hover {
            background-color: #e0e0e0;
            border-color: #4CAF50;
        }
        .file-name {
            margin-top: 5px;
            font-size: 12px;
            color: #2e7d32;
        }
        .document-type { 
            display: grid; 
            grid-template-columns: 1fr 1fr; 
            gap: 10px; 
            margin-top: 5px;
        }
        .doc-option {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 3px;
            cursor: pointer;
            text-align: center;
        }
        .doc-option input[type=radio] {
            width: auto;
            margin-right: 5px;
        }
        .uploaded-docs { 
            background-color: #f9f9f9; 
            padding: 15px; 
            border-radius: 3px; 
            margin-top: 30px; 
        }
        .uploaded-docs h3 { margin-top: 0; color: #333; }
        .doc-item {
            background-color: white;
            padding: 10px;
            margin: 10px 0;
            border-left: 4px solid #2196F3;
            border-radius: 3px;
        }
        .doc-type-badge {
            display: inline-block;
            padding: 3px 8px;
            background-color: #2196F3;
            color: white;
            border-radius: 3px;
            font-size: 11px;
            margin-right: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="/cases" class="back-link">← Back to Cases</a>
        
        <h1>📄 Submit Documents & Declarations</h1>
        
        <div class="info">
            <strong>Case:</strong> {{ $case->external_id }}<br>
            <strong>Vehicle:</strong> {{ $case->vehicle ? $case->vehicle->plate_no : 'N/A' }}<br>
            <strong>Status:</strong> {{ ucfirst(str_replace('_', ' ', $case->status)) }}
        </div>

        @if (session('success'))
        <div class="success">
            ✅ {{ session('success') }}
        </div>
        @endif

        @if ($errors->any())
        <div style="background-color: #ffebee; border-left: 4px solid #f44336; padding: 10px; margin-bottom: 20px; border-radius: 3px; color: #c62828;">
            <strong>Validation Errors:</strong>
            <ul style="margin: 10px 0 0 20px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('documents.store', $case->id) }}" enctype="multipart/form-data">
            @csrf

            <label>
                Document Type <span class="required">*</span>
            </label>
            <div class="document-type">
                <div class="doc-option">
                    <input type="radio" name="type" id="declaration" value="declaration" required @checked(old('type') === 'declaration')>
                    <label for="declaration" style="margin: 0; font-weight: normal;">
                        📋 Declaration
                    </label>
                </div>
                <div class="doc-option">
                    <input type="radio" name="type" id="invoice" value="invoice" required @checked(old('type') === 'invoice')>
                    <label for="invoice" style="margin: 0; font-weight: normal;">
                        🧾 Invoice
                    </label>
                </div>
                <div class="doc-option">
                    <input type="radio" name="type" id="packing_list" value="packing_list" required @checked(old('type') === 'packing_list')>
                    <label for="packing_list" style="margin: 0; font-weight: normal;">
                        📦 Packing List
                    </label>
                </div>
                <div class="doc-option">
                    <input type="radio" name="type" id="certificate" value="certificate" required @checked(old('type') === 'certificate')>
                    <label for="certificate" style="margin: 0; font-weight: normal;">
                        ✅ Certificate
                    </label>
                </div>
                <div class="doc-option">
                    <input type="radio" name="type" id="other" value="other" required @checked(old('type') === 'other')>
                    <label for="other" style="margin: 0; font-weight: normal;">
                        📎 Other
                    </label>
                </div>
            </div>
            @error('type')
                <span class="error">{{ $message }}</span>
            @enderror

            <label for="file">Document File</label>
            <div class="file-input-wrapper">
                <input type="file" name="file" id="file" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.zip">
                <label for="file" class="file-input-label">
                    📁 Click to select file or drag & drop<br>
                    <small>Accepted: PDF, DOC, DOCX, XLS, XLSX, JPG, PNG, ZIP</small>
                </label>
                <div class="file-name" id="fileName"></div>
            </div>

            <label for="description">Description</label>
            <textarea name="description" id="description" rows="3" placeholder="Add any notes about this document...">{{ old('description') }}</textarea>
            @error('description')
                <span class="error">{{ $message }}</span>
            @enderror

            <button type="submit">📤 Upload Document</button>
        </form>

        <!-- Display already uploaded documents -->
        @if($case->documents->count() > 0)
        <div class="uploaded-docs">
            <h3>📋 Uploaded Documents ({{ $case->documents->count() }})</h3>
            @foreach($case->documents as $document)
            <div class="doc-item">
                <span class="doc-type-badge">{{ strtoupper(str_replace('_', ' ', $document->type)) }}</span>
                <strong>{{ $document->description ?? 'No description' }}</strong><br>
                <small style="color: #666;">
                    Files: {{ $document->files->count() }} | 
                    Created: {{ $document->created_at->format('Y-m-d H:i') }}
                </small>
                @if($document->files->count() > 0)
                <div style="margin-top: 8px;">
                    @foreach($document->files as $file)
                    <div style="font-size: 12px; color: #2196F3;">
                        📄 {{ $file->filename }} ({{ number_format($file->file_size / 1024, 2) }} KB)
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
            @endforeach
        </div>
        @endif
    </div>

    <script>
        // File input handler
        const fileInput = document.getElementById('file');
        const fileNameDiv = document.getElementById('fileName');
        
        fileInput.addEventListener('change', function() {
            if (this.files.length > 0) {
                fileNameDiv.textContent = '✅ Selected: ' + this.files[0].name;
            }
        });

        // Drag and drop
        const fileInputLabel = document.querySelector('.file-input-label');
        fileInputLabel.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.style.backgroundColor = '#e0e0e0';
            this.style.borderColor = '#4CAF50';
        });

        fileInputLabel.addEventListener('dragleave', function(e) {
            e.preventDefault();
            this.style.backgroundColor = '#f0f0f0';
            this.style.borderColor = '#ddd';
        });

        fileInputLabel.addEventListener('drop', function(e) {
            e.preventDefault();
            this.style.backgroundColor = '#f0f0f0';
            this.style.borderColor = '#ddd';
            fileInput.files = e.dataTransfer.files;
            if (fileInput.files.length > 0) {
                fileNameDiv.textContent = '✅ Selected: ' + fileInput.files[0].name;
            }
        });
    </script>
</body>
</html>
