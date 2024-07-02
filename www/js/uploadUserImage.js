$(document).ready(function () {
    let avatar = document.getElementById('avatar');
    let image = document.getElementById('userImage');
    let input = document.getElementById('uploadUserImage');
    let $modal = $('#uploadImageModalToggle');
    let cropper;

    $('[data-toggle="tooltip"]').tooltip();

    input.addEventListener('change', function (e) {
        let files = e.target.files;
        let done = function (url) {
            input.value = '';
            image.src = url;
            $modal.modal('show');
        };
        let reader;
        let file;

        if (files && files.length > 0) {
            file = files[0];

            if (URL) {
                done(URL.createObjectURL(file));
            } else if (FileReader) {
                reader = new FileReader();
                reader.onload = function (e) {
                    done(reader.result);
                };
                reader.readAsDataURL(file);
            }
        }
    });

    $modal.on('shown.bs.modal', function () {
        cropper = new Cropper(image, {
            aspectRatio: 1,
            viewMode: 3,
        });
    }).on('hidden.bs.modal', function () {
        cropper.destroy();
        cropper = null;
    });

    document.getElementById('crop').addEventListener('click', function () {
        let canvas;
        if (cropper) {
            canvas = cropper.getCroppedCanvas({
                width: 160,
                height: 160,
            });
            avatar.src = canvas.toDataURL();
            console.log('canvas.toDataURL()');
            console.log(canvas.toDataURL());
            document.getElementById('croppedImage').value = canvas.toDataURL();
            canvas.toBlob(function (blob) {
                let formData = new FormData();
                formData.append('imageURL', blob);
            });
        }
    });
});