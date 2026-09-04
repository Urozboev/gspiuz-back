const editors = {};

const initCK = (el) => {
    ClassicEditor
        .create(el, {
            toolbar: {
                items: [
                    // oddiy pluginlar bo‘lishi kerak yoki custom build bo‘lsa ishlaydi
                    'bold', 'italic', 'link', 'undo', 'redo'
                ]
            }
        })
        .then(editor => {
            const lang = $(el).attr('data-lang');
            editors[lang] = editor;
        })
        .catch(error => {
            console.error(error);
        });
};
