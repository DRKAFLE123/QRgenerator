# Free QR Generator

A modern, feature-rich QR code generator with support for multiple QR types, customizable Bio Pages, and a full Admin Dashboard for subscription management. Built with Laravel 11 backend and vanilla JavaScript frontend.

![QR Generator](https://img.shields.io/badge/Laravel-11-red?style=flat-square&logo=laravel)
![License](https://img.shields.io/badge/license-MIT-blue?style=flat-square)

## Features

### QR Code Generation
- **Multiple QR Types**: URL, Text, WiFi, Email, Phone, SMS, and Social Media
- **Social Media Support**: Facebook, Instagram, Twitter, LinkedIn, YouTube, TikTok, WhatsApp
- **Customization**: Custom colors for foreground and background
- **Instant Preview**: Real-time QR code generation and preview
- **Download & Copy**: Export QR codes as PNG/SVG or copy to clipboard

### Admin Dashboard (New!)
- **Statistics Overview**: Centered "Total Bio Pages" count and categorized breakdown (Active, Paid, Expiring, etc.).
- **User Management**: View, filter, search, and delete bio pages.
- **Expiry Management**:
    - **Tracking**: Integrated `expires_at` tracking for all bio pages.
    - **Expiring Soon**: Dedicated filter and warning icons for pages expiring within 7 days.
    - **Renewal System**: One-click renewal for 180 or 365 days.
    - **Custom Expiry**: Interactive calendar to set precise expiry dates.
- **Status Controls**: Toggle bio page status (Active/Inactive) and update payment status (Paid/Unpaid) instantly.
- **Glassmorphism UI**: Premium, modern design with full mobile responsiveness.

### QR Scanner & Tools
- **Image Scanner**: Upload QR code images (PNG, JPG, WEBP) to decode them instantly.
- **Live Scanning**: Uses `jsQR` for fast browser-based QR decoding.
- **Mobile Responsive**: Optimized view for scanning on the go.

### Bio Page QR
- **Multi-Link Pages**: Create a single QR code linking to multiple social profiles
- **Three Themes**: Modern, Vibrant, and Business designs
- **Branding**: Upload custom logo and cover image
- **Responsive Design**: Mobile-friendly Bio Pages
- **Custom Colors**: Personalize theme and background colors

## Tech Stack

### Backend
- **Framework**: Laravel 11
- **Database**: SQLite
- **Image Processing**: PHP GD Library
- **QR Library**: SimpleSoftwareIO/simple-qrcode

### Frontend
- **HTML5/CSS3**: Modern, responsive design (Vanilla CSS)
- **JavaScript**: Vanilla JS (no frameworks)
- **Decoding**: jsQR (for scanning)
- **Icons**: Font Awesome 6.4.0
- **Fonts**: Google Fonts (Inter, Roboto, Outfit)

## Installation

### Prerequisites
- PHP 8.2 or higher (Extensions: gd, mbstring, pdo, sqlite3)
- Composer
- SQLite extension enabled

### Backend Setup
1. **Clone the repository**
   ```bash
   git clone https://github.com/DRKAFLE123/QRgenerator.git
   cd QRgenerator
   ```
2. **Install backend dependencies**
   ```bash
   cd backend
   composer install --optimize-autoloader
   ```
3. **Configure environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
4. **Setup database**
   ```bash
   php artisan migrate --force
   ```
5. **Start the backend server**
   ```bash
   php artisan serve
   ```

### Frontend Setup
1. **Navigate to frontend directory**
   ```bash
   cd ../frontend
   ```
2. **Start a local server**
   Using Python: `python -m http.server 8080`
   Or using PHP: `php -S localhost:8080`

## Deployment (cPanel/Shared Hosting)
1. **Upload** the project files to your server.
2. **PHP Version**: Ensure your host uses PHP 8.2+.
3. **Public Directory**: Set the document root for your backend domain/subdomain to the `backend/public` folder.
4. **Permissions**: Ensure `storage` and `bootstrap/cache` are writable.
5. **Database**: The SQLite database will be located at `backend/database/database.sqlite`. Ensure this file is writable.

## License
This project is open-source and available under the [MIT License](LICENSE).

---
**Made with ❤️ by Dr. Kafle**
