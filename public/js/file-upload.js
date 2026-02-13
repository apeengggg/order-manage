/**
 * file-upload.js - Global reusable file upload component
 *
 * Usage: App.FileUpload.init({ module: 'expeditions', moduleId: 5, container: '#file-section' })
 * This will render upload form + file list in the container.
 */
(function($) {
    'use strict';

    var FileUpload = {
        baseUrl: '',
        defaults: {
            module: '',
            moduleId: 0,
            container: '#file-upload-section',
            canUpload: true,
            canDelete: true,
            multiple: true,
            accept: '.jpg,.jpeg,.png,.gif,.webp,.pdf,.doc,.docx,.xls,.xlsx,.csv,.txt,.zip,.rar'
        },

        /**
         * Initialize file upload component
         * @param {object} options
         */
        init: function(options) {
            var opts = $.extend({}, this.defaults, options);
            this.baseUrl = $('meta[name="base-url"]').attr('content') || '/';
            this._render(opts);
            this._loadFiles(opts);
            this._bindEvents(opts);
        },

        _render: function(opts) {
            var html = '<div class="file-upload-wrapper">';

            if (opts.canUpload) {
                html += '<div class="file-upload-form mb-3">';
                html += '  <div class="input-group">';
                html += '    <div class="custom-file">';
                html += '      <input type="file" class="custom-file-input" id="fileInput_' + opts.module + '"';
                html += '        accept="' + opts.accept + '"' + (opts.multiple ? ' multiple' : '') + '>';
                html += '      <label class="custom-file-label" for="fileInput_' + opts.module + '">Pilih file...</label>';
                html += '    </div>';
                html += '    <div class="input-group-append">';
                html += '      <button type="button" class="btn btn-primary btn-upload-file" data-module="' + opts.module + '" data-module-id="' + opts.moduleId + '">';
                html += '        <i class="fas fa-upload mr-1"></i> Upload';
                html += '      </button>';
                html += '    </div>';
                html += '  </div>';
                html += '  <div class="upload-progress mt-2" style="display:none;">';
                html += '    <div class="progress"><div class="progress-bar progress-bar-striped progress-bar-animated" style="width:0%"></div></div>';
                html += '  </div>';
                html += '  <small class="text-muted">Maks. 5MB per file. Format: jpg, png, gif, pdf, doc, xls, csv, txt, zip, rar</small>';
                html += '</div>';
            }

            html += '<div class="file-list" id="fileList_' + opts.module + '">';
            html += '  <div class="text-center text-muted py-3"><i class="fas fa-spinner fa-spin mr-1"></i> Memuat file...</div>';
            html += '</div>';
            html += '</div>';

            $(opts.container).html(html);
        },

        _loadFiles: function(opts) {
            var self = this;
            $.getJSON(this.baseUrl + 'files/list', {
                module: opts.module,
                module_id: opts.moduleId
            }, function(res) {
                if (res.success) {
                    self._renderFiles(opts, res.files);
                } else {
                    $('#fileList_' + opts.module).html('<div class="text-center text-muted py-3">Gagal memuat file.</div>');
                }
            }).fail(function() {
                $('#fileList_' + opts.module).html('<div class="text-center text-muted py-3">Gagal memuat file.</div>');
            });
        },

        _renderFiles: function(opts, files) {
            var self = this;
            var container = $('#fileList_' + opts.module);

            if (!files || files.length === 0) {
                container.html('<div class="text-center text-muted py-3"><i class="fas fa-folder-open mr-1"></i> Belum ada file.</div>');
                return;
            }

            var html = '<div class="row">';
            files.forEach(function(file) {
                html += '<div class="col-md-3 col-sm-4 col-6 mb-3 file-item" data-file-id="' + file.id + '">';
                html += '  <div class="card h-100 shadow-sm">';

                // Thumbnail or icon
                if (file.is_image && file.thumb_url) {
                    html += '  <div class="card-img-top text-center p-2" style="height:140px;overflow:hidden;display:flex;align-items:center;justify-content:center;background:#f8f9fa;">';
                    html += '    <img src="' + file.thumb_url + '" alt="' + file.file_name + '" style="max-width:100%;max-height:130px;object-fit:contain;">';
                    html += '  </div>';
                } else {
                    html += '  <div class="card-img-top text-center p-3" style="height:140px;display:flex;align-items:center;justify-content:center;background:#f8f9fa;">';
                    html += '    <i class="' + self._getFileIcon(file.file_type) + ' fa-3x"></i>';
                    html += '  </div>';
                }

                html += '  <div class="card-body p-2">';
                html += '    <p class="card-text small text-truncate mb-1" title="' + file.file_name + '">' + file.file_name + '</p>';
                html += '    <small class="text-muted">' + file.size_formatted + '</small>';
                html += '  </div>';
                html += '  <div class="card-footer p-1 text-center">';
                html += '    <a href="' + file.download_url + '" class="btn btn-sm btn-outline-primary" title="Download Original"><i class="fas fa-download"></i></a>';

                if (file.is_image) {
                    html += '  <button type="button" class="btn btn-sm btn-outline-info btn-preview-file" data-url="' + file.url + '" data-name="' + file.file_name + '" title="Preview"><i class="fas fa-eye"></i></button>';
                }

                if (opts.canDelete) {
                    html += '  <button type="button" class="btn btn-sm btn-outline-danger btn-delete-file" data-file-id="' + file.id + '" title="Hapus"><i class="fas fa-trash"></i></button>';
                }

                html += '  </div>';
                html += '  </div>';
                html += '</div>';
            });
            html += '</div>';

            container.html(html);
        },

        _getFileIcon: function(mimeType) {
            if (!mimeType) return 'fas fa-file';
            if (mimeType.indexOf('pdf') > -1) return 'fas fa-file-pdf text-danger';
            if (mimeType.indexOf('word') > -1 || mimeType.indexOf('document') > -1) return 'fas fa-file-word text-primary';
            if (mimeType.indexOf('sheet') > -1 || mimeType.indexOf('excel') > -1) return 'fas fa-file-excel text-success';
            if (mimeType.indexOf('zip') > -1 || mimeType.indexOf('rar') > -1) return 'fas fa-file-archive text-warning';
            if (mimeType.indexOf('text') > -1) return 'fas fa-file-alt';
            return 'fas fa-file';
        },

        _bindEvents: function(opts) {
            var self = this;

            // File input label update
            $(opts.container).on('change', '.custom-file-input', function() {
                var files = this.files;
                var label = files.length > 1 ? files.length + ' file dipilih' : files[0].name;
                $(this).next('.custom-file-label').text(label);
            });

            // Upload button
            $(opts.container).on('click', '.btn-upload-file', function() {
                var fileInput = $('#fileInput_' + opts.module)[0];
                if (!fileInput.files.length) {
                    App.error('Pilih file terlebih dahulu.');
                    return;
                }

                var formData = new FormData();
                formData.append('module', opts.module);
                formData.append('module_id', opts.moduleId);

                for (var i = 0; i < fileInput.files.length; i++) {
                    formData.append(fileInput.files.length > 1 ? 'file[' + i + ']' : 'file', fileInput.files[i]);
                }

                // Restructure for multiple files
                if (fileInput.files.length > 1) {
                    formData = new FormData();
                    formData.append('module', opts.module);
                    formData.append('module_id', opts.moduleId);
                    for (var j = 0; j < fileInput.files.length; j++) {
                        formData.append('file[]', fileInput.files[j]);
                    }
                }

                var $progress = $(opts.container).find('.upload-progress');
                var $bar = $progress.find('.progress-bar');
                $progress.show();

                $.ajax({
                    url: self.baseUrl + 'files/upload',
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    xhr: function() {
                        var xhr = new XMLHttpRequest();
                        xhr.upload.addEventListener('progress', function(e) {
                            if (e.lengthComputable) {
                                var pct = Math.round((e.loaded / e.total) * 100);
                                $bar.css('width', pct + '%').text(pct + '%');
                            }
                        });
                        return xhr;
                    },
                    success: function(res) {
                        $progress.hide();
                        $bar.css('width', '0%').text('');

                        if (res.success) {
                            App.success(res.message);
                            fileInput.value = '';
                            $(fileInput).next('.custom-file-label').text('Pilih file...');
                            self._loadFiles(opts);
                        } else {
                            App.error(res.message);
                        }
                    },
                    error: function() {
                        $progress.hide();
                        $bar.css('width', '0%').text('');
                        App.error('Upload gagal. Coba lagi.');
                    }
                });
            });

            // Delete file
            $(opts.container).on('click', '.btn-delete-file', function() {
                var fileId = $(this).data('file-id');
                App.confirm({
                    title: 'Hapus file?',
                    text: 'File yang dihapus tidak bisa dikembalikan.',
                    confirmText: 'Ya, Hapus!',
                    confirmColor: '#d33',
                    onConfirm: function() {
                        $.post(self.baseUrl + 'files/delete/' + fileId, {}, function(res) {
                            if (res.success) {
                                App.success(res.message);
                                self._loadFiles(opts);
                            } else {
                                App.error(res.message);
                            }
                        }, 'json').fail(function() {
                            App.error('Gagal menghapus file.');
                        });
                    }
                });
            });

            // Preview image (original size)
            $(opts.container).on('click', '.btn-preview-file', function() {
                var url = $(this).data('url');
                var name = $(this).data('name');
                Swal.fire({
                    title: name,
                    imageUrl: url,
                    imageAlt: name,
                    width: 'auto',
                    showConfirmButton: false,
                    showCloseButton: true
                });
            });
        }
    };

    // Expose to App namespace
    App.FileUpload = FileUpload;

})(jQuery);
