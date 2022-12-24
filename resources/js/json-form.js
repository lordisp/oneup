const dropzone = document.querySelector('.dropzone');

dropzone.addEventListener('dragover', e => {
    e.preventDefault();
    dropzone.classList.add('dragover');
});

dropzone.addEventListener('dragleave', e => {
    e.preventDefault();
    dropzone.classList.remove('dragover');
});

dropzone.addEventListener('drop', e => {
    e.preventDefault();
    dropzone.classList.remove('dragover');

    // Add uploaded files to Livewire model variable
    let files = Array.from(e.dataTransfer.files);
    files.forEach(file => {
        let reader = new FileReader();
        reader.readAsText(file);
        reader.onloadend = () => {
            window.Livewire.emit('addJson', reader.result);
        };
    });
});
