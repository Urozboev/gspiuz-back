{{-- Bitta rasm yuklash: yuklangan fayl nomi formaga `dropzone_images` boʻlib qoʻshiladi. --}}
<script>
    window.addEventListener('load', function () {
        var el = document.querySelector('div#dropzone');
        if (!el || typeof Dropzone === 'undefined') {
            return;
        }

        new Dropzone(el, {
            url: "{{ url('/admin/upload_from_dropzone') }}",
            paramName: 'file',
            headers: {'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')},
            addRemoveLinks: true,
            maxFiles: 1,
            maxFilesize: 15,
            acceptedFiles: 'image/*',
            success: function (file, response) {
                var form = document.getElementById('department-form');
                var old = form.querySelector('input[name="dropzone_images"]');
                if (old) {
                    old.remove();
                }
                var input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'dropzone_images';
                input.value = response.file_name;
                form.append(input);
            },
            removedfile: function (file) {
                file.previewElement.remove();
                var input = document.querySelector('input[name="dropzone_images"]');
                if (input) {
                    input.remove();
                }
            },
            error: function (file, message) {
                alert(typeof message === 'string' ? message : (message.error || 'Rasm yuklanmadi'));
                this.removeFile(file);
            },
            dictDefaultMessage: 'Rasmni shu yerga tashlang yoki bosing',
            dictRemoveFile: 'Olib tashlash',
            dictCancelUpload: 'Bekor qilish',
            dictMaxFilesExceeded: 'Faqat bitta rasm'
        });
    });
</script>
