function getPreviewElements() {
    return {
        input: document.getElementById('logo_suite_image_url'),
        preview: document.getElementById('logo-preview'),
        error: document.getElementById('logo-preview-error'),
        widthInput: document.getElementById('logo_suite_display_width'),
        heightInput: document.getElementById('logo_suite_display_height'),
        keepRatioInput: document.getElementById('logo_suite_keep_ratio')
    };
}

function applyPreviewSizeSettings() {
    const { preview, widthInput, heightInput, keepRatioInput } = getPreviewElements();
    if (!preview || !widthInput || !heightInput || !keepRatioInput) {
        return;
    }

    const width = parseInt(widthInput.value, 10) || 0;
    const height = parseInt(heightInput.value, 10) || 0;
    const keepRatio = keepRatioInput.checked;

    preview.style.width = '';
    preview.style.height = '';
    preview.style.objectFit = '';
    preview.style.maxHeight = '';

    const hasCustomSize = width > 0 || height > 0;
    if (!hasCustomSize) {
        return;
    }

    if (keepRatio) {
        if (width > 0 && height > 0) {
            preview.style.width = width + 'px';
            preview.style.height = height + 'px';
            preview.style.objectFit = 'contain';
        } else if (width > 0) {
            preview.style.width = width + 'px';
            preview.style.height = 'auto';
        } else {
            preview.style.height = height + 'px';
            preview.style.width = 'auto';
        }
    } else {
        if (width > 0) {
            preview.style.width = width + 'px';
        }
        if (height > 0) {
            preview.style.height = height + 'px';
        }
    }

    preview.style.maxHeight = 'none';
}

function syncDimensionsFromRatio(changedField) {
    const { preview, widthInput, heightInput, keepRatioInput } = getPreviewElements();
    if (!preview || !widthInput || !heightInput || !keepRatioInput || !keepRatioInput.checked) {
        return;
    }

    const naturalWidth = preview.naturalWidth || 0;
    const naturalHeight = preview.naturalHeight || 0;
    if (!naturalWidth || !naturalHeight) {
        return;
    }

    const ratio = naturalWidth / naturalHeight;
    const width = parseInt(widthInput.value, 10) || 0;
    const height = parseInt(heightInput.value, 10) || 0;

    if (changedField === 'width' && width > 0) {
        heightInput.value = Math.max(1, Math.round(width / ratio));
    } else if (changedField === 'height' && height > 0) {
        widthInput.value = Math.max(1, Math.round(height * ratio));
    } else if (changedField === 'ratio-toggle' && width > 0 && height > 0) {
        heightInput.value = Math.max(1, Math.round(width / ratio));
    }
}

function prefillDimensionsFromLoadedImage() {
    const { preview, widthInput, heightInput } = getPreviewElements();
    if (!preview || !widthInput || !heightInput) {
        return;
    }

    if (!preview.naturalWidth || !preview.naturalHeight) {
        return;
    }

    const widthEmpty = !widthInput.value || parseInt(widthInput.value, 10) <= 0;
    const heightEmpty = !heightInput.value || parseInt(heightInput.value, 10) <= 0;

    if (widthEmpty) {
        widthInput.value = preview.naturalWidth;
    }
    if (heightEmpty) {
        heightInput.value = preview.naturalHeight;
    }
}

function updateLogoPreview() {
    const { input, preview, error } = getPreviewElements();
    if (!input || !preview || !error) {
        return;
    }

    const url = input.value.trim();
    error.classList.add('logo-preview-hidden');

    if (url) {
        preview.src = url;
        preview.classList.remove('logo-preview-hidden');
    } else {
        preview.src = '';
        preview.classList.add('logo-preview-hidden');
        error.classList.add('logo-preview-hidden');
    }
}

function isInsecureLogoUrl(url) {
    return window.location.protocol === 'https:' && /^http:\/\//i.test(url);
}

function showInsecureLogoAlert() {
    alert('⚠️ Mixed content blocked: this admin is in HTTPS, so the logo URL must also be HTTPS.');
}

function logoPreviewError() {
    const { error } = getPreviewElements();
    if (!error) {
        return;
    }

    error.classList.remove('logo-preview-hidden');
}

function logoPreviewSuccess() {
    const { error } = getPreviewElements();
    if (!error) {
        return;
    }

    error.classList.add('logo-preview-hidden');
    prefillDimensionsFromLoadedImage();
    applyPreviewSizeSettings();
}

document.addEventListener('DOMContentLoaded', function () {
    const { input, preview, widthInput, heightInput, keepRatioInput } = getPreviewElements();
    const form = document.querySelector('form.logo-suite-form');
    const fileInput = document.getElementById('logo_suite_image_file');
    const uploadAlert = document.getElementById('logo-upload-alert');
    if (!input || !form) {
        return;
    }

    input.addEventListener('input', updateLogoPreview);

    if (widthInput && heightInput && keepRatioInput) {
        widthInput.addEventListener('input', function () {
            syncDimensionsFromRatio('width');
            applyPreviewSizeSettings();
        });
        heightInput.addEventListener('input', function () {
            syncDimensionsFromRatio('height');
            applyPreviewSizeSettings();
        });
        keepRatioInput.addEventListener('change', function () {
            syncDimensionsFromRatio('ratio-toggle');
            applyPreviewSizeSettings();
        });
    }

    input.addEventListener('blur', function () {
        const url = input.value.trim();
        const hasLocalUpload = fileInput && fileInput.files && fileInput.files.length > 0;
        if (hasLocalUpload) {
            return;
        }
        if (url && isInsecureLogoUrl(url)) {
            showInsecureLogoAlert();
        }
    });

    form.addEventListener('submit', function (event) {
        const submitter = event.submitter;
        const isSaveAction = !submitter || submitter.name === 'logo_suite_save_settings';
        if (!isSaveAction) {
            return;
        }

        const url = input.value.trim();
        const hasLocalUpload = fileInput && fileInput.files && fileInput.files.length > 0;
        if (hasLocalUpload) {
            return;
        }
        if (url && isInsecureLogoUrl(url)) {
            event.preventDefault();
            showInsecureLogoAlert();
            input.focus();
        }
    });

    if (fileInput && uploadAlert) {
        const toggleUploadAlert = function () {
            const hasLocalUpload = fileInput.files && fileInput.files.length > 0;
            uploadAlert.classList.toggle('logo-upload-alert-hidden', !hasLocalUpload);
        };

        fileInput.addEventListener('change', toggleUploadAlert);
        toggleUploadAlert();
    }

    if (preview && !preview.classList.contains('logo-preview-hidden') && preview.complete && preview.naturalWidth > 0) {
        logoPreviewSuccess();
    } else {
        applyPreviewSizeSettings();
    }
});
