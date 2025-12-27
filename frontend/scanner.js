// Scanner State
let videoStream = null;
let scanning = false;
let currentFacingMode = 'environment'; // Start with rear camera

// DOM Elements
const video = document.getElementById('video');
const canvas = document.getElementById('canvas');
const canvasContext = canvas.getContext('2d');
const statusMessage = document.getElementById('status-message');
const resultSection = document.getElementById('result-section');
const errorSection = document.getElementById('error-section');
const resultText = document.getElementById('result-text');
const resultTypeText = document.getElementById('result-type-text');
const errorMessage = document.getElementById('error-message');

// Buttons
const switchCameraBtn = document.getElementById('switch-camera-btn');
const uploadBtn = document.getElementById('upload-btn');
const imageInput = document.getElementById('image-input');
const closeResultBtn = document.getElementById('close-result-btn');
const copyResultBtn = document.getElementById('copy-result-btn');
const openResultBtn = document.getElementById('open-result-btn');
const retryBtn = document.getElementById('retry-btn');

// Initialize Scanner
async function initScanner() {
    try {
        await startCamera();
        errorSection.classList.add('hidden');
        scanning = true;
        requestAnimationFrame(scanQRCode);
    } catch (error) {
        showError(error);
    }
}

// Start Camera
async function startCamera() {
    try {
        // Stop existing stream if any
        if (videoStream) {
            videoStream.getTracks().forEach(track => track.stop());
        }

        const constraints = {
            video: {
                facingMode: currentFacingMode,
                width: { ideal: 1280 },
                height: { ideal: 720 }
            }
        };

        videoStream = await navigator.mediaDevices.getUserMedia(constraints);
        video.srcObject = videoStream;

        // Wait for video to be ready
        await new Promise((resolve) => {
            video.onloadedmetadata = () => {
                video.play();
                resolve();
            };
        });

        // Set canvas size to match video
        canvas.width = video.videoWidth || 640;
        canvas.height = video.videoHeight || 480;

    } catch (error) {
        console.error('Camera Error:', error);
        throw error;
    }
}

// Scan QR Code
function scanQRCode() {
    if (!scanning) return;

    if (video.readyState === video.HAVE_ENOUGH_DATA) {
        // Draw video frame to canvas
        canvasContext.drawImage(video, 0, 0, canvas.width, canvas.height);

        // Get image data
        const imageData = canvasContext.getImageData(0, 0, canvas.width, canvas.height);

        // Scan for QR code
        const code = jsQR(imageData.data, imageData.width, imageData.height, {
            inversionAttempts: "dontInvert",
        });

        if (code) {
            handleQRCodeDetected(code.data);
            return; // Stop scanning
        }
    }

    // Continue scanning
    requestAnimationFrame(scanQRCode);
}

// Handle Image Upload
function handleImageUpload(e) {
    const file = e.target.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = function (event) {
        const img = new Image();
        img.onload = function () {
            // Set canvas size to match image
            canvas.width = img.width;
            canvas.height = img.height;

            // Draw image to canvas
            canvasContext.drawImage(img, 0, 0);

            // Get image data
            try {
                const imageData = canvasContext.getImageData(0, 0, canvas.width, canvas.height);

                // Scan for QR code
                const code = jsQR(imageData.data, imageData.width, imageData.height, {
                    inversionAttempts: "dontInvert",
                });

                if (code) {
                    handleQRCodeDetected(code.data);
                } else {
                    alert('No QR code found in this image. Please try another one or use the live camera.');
                    // Reset input
                    imageInput.value = '';
                }
            } catch (err) {
                console.error('Decoding error:', err);
                alert('Failed to process image. Make sure it is a valid QR code image.');
                imageInput.value = '';
            }
        };
        img.onerror = function () {
            alert('Failed to load image. Please select a valid image file.');
            imageInput.value = '';
        };
        img.src = event.target.result;
    };
    reader.readAsDataURL(file);
}

// Handle QR Code Detection
function handleQRCodeDetected(data) {
    scanning = false;

    // Vibrate if supported
    if (navigator.vibrate) {
        navigator.vibrate(200);
    }

    // Determine type
    const type = detectQRType(data);

    // Display result
    resultText.textContent = data;
    resultTypeText.textContent = type;
    resultSection.classList.remove('hidden');
    resultSection.classList.add('show');

    // Update open button visibility
    if (type === 'URL' || type === 'Email') {
        openResultBtn.style.display = 'flex';
    } else {
        openResultBtn.style.display = 'none';
    }

    // Resync canvas size to video if coming from camera
    if (video.readyState === video.HAVE_ENOUGH_DATA) {
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
    }
}

// Detect QR Code Type
function detectQRType(data) {
    if (data.startsWith('http://') || data.startsWith('https://')) {
        return 'URL';
    } else if (data.startsWith('mailto:')) {
        return 'Email';
    } else if (data.startsWith('tel:')) {
        return 'Phone';
    } else if (data.startsWith('smsto:')) {
        return 'SMS';
    } else if (data.startsWith('WIFI:')) {
        return 'WiFi';
    } else if (data.startsWith('geo:')) {
        return 'Location';
    } else if (data.startsWith('BEGIN:VCARD')) {
        return 'Contact';
    } else {
        return 'Text';
    }
}

// Close Result and Resume Scanning
function closeResult() {
    resultSection.classList.remove('show');
    // Clear input
    imageInput.value = '';

    setTimeout(() => {
        resultSection.classList.add('hidden');
        scanning = true;
        requestAnimationFrame(scanQRCode);
    }, 300);
}

// Copy Result
async function copyResult() {
    const text = resultText.textContent;
    try {
        await navigator.clipboard.writeText(text);

        // Visual feedback
        const originalText = copyResultBtn.innerHTML;
        copyResultBtn.innerHTML = '<i class="fa-solid fa-check"></i> Copied!';
        copyResultBtn.style.background = 'linear-gradient(135deg, #51CF66 0%, #37B24D 100%)';

        setTimeout(() => {
            copyResultBtn.innerHTML = originalText;
            copyResultBtn.style.background = '';
        }, 2000);
    } catch (error) {
        console.error('Copy failed:', error);
        alert('Failed to copy to clipboard');
    }
}

// Open Result
function openResult() {
    const data = resultText.textContent;
    const type = resultTypeText.textContent;

    if (type === 'URL') {
        window.open(data, '_blank');
    } else if (type === 'Email') {
        window.location.href = data;
    } else if (type === 'Phone') {
        window.location.href = data;
    }
}

// Switch Camera
async function switchCamera() {
    currentFacingMode = currentFacingMode === 'environment' ? 'user' : 'environment';

    try {
        await startCamera();
        if (!scanning && !resultSection.classList.contains('show')) {
            scanning = true;
            requestAnimationFrame(scanQRCode);
        }
    } catch (error) {
        console.error('Failed to switch camera:', error);
        // Revert facing mode
        currentFacingMode = currentFacingMode === 'environment' ? 'user' : 'environment';
    }
}

// Show Error
function showError(error) {
    errorSection.classList.remove('hidden');

    if (error.name === 'NotAllowedError' || error.name === 'PermissionDeniedError') {
        errorMessage.textContent = 'Camera access was denied. Please allow camera permissions in your browser settings.';
    } else if (error.name === 'NotFoundError' || error.name === 'DevicesNotFoundError') {
        errorMessage.textContent = 'No camera found on this device.';
    } else if (error.name === 'NotReadableError' || error.name === 'TrackStartError') {
        errorMessage.textContent = 'Camera is already in use by another application.';
    } else {
        errorMessage.textContent = 'Unable to access camera. Please check your device settings.';
    }
}

// Retry Camera Access
function retryCamera() {
    errorSection.classList.add('hidden');
    initScanner();
}

// Event Listeners
closeResultBtn.addEventListener('click', closeResult);
copyResultBtn.addEventListener('click', copyResult);
openResultBtn.addEventListener('click', openResult);
switchCameraBtn.addEventListener('click', switchCamera);
uploadBtn.addEventListener('click', () => imageInput.click());
imageInput.addEventListener('change', handleImageUpload);
retryBtn.addEventListener('click', retryCamera);

// Cleanup on page unload
window.addEventListener('beforeunload', () => {
    if (videoStream) {
        videoStream.getTracks().forEach(track => track.stop());
    }
});

// Start scanner on page load
window.addEventListener('load', initScanner);
