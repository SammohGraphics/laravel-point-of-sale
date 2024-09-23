<script>
    function previewImage() {
        const image = document.querySelector('#image');
        const imagePreview = document.querySelector('#image-preview');

        // Display the image preview element
        imagePreview.style.display = 'block';

        // Create a FileReader object
        const oFReader = new FileReader();

        // Read the selected file
        oFReader.readAsDataURL(image.files[0]);

        // When the file is read, set the src of the image preview
        oFReader.onload = function(oFREvent) {
            imagePreview.src = oFREvent.target.result;
        }

        // Reset the preview if no file is selected
        if (image.files.length === 0) {
            imagePreview.style.display = 'none'; // Hide the preview if no file is selected
            imagePreview.src = '#'; // Reset the image source
        }
    }
</script>
