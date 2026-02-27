(function () {
    let cropper = null;
    const input = document.getElementById('avatarInput');
    const cropperImage = document.getElementById('cropperImage');
    const btnCrop = document.getElementById('btnCrop');
    const btnReset = document.getElementById('btnReset');
    const avatarCropped = document.getElementById('avatarCropped');
    const currentAvatar = document.getElementById('currentAvatar');
    const originalSrc = currentAvatar ? currentAvatar.getAttribute('data-original-src') : '';

    if (!input) return;

    function destroyCropper() {
        if (cropper) {
            cropper.destroy();
            cropper = null;
        }
    }

    function setButtons(enabled) {
        btnCrop.disabled = !enabled;
        btnReset.disabled = !enabled;
    }

    input.addEventListener('change', function (e) {
        const file = e.target.files && e.target.files[0];
        if (!file) return;

        if (!file.type || !file.type.startsWith('image/')) {
            alert('Please select a valid image file.');
            input.value = '';
            return;
        }

        const reader = new FileReader();
        reader.onload = function () {
            destroyCropper();
            avatarCropped.value = '';

            cropperImage.src = reader.result;
            cropperImage.style.display = 'block';

            // Optional: preview the selected image immediately (before crop)
            if (currentAvatar) {
                currentAvatar.src = reader.result;
            }

            cropper = new Cropper(cropperImage, {
                viewMode: 1,
                aspectRatio: 1,
                autoCropArea: 1,
                responsive: true,
                background: false,
                dragMode: 'move',
                guides: true,
            });

            setButtons(true);
        };
        reader.readAsDataURL(file);
    });

    btnCrop.addEventListener('click', function () {
        if (!cropper) return;

        const canvas = cropper.getCroppedCanvas({
            width: 512,
            height: 512,
            imageSmoothingEnabled: true,
            imageSmoothingQuality: 'high',
        });

        const dataUrl = canvas.toDataURL('image/jpeg', 0.9);

        avatarCropped.value = dataUrl;

        // This now always works because #currentAvatar always exists
        currentAvatar.src = dataUrl;
    });

    btnReset.addEventListener('click', function () {
        destroyCropper();

        cropperImage.style.display = 'none';
        cropperImage.src = '';
        input.value = '';
        avatarCropped.value = '';

        // Restore what was on the page when it loaded:
        if (currentAvatar && originalSrc) {
            currentAvatar.src = originalSrc;
        }

        setButtons(false);
    });

    // If user submits without clicking Crop, we auto-crop once
    const profileForm = document.getElementById('profileForm');
    if (profileForm) {
        profileForm.addEventListener('submit', function () {
            if (cropper && !avatarCropped.value) {
                const canvas = cropper.getCroppedCanvas({ width: 512, height: 512 });
                avatarCropped.value = canvas.toDataURL('image/jpeg', 0.9);
            }
        });
    }
})();