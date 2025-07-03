<!-- Form HTML cho Laravel Blade -->
<div class="box box-info padding-1">
    <div class="box-body">
        <div class="form-group required">
            <label for="number">Thứ tự chương</label>
            <input type="number" name="number" id="number" value="{{ old('number', $chapter->number ?? '') }}"
                   class="form-control{{ $errors->has('number') ? ' is-invalid' : '' }}">
            @if ($errors->has('number'))
                <div class="invalid-feedback">{{ $errors->first('number') }}</div>
            @endif
        </div>
        <div class="form-group required">
            <label for="title">Tên chương</label>
            <input type="text" name="title" id="title" value="{{ old('title', $chapter->title ?? '') }}"
                   class="form-control{{ $errors->has('title') ? ' is-invalid' : '' }}">
            @if ($errors->has('title'))
                <div class="invalid-feedback">{{ $errors->first('title') }}</div>
            @endif
        </div>
        <div class="form-group required">
            <label for="content">Nội dung</label>
            <textarea name="content" id="content"
                class="form-control{{ $errors->has('content') ? ' is-invalid' : '' }}">{{ old('content', $chapter->content ?? '') }}</textarea>
            @if ($errors->has('content'))
                <div class="invalid-feedback">{{ $errors->first('content') }}</div>
            @endif
        </div>
        
        <!-- Thông báo trạng thái -->
        <div id="storage-status" class="mt-2"></div>
    </div>
    <div class="box-footer mt20">
        <button type="submit" class="btn btn-primary">{{ __('Xác nhận') }}</button>
    </div>
</div>

<!-- Add CKEditor CDN -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="{{ asset('assets/vendor/ckeditor5.css') }}">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

@section('ArticleScripts')
<script type="importmap">
    {
        "imports": {
            "ckeditor5": "{{ asset('assets/vendor/ckeditor5.js') }}",
            "ckeditor5/": "{{ asset('assets/vendor/') }}/"
        }
    }
</script>

<script type="module">
    import {
        ClassicEditor,
        Essentials,
        Paragraph,
        Bold,
        Italic,
        Font,
        Heading,
        Image,
        ImageUpload,
        ImageToolbar,
        ImageCaption,
        ImageStyle,
        Alignment,
        List
    } from 'ckeditor5';
    
    // Custom Upload Adapter - chỉ để preview local
    class LocalPreviewUploadAdapter {
        constructor(loader) {
            this.loader = loader;
        }

        upload() {
            return new Promise((resolve, reject) => {
                this.loader.file.then(async file => {
                    try {
                        // Chuyển đổi file thành base64
                        const reader = new FileReader();
                        reader.readAsDataURL(file);
                        reader.onload = () => {
                            const base64Data = reader.result;
                            
                            // Lưu vào tempImages
                            const tempId = 'temp_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
                            window.tempImages = window.tempImages || {};
                            window.tempImages[tempId] = {
                                data: base64Data,
                                name: file.name,
                                file: file
                            };
                            
                            // Thông báo
                            updateStatus(`Đã tải ảnh "${file.name}" để preview`, 'info');
                            
                            // Trả về URL cho CKEditor
                            resolve({
                                default: base64Data
                            });
                        };
                        reader.onerror = () => reject('Lỗi khi đọc file');
                    } catch (error) {
                        reject('Lỗi khi xử lý ảnh: ' + error.message);
                    }
                });
            });
        }

        abort() {
            console.log("Upload đã bị hủy");
        }
    }
    
    // Hàm để cập nhật trạng thái
    function updateStatus(message, type = 'info') {
        const statusDiv = document.getElementById('storage-status');
        if (!statusDiv) return;
        
        const alertClass = type === 'success' ? 'alert-success' : 
                          type === 'warning' ? 'alert-warning' : 
                          type === 'error' ? 'alert-danger' : 'alert-info';
        
        statusDiv.innerHTML = `<div class="alert ${alertClass} alert-dismissible">
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            ${message}
        </div>`;
        
        // Tự động ẩn sau 5 giây
        setTimeout(() => {
            statusDiv.innerHTML = '';
        }, 5000);
    }
    
    // Plugin Upload Adapter - phải định nghĩa trước khi sử dụng
    function uploadAdapterPlugin(editor) {
        editor.plugins.get('FileRepository').createUploadAdapter = (loader) => {
            return new LocalPreviewUploadAdapter(loader);
        };
    }

    // Khởi tạo CKEditor
    ClassicEditor
        .create(document.querySelector('#content'), {
            licenseKey: 'GPL',
            plugins: [
                Essentials, 
                Paragraph, 
                Bold, 
                Italic, 
                Font, 
                Heading, 
                Image, 
                ImageUpload,
                ImageToolbar, 
                ImageCaption,
                ImageStyle,
                Alignment,
                List
            ],
            toolbar: [
                'undo', 'redo', '|',
                'bold', 'italic', '|',
                'heading', '|',
                'fontSize', 'fontFamily', 'fontColor', 'fontBackgroundColor', '|',
                'alignment', '|',
                'bulletedList', 'numberedList', '|',
                'imageUpload'
            ],
            image: {
                toolbar: [
                    'imageTextAlternative', 
                    'imageStyle:full', 
                    'imageStyle:side', 
                    'imageStyle:alignLeft', 
                    'imageStyle:alignCenter', 
                    'imageStyle:alignRight'
                ]
            },
            heading: {
                options: [
                    { model: 'paragraph', title: 'Paragraph', class: 'ck-heading_paragraph' },
                    { model: 'heading1', view: 'h1', title: 'Heading 1', class: 'ck-heading_heading1' },
                    { model: 'heading2', view: 'h2', title: 'Heading 2', class: 'ck-heading_heading2' },
                    { model: 'heading3', view: 'h3', title: 'Heading 3', class: 'ck-heading_heading3' }
                ]
            },
            alignment: {
                options: ['left', 'center', 'right', 'justify']
            },
            extraPlugins: [uploadAdapterPlugin],
        })
        .then(editor => {
            window.editor = editor;
            console.log('Editor đã được khởi tạo');
        })
        .catch(error => {
            console.error('Lỗi khởi tạo CKEditor:', error);
            updateStatus('Lỗi khởi tạo CKEditor: ' + error.message, 'error');
        });
</script>
@endsection