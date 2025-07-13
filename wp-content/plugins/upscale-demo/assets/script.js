jQuery(document).ready(function($) {
    const dropbox = $('#upscale-dropbox');
    const fileInput = $('#upscale-file-input');
    const spinner = $('#upscale-spinner');
    const status = $('#upscale-status');
    const progressContainer = $('#progress-container');
    const progressBar = $('#progress-bar');
    const results = $('#upscale-results');

    function resetUI() {
        status.text('');
        results.html('');
        progressContainer.hide();
        progressBar.width('0%');
    }

    $('#select-file-btn').on('click', function() {
        fileInput.click();
    });    

    dropbox.on('dragover', function(e) {
        e.preventDefault();
        e.stopPropagation();
        dropbox.addClass('dragover');
    });

    dropbox.on('dragleave', function(e) {
        e.preventDefault();
        e.stopPropagation();
        dropbox.removeClass('dragover');
    });

    dropbox.on('drop', function(e) {
        e.preventDefault();
        e.stopPropagation();
        dropbox.removeClass('dragover');
        const files = e.originalEvent.dataTransfer.files;
        if (files.length) {
            uploadFile(files[0]);
        }
    });

    fileInput.on('change', function() {
        if (this.files.length) {
            uploadFile(this.files[0]);
        }
    });

    function uploadFile(file) {
        resetUI();
        spinner.show();
        status.text('Uploading and processing... please wait.');

        const formData = new FormData();
        formData.append('action', 'upscale_image');
        formData.append('nonce', UpscaleDemoAjax.nonce);
        formData.append('file', file);

        $.ajax({
            url: UpscaleDemoAjax.ajaxurl,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            xhr: function() {
                const xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener("progress", function(evt) {
                    if (evt.lengthComputable) {
                        const percentComplete = (evt.loaded / evt.total) * 100;
                        progressContainer.show();
                        progressBar.width(percentComplete + '%');
                    }
                }, false);
                return xhr;
            },
            success: function(response) {
                spinner.hide();
                if (response.success) {
                    status.text('Upscaling complete!');
                    const original = response.data.original;
                    const upscaled = response.data.upscaled;

                    results.html(`
                        <h3>Comparison Preview</h3>
                        <div class="image-comparison">
                            <div class="images-container" style="height:400px;">
                                <img class="before-image" src="${original}" alt="Before Image" />
                                <img class="after-image" src="${upscaled}" alt="After Image" />
                                <div class="label before-label">Before</div>
                                <div class="label after-label">After</div>
                                <div class="slider-line"></div>
                                <div class="slider-icon" aria-hidden="true">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:100%; height:100%;">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9"/>
                                    </svg>
                                </div>
                                <input type="range" class="slider" min="0" max="100" value="50" aria-label="Image comparison slider"/>
                            </div>
                        </div>
                        <p>
                            <a href="${upscaled}" download class="blue-button" target="_blank">⬇ Download Upscaled Image</a>
                        </p>
                    `);
                    
                    const container = results.find(".image-comparison")[0];
                    if (container) {
                        const slider = container.querySelector(".slider");
                        const beforeImage = container.querySelector(".before-image");
                        const sliderLine = container.querySelector(".slider-line");
                        const sliderIcon = container.querySelector(".slider-icon");
                    
                        slider.addEventListener("input", (e) => {
                            const value = e.target.value;
                            beforeImage.style.clipPath = `inset(0 ${100 - value}% 0 0)`;
                            sliderLine.style.left = value + "%";
                            sliderIcon.style.left = value + "%";
                        });
                    }                    
                } else {
                    status.text('Error: ' + response.data);
                }
            },
            error: function(xhr, statusText, errorThrown) {
                spinner.hide();
                let msg = 'An unexpected error occurred.';

                if (xhr.responseJSON?.data) {
                    msg = 'Error: ' + xhr.responseJSON.data;
                } else if (xhr.responseText) {
                    msg = 'Server Error: ' + xhr.responseText;
                } else if (errorThrown) {
                    msg = 'Request failed: ' + errorThrown;
                }

                console.error('[Upscale Demo] AJAX error', {
                    status: statusText,
                    error: errorThrown,
                    response: xhr
                });

                status.text(msg);
            }
        });
    }
});
