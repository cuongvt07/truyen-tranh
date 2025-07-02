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
        
        <!-- Thêm nút quản lý localStorage -->
        <div class="form-group">
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-secondary btn-sm" onclick="loadFromStorage()">
                    <i class="fa fa-download"></i> Tải từ localStorage
                </button>
                <button type="button" class="btn btn-info btn-sm" onclick="showStorageInfo()">
                    <i class="fa fa-info-circle"></i> Thông tin lưu trữ
                </button>
                <button type="button" class="btn btn-warning btn-sm" onclick="clearStorage()">
                    <i class="fa fa-trash"></i> Xóa localStorage
                </button>
            </div>
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
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

@section('ArticleScripts')
<script>
    let editorInstance = null;
    
    // Constants cho localStorage
    const STORAGE_KEY = 'ckeditor_images_{{ $article->id ?? "new" }}'; // Unique key cho từng article
    const CONTENT_KEY = 'ckeditor_content_{{ $article->id ?? "new" }}';
    
    // Lưu trữ ảnh tạm thời (chỉ trong session, chưa lưu localStorage)
    let tempImages = {};

    // Hàm chuyển đổi file thành base64
    function fileToBase64(file) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onload = () => resolve(reader.result);
            reader.onerror = reject;
            reader.readAsDataURL(file);
        });
    }

    // Hàm lưu ảnh vào localStorage (chỉ khi submit form)
    function saveImageToStorage(base64Data, fileName) {
        try {
            const images = JSON.parse(localStorage.getItem(STORAGE_KEY) || '{}');
            const imageId = Date.now() + '_' + Math.random().toString(36).substr(2, 9);
            images[imageId] = {
                data: base64Data,
                name: fileName,
                timestamp: Date.now()
            };
            localStorage.setItem(STORAGE_KEY, JSON.stringify(images));
            return imageId;
        } catch (error) {
            console.error('Lỗi khi lưu ảnh vào localStorage:', error);
            return null;
        }
    }

    // Hàm lưu ảnh tạm thời để preview
    function saveTempImage(file) {
        return new Promise(async (resolve, reject) => {
            try {
                const base64Data = await fileToBase64(file);
                const tempId = 'temp_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
                
                tempImages[tempId] = {
                    data: base64Data,
                    name: file.name,
                    file: file
                };
                
                updateStatus(`Đã tải ảnh "${file.name}" để preview (chưa lưu localStorage)`, 'info');
                resolve(base64Data);
            } catch (error) {
                reject(error);
            }
        });
    }

    // Custom Upload Adapter - chỉ để preview local
    class LocalPreviewUploadAdapter {
        constructor(loader) {
            this.loader = loader;
        }

        upload() {
            return new Promise((resolve, reject) => {
                this.loader.file.then(async file => {
                    try {
                        const base64Data = await saveTempImage(file);
                        resolve({
                            default: base64Data // Sử dụng base64 để preview
                        });
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

    // Plugin để xử lý paste ảnh
    function handlePasteImages(editor) {
        editor.editing.view.document.on('clipboardInput', (evt, data) => {
            const dataTransfer = data.dataTransfer;
            
            if (dataTransfer && dataTransfer.files && dataTransfer.files.length > 0) {
                const files = Array.from(dataTransfer.files);
                
                files.forEach(async file => {
                    if (file.type.startsWith('image/')) {
                        try {
                            const base64Data = await saveTempImage(file);
                            
                            // Chèn ảnh vào editor để preview
                            editor.model.change(writer => {
                                const imageElement = writer.createElement('imageBlock', {
                                    src: base64Data,
                                    alt: file.name
                                });
                                
                                editor.model.insertContent(imageElement, editor.model.document.selection);
                            });
                        } catch (error) {
                            console.error('Lỗi khi xử lý ảnh paste:', error);
                        }
                    }
                });
            }
        });
    }

    // Plugin Upload Adapter
    function uploadAdapterPlugin(editor) {
        editor.plugins.get('FileRepository').createUploadAdapter = (loader) => {
            return new LocalPreviewUploadAdapter(loader);
        };
    }

    // Hàm lưu tất cả ảnh tạm thời vào localStorage khi submit
    function saveAllTempImages() {
        const savedImages = [];
        
        for (let tempId in tempImages) {
            const tempImage = tempImages[tempId];
            const imageId = saveImageToStorage(tempImage.data, tempImage.name);
            if (imageId) {
                savedImages.push({
                    tempId: tempId,
                    imageId: imageId,
                    name: tempImage.name
                });
            }
        }
        
        // Xóa ảnh tạm thời sau khi lưu
        tempImages = {};
        
        return savedImages;
    }

    // Cập nhật trạng thái
    function updateStatus(message, type = 'info') {
        const statusDiv = document.getElementById('storage-status');
        if (!statusDiv) return;
        
        const alertClass = type === 'success' ? 'alert-success' : 
                          type === 'warning' ? 'alert-warning' : 
                          type === 'error' ? 'alert-danger' : 'alert-info';
        
        statusDiv.innerHTML = `<div class="alert ${alertClass} alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            ${message}
        </div>`;
        
        // Tự động ẩn sau 5 giây
        setTimeout(() => {
            statusDiv.innerHTML = '';
        }, 5000);
    }

    // Khởi tạo CKEditor
    ClassicEditor
        .create(document.querySelector('#content'), {
            extraPlugins: [uploadAdapterPlugin],
            toolbar: {
                items: [
                    'heading',
                    '|',
                    'bold',
                    'italic',
                    'link',
                    'bulletedList',
                    'numberedList',
                    '|',
                    'outdent',
                    'indent',
                    '|',
                    'imageUpload',
                    'blockQuote',
                    'insertTable',
                    'mediaEmbed',
                    'undo',
                    'redo'
                ]
            },
            language: 'vi',
            image: {
                toolbar: [
                    'imageTextAlternative',
                    'imageStyle:full',
                    'imageStyle:side'
                ]
            },
            table: {
                contentToolbar: [
                    'tableColumn',
                    'tableRow',
                    'mergeTableCells'
                ]
            }
        })
        .then(editor => {
            editorInstance = editor;
            console.log('CKEditor khởi tạo thành công');
            
            // Xử lý paste ảnh
            handlePasteImages(editor);
            
            // Tải nội dung đã lưu (nếu có)
            const savedContent = localStorage.getItem(CONTENT_KEY);
            if (savedContent && savedContent !== editor.getData()) {
                if (confirm('Có nội dung đã lưu trong localStorage. Bạn có muốn tải nó không?')) {
                    editor.setData(savedContent);
                    updateStatus('Đã tải nội dung từ localStorage', 'success');
                }
            }
        })
        .catch(error => {
            console.error('Lỗi khởi tạo CKEditor:', error);
            updateStatus('Lỗi khởi tạo CKEditor: ' + error.message, 'error');
        });

    // Hàm tải nội dung từ localStorage
    function loadFromStorage() {
        const savedContent = localStorage.getItem(CONTENT_KEY);
        if (savedContent && editorInstance) {
            if (confirm('Tải nội dung từ localStorage sẽ ghi đè nội dung hiện tại. Bạn có chắc chắn?')) {
                // Xóa ảnh tạm thời khi tải nội dung mới
                tempImages = {};
                
                editorInstance.setData(savedContent);
                updateStatus('Đã tải nội dung từ localStorage!', 'success');
            }
        } else {
            updateStatus('Không có nội dung đã lưu trong localStorage!', 'warning');
        }
    }

    // Hàm xóa localStorage
    function clearStorage() {
        if (confirm('Bạn có chắc chắn muốn xóa tất cả dữ liệu localStorage cho chapter này?')) {
            localStorage.removeItem(STORAGE_KEY);
            localStorage.removeItem(CONTENT_KEY);
            tempImages = {};
            
            updateStatus('Đã xóa tất cả dữ liệu localStorage!', 'success');
        }
    }

    // Hiển thị thông tin localStorage
    function showStorageInfo() {
        const images = JSON.parse(localStorage.getItem(STORAGE_KEY) || '{}');
        const imageCount = Object.keys(images).length;
        const tempImageCount = Object.keys(tempImages).length;
        const content = localStorage.getItem(CONTENT_KEY);
        
        const storageSize = checkStorageSize();
        
        const info = `
            <strong>Thông tin localStorage cho chapter này:</strong><br>
            📸 Ảnh đã lưu: ${imageCount}<br>
            🔄 Ảnh đang preview: ${tempImageCount}<br>
            📝 Nội dung: ${content ? 'Có (' + Math.round(content.length/1024) + ' KB)' : 'Không có'}<br>
            💾 Tổng dung lượng localStorage: ${storageSize} MB
        `;
        
        updateStatus(info, 'info');
        console.log('Storage info:', { 
            imageCount, 
            tempImageCount, 
            hasContent: !!content, 
            contentSize: content ? content.length : 0,
            storageSize 
        });
    }

    // Kiểm tra dung lượng localStorage
    function checkStorageSize() {
        let total = 0;
        for (let key in localStorage) {
            if (localStorage.hasOwnProperty(key)) {
                total += localStorage[key].length;
            }
        }
        return (total / 1024 / 1024).toFixed(2); // MB
    }

    // Tự động hiển thị số ảnh đang preview
    setInterval(() => {
        const tempCount = Object.keys(tempImages).length;
        if (tempCount > 0) {
            document.title = `(${tempCount} ảnh preview) ` + document.title.replace(/^\(\d+ ảnh preview\) /, '');
        } else {
            document.title = document.title.replace(/^\(\d+ ảnh preview\) /, '');
        }
    }, 2000);

    // Cảnh báo khi rời khỏi trang nếu có ảnh chưa lưu
    window.addEventListener('beforeunload', function(e) {
        const tempCount = Object.keys(tempImages).length;
        if (tempCount > 0) {
            const message = `Bạn có ${tempCount} ảnh chưa được lưu vào localStorage. Bạn có chắc muốn rời khỏi trang?`;
            e.preventDefault();
            e.returnValue = message;
            return message;
        }
    });
</script>
@endsection