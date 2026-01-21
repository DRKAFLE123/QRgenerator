const API_URL = '/api/generate-qr';
const BIO_API_URL = '/api/create-bio';

// File Size Validation (10MB limit)
function validateFileSize(input, errorId) {
    const maxSize = 10 * 1024 * 1024; // 10MB in bytes
    const errorElement = document.getElementById(errorId);

    if (input.files.length > 0) {
        const file = input.files[0];
        const fileSizeMB = (file.size / (1024 * 1024)).toFixed(2);

        if (file.size > maxSize) {
            errorElement.textContent = `File size (${fileSizeMB}MB) exceeds the 10MB limit. Please choose a smaller file.`;
            errorElement.style.display = 'block';
            input.value = ''; // Clear the input
        } else {
            errorElement.style.display = 'none';
            errorElement.textContent = '';
        }
    }
}

// URL Validation helper
function isValidURL(url) {
    if (!url) return false;
    try {
        const pattern = new RegExp('^(https?:\\/\\/)?' + // protocol
            '((([a-z\\d]([a-z\\d-]*[a-z\\d])*)\\.)+[a-z]{2,}|' + // domain name
            '((\\d{1,3}\\.){3}\\d{1,3}))' + // OR ip (v4) address
            '(\\:\\d+)?(\\/[-a-z\\d%_.~+]*)*' + // port and path
            '(\\?[;&a-z\\d%_.~+=-]*)?' + // query string
            '(\\#[-a-z\\d_]*)?$', 'i'); // fragment locator
        return !!pattern.test(url);
    } catch (e) {
        return false;
    }
}

function showNotification(message, type = 'success') {
    const container = document.getElementById('toast-container');
    if (!container) return;

    const toast = document.createElement('div');
    toast.className = `toast ${type}`;

    const icon = type === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation';

    toast.innerHTML = `
        <i class="fa-solid ${icon}"></i>
        <div class="message">${message}</div>
    `;

    container.appendChild(toast);

    // Auto remove after 3 seconds
    setTimeout(() => {
        toast.classList.add('fade-out');
        setTimeout(() => {
            toast.remove();
        }, 300);
    }, 3000);
}


// Mode Switching
function switchMode(mode) {
    // Update Tabs
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    document.getElementById(`tab-${mode}`).classList.add('active');

    // Update Views
    document.getElementById('mode-single').style.display = mode === 'single' ? 'block' : 'none';
    document.getElementById('mode-bio').style.display = mode === 'bio' ? 'block' : 'none';

    // Reset Preview
    document.getElementById('qr-image').style.display = 'none';
    document.getElementById('qr-placeholder').style.display = 'block';
}

// Bio Page Logic
function addBioLink() {
    const container = document.getElementById('bio-links-container');
    const row = document.createElement('div');
    row.className = 'bio-link-row';

    row.innerHTML = `
        <select class="bio-platform" onchange="updateBioLinkInput(this)">
            <option value="website">Website</option>
            <option value="facebook">Facebook</option>
            <option value="instagram">Instagram</option>
            <option value="twitter">Twitter</option>
            <option value="linkedin">LinkedIn</option>
            <option value="youtube">YouTube</option>
            <option value="tiktok">TikTok</option>
            <option value="whatsapp">WhatsApp</option>
            <option value="phone">Phone</option>
            <option value="sms">SMS</option>
            <option value="text">Plain Text</option>
        </select>
        <input type="text" class="bio-url" placeholder="URL or Username">
        <button class="remove-link-btn" onclick="this.parentElement.remove()">
            <i class="fa-solid fa-trash"></i>
        </button>
    `;

    container.appendChild(row);
}

function updateBioLinkInput(select) {
    const input = select.nextElementSibling;
    const type = select.value;

    if (type === 'phone' || type === 'sms') {
        input.placeholder = 'Phone Number (e.g. +1234567890)';
        input.type = 'tel';
    } else if (type === 'text') {
        input.placeholder = 'Enter your text here...';
        input.type = 'text';
    } else {
        input.placeholder = 'URL or Username';
        input.type = 'text';
    }
}

async function createBioPage() {
    const website = document.getElementById('bio-website').value;
    const theme = document.getElementById('bio-theme').value;
    const logoFile = document.getElementById('bio-logo').files[0];
    const coverFile = document.getElementById('bio-cover').files[0];

    const orgName = document.getElementById('bio-org-name').value;
    const color = document.getElementById('bio-color').value;
    const bgColor = document.getElementById('bio-bg-color').value;

    if (!orgName) {
        showNotification('Please enter an Organization Name', 'error');
        return;
    }

    if (website && !isValidURL(website)) {
        showNotification('Please enter a valid website URL (e.g., https://example.com)', 'error');
        return;
    }

    const links = [];
    document.querySelectorAll('.bio-link-row').forEach(row => {
        const platform = row.querySelector('.bio-platform').value;
        const url = row.querySelector('.bio-url').value;
        if (url) {
            links.push({ platform, url });
        }
    });

    if (links.length === 0) {
        showNotification('Please add at least one link or text block', 'error');
        return;
    }

    // Validate social links
    for (const link of links) {
        if (['text', 'phone', 'sms'].includes(link.platform)) continue;

        if (link.platform === 'website' && !isValidURL(link.url)) {
            showNotification(`Invalid URL for Website link`, 'error');
            return;
        }
        if (['facebook', 'linkedin', 'youtube'].includes(link.platform) && !isValidURL(link.url)) {
            showNotification(`Please enter a valid URL for ${link.platform}`, 'error');
            return;
        }
    }

    // Show loading
    const btn = document.getElementById('create-bio-btn');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Creating...';
    btn.disabled = true;

    try {
        const formData = new FormData();
        formData.append('name', orgName);
        formData.append('links', JSON.stringify(links));
        formData.append('color', color);
        formData.append('bg_color', bgColor);
        formData.append('theme', theme);
        if (website) formData.append('website', website);
        if (logoFile) formData.append('logo', logoFile);
        if (coverFile) formData.append('cover', coverFile);

        const response = await fetch(BIO_API_URL, {
            method: 'POST',
            headers: {
                'Accept': 'application/json'
            },
            body: formData
        });

        if (!response.ok) {
            const errorText = await response.text();
            let errorMessage = 'Failed to create Bio Page';
            try {
                const errorJson = JSON.parse(errorText);
                if (errorJson.message) errorMessage = errorJson.message;
                if (errorJson.errors) {
                    errorMessage = Object.values(errorJson.errors).flat().join(', ');
                }
            } catch (e) {
                // If not JSON, use the raw text (truncated if too long)
                errorMessage = errorText.substring(0, 100);
            }
            throw new Error(errorMessage);
        }

        const result = await response.json();

        // Generate QR for the Bio Page URL
        await generateQRForUrl(result.bio_url, color, bgColor);
        showNotification('Bio Page created successfully! ✨');


    } catch (error) {
        console.error(error);
        showNotification(error.message || 'Error creating Bio Page', 'error');
    } finally {
        btn.innerHTML = originalText;
        btn.disabled = false;
    }
}

async function generateQRForUrl(url, color, bgColor = '#FFFFFF') {
    try {
        const formData = new FormData();
        formData.append('data', url);
        formData.append('type', 'url');
        formData.append('size', 10);
        formData.append('color', color);
        formData.append('bg_color', bgColor);

        const response = await fetch(API_URL, {
            method: 'POST',
            headers: {
                'Accept': 'application/json'
            },
            body: formData
        });

        if (!response.ok) throw new Error('Generation failed');

        const result = await response.json();

        // Update UI
        const img = document.getElementById('qr-image');
        img.src = result.qr_image;
        img.style.display = 'block';
        document.getElementById('qr-placeholder').style.display = 'none';

    } catch (error) {
        console.error(error);
        showNotification('Failed to generate QR code', 'error');
    }
}


function updateForm() {
    // Hide all forms
    document.querySelectorAll('#input-forms > div').forEach(div => {
        div.classList.remove('active-form');
        div.classList.add('hidden-form');
    });

    const type = document.getElementById('qr-type').value;
    let formId = 'form-url';
    let socialLabel = 'Profile URL';
    let socialPlaceholder = 'https://...';

    // Social Media Logic
    const socialTypes = ['facebook', 'instagram', 'twitter', 'linkedin', 'youtube', 'tiktok', 'whatsapp'];

    if (socialTypes.includes(type)) {
        formId = 'form-social';
        const socialInput = document.getElementById('social-input');

        if (type === 'instagram' || type === 'twitter' || type === 'tiktok') {
            socialLabel = 'Username';
            socialPlaceholder = '@username';
        } else if (type === 'whatsapp') {
            socialLabel = 'Phone Number';
            socialPlaceholder = '1234567890';
        }

        document.getElementById('social-label').innerText = socialLabel;
        document.getElementById('social-input').placeholder = socialPlaceholder;
    } else {
        formId = `form-${type}`;
    }

    // Show selected form
    const selectedForm = document.getElementById(formId);
    if (selectedForm) {
        selectedForm.classList.remove('hidden-form');
        selectedForm.classList.add('active-form');
    }
}

async function generateQR() {
    const type = document.getElementById('qr-type').value;
    const color = document.getElementById('color-fg').value;
    const bgColor = document.getElementById('color-bg').value;

    let data = '';

    // Collect data based on type
    if (type === 'url') {
        data = document.getElementById('url-input').value;
    } else if (type === 'text') {
        data = document.getElementById('text-input').value;
    } else if (type === 'wifi') {
        const ssid = document.getElementById('wifi-ssid').value;
        const pass = document.getElementById('wifi-password').value;
        const enc = document.getElementById('wifi-type').value;

        if (!ssid) {
            showNotification('Please enter a Network Name (SSID)', 'error');
            return;
        }
        data = `WIFI:S:${ssid};T:${enc};P:${pass};;`;
    } else if (type === 'email') {
        const email = document.getElementById('email-address').value;
        const subject = document.getElementById('email-subject').value;
        const body = document.getElementById('email-body').value;

        if (!email || !email.includes('@')) {
            showNotification('Please enter a valid email address', 'error');
            return;
        }
        data = `mailto:${email}?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;
    } else if (type === 'phone') {
        const phone = document.getElementById('phone-number').value;
        if (!phone) {
            showNotification('Please enter a phone number', 'error');
            return;
        }
        data = phone;
    } else if (type === 'sms') {
        const number = document.getElementById('sms-number').value;
        const msg = document.getElementById('sms-message').value;

        if (!number) {
            showNotification('Please enter a recipient number', 'error');
            return;
        }
        data = `smsto:${number}:${msg}`;
    } else if (['facebook', 'instagram', 'twitter', 'linkedin', 'youtube', 'tiktok', 'whatsapp'].includes(type)) {
        data = document.getElementById('social-input').value;

        // Add prefixes if needed
        if (type === 'instagram' && !data.startsWith('http')) data = `https://instagram.com/${data.replace('@', '')}`;
        if (type === 'twitter' && !data.startsWith('http')) data = `https://twitter.com/${data.replace('@', '')}`;
        if (type === 'tiktok' && !data.startsWith('http')) data = `https://tiktok.com/@${data.replace('@', '')}`;
    }

    if (!data) {
        showNotification('Please enter some content for the QR code', 'error');
        return;
    }

    // Validate URL based types
    const urlTypes = ['url', 'facebook', 'linkedin', 'youtube'];
    if (urlTypes.includes(type) && !isValidURL(data)) {
        showNotification('Please enter a valid URL (e.g., https://example.com)', 'error');
        return;
    }

    // Show loading state
    const btn = document.getElementById('generate-btn');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Generating...';
    btn.disabled = true;

    try {
        const formData = new FormData();
        formData.append('data', data);
        formData.append('type', type);
        formData.append('size', 10);
        formData.append('color', color);
        formData.append('bg_color', bgColor);

        const response = await fetch(API_URL, {
            method: 'POST',
            headers: {
                'Accept': 'application/json'
            },
            body: formData
        });

        if (!response.ok) throw new Error('Generation failed');

        const result = await response.json();

        // Update UI
        const img = document.getElementById('qr-image');
        img.src = result.qr_image;
        img.style.display = 'block';
        document.getElementById('qr-placeholder').style.display = 'none';
        showNotification('QR Code generated successfully! 🚀');


    } catch (error) {
        console.error(error);
        showNotification('Failed to generate QR code', 'error');
    } finally {
        btn.innerHTML = originalText;
        btn.disabled = false;
    }
}

function downloadQR() {
    const img = document.getElementById('qr-image');
    if (!img.src) {
        showNotification('No QR Code to download', 'error');
        return;
    }

    const link = document.createElement('a');
    const timestamp = new Date().getTime();

    // Check if it's an SVG
    if (img.src.startsWith('data:image/svg+xml')) {
        link.download = `qrcode-${timestamp}.svg`;
    } else {
        link.download = `qrcode-${timestamp}.png`;
    }

    link.href = img.src;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

async function copyQR() {
    const img = document.getElementById('qr-image');
    if (!img.src) return;

    try {
        // If it's SVG, we need to convert to PNG for clipboard support
        if (img.src.startsWith('data:image/svg+xml')) {
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');

            const image = new Image();
            image.onload = () => {
                // Set canvas size matching the image or a fixed reasonable size for QR
                canvas.width = image.width || 800;
                canvas.height = image.height || 800;

                // Draw white background first (transparent SVG -> black CLI clipboard issue)
                ctx.fillStyle = "#FFFFFF";
                ctx.fillRect(0, 0, canvas.width, canvas.height);

                ctx.drawImage(image, 0, 0, canvas.width, canvas.height);

                canvas.toBlob(async (blob) => {
                    try {
                        if (!blob) throw new Error("Canvas conversion failed");
                        await navigator.clipboard.write([
                            new ClipboardItem({ 'image/png': blob })
                        ]);
                        showNotification('QR Code copied to clipboard!');
                    } catch (e) {
                        console.error('Clipboard write failed', e);
                        showNotification('Failed to copy image', 'error');
                    }
                }, 'image/png');
            };
            image.onerror = (e) => {
                console.error("Image load failed", e);
                showNotification("Failed to process QR image", 'error');
            };
            image.src = img.src;
        } else {
            // Standard PNG copy
            const response = await fetch(img.src);
            const blob = await response.blob();
            await navigator.clipboard.write([
                new ClipboardItem({
                    [blob.type]: blob
                })
            ]);
            showNotification('QR Code copied to clipboard!');
        }
    } catch (err) {
        console.error(err);
        showNotification('Failed to copy', 'error');
    }
}
