# Free QR Generator

A modern, feature-rich QR code generator with support for multiple QR types and customizable Bio Pages. Built with Laravel 11 backend and vanilla JavaScript frontend.

![QR Generator](https://img.shields.io/badge/Laravel-11-red?style=flat-square&logo=laravel)
![License](https://img.shields.io/badge/license-MIT-blue?style=flat-square)

## Features

### QR Code Generation
- **Multiple QR Types**: URL, Text, WiFi, Email, Phone, SMS, and Social Media
- **Social Media Support**: Facebook, Instagram, Twitter, LinkedIn, YouTube, TikTok, WhatsApp
- **Customization**: Custom colors for foreground and background
- **Instant Preview**: Real-time QR code generation and preview
- **Download & Copy**: Export QR codes as PNG/SVG or copy to clipboard

### Bio Page QR
- **Multi-Link Pages**: Create a single QR code linking to multiple social profiles
- **Three Themes**: Modern, Vibrant, and Business designs
- **Branding**: Upload custom logo (10MB max) and cover image (10MB max)
- **Responsive Design**: Mobile-friendly Bio Pages
- **Custom Colors**: Personalize theme and background colors

## Tech Stack

### Backend
- **Framework**: Laravel 11
- **Database**: SQLite
- **Image Processing**: PHP GD Library
- **QR Library**: SimpleSoftwareIO/simple-qrcode

### Frontend
- **HTML5/CSS3**: Modern, responsive design
- **JavaScript**: Vanilla JS (no frameworks)
- **Icons**: Font Awesome 6.4.0
- **Fonts**: Google Fonts (Inter, Roboto, Outfit)

## Installation

### Prerequisites
- PHP 8.2 or higher
- Composer
- Node.js & npm (optional, for frontend development)
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
   composer install
   ```

3. **Configure environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Setup database**
   ```bash
   php artisan migrate
   ```

5. **Create storage symlink**
   ```bash
   php artisan storage:link
   ```

6. **Start the backend server**
   ```bash
   php artisan serve
   ```
   Backend will run on `http://localhost:8000`

### Frontend Setup

1. **Navigate to frontend directory**
   ```bash
   cd ../frontend
   ```

2. **Start a local server**
   
   Using Python:
   ```bash
   python -m http.server 8080
   ```
   
   Or using PHP:
   ```bash
   php -S localhost:8080
   ```
   
   Frontend will run on `http://localhost:8080`

## Usage

### Single QR Code Generation

1. Open `http://localhost:8080` in your browser
2. Select the **Single Link QR** tab
3. Choose your QR type from the dropdown
4. Fill in the required information
5. Customize colors if desired
6. Click **Generate QR Code**
7. Download or copy the generated QR code

### Bio Page Creation

1. Switch to the **Bio Page QR** tab
2. Enter your organization/name
3. Add website URL (optional)
4. Select a theme (Modern, Vibrant, or Business)
5. Upload logo and cover image (max 10MB each)
6. Add social media links using the **Add Link** button
7. Customize theme colors
8. Click **Create Bio Page**
9. Scan the generated QR code to view your Bio Page

## API Endpoints

### Generate QR Code
```http
POST /api/generate-qr
Content-Type: multipart/form-data

Parameters:
- data: string (required) - Content to encode
- type: string (required) - QR type
- size: integer (default: 10) - QR code size
- color: string (default: #000000) - Foreground color
- bg_color: string (default: #FFFFFF) - Background color
```

### Create Bio Page
```http
POST /api/create-bio
Content-Type: multipart/form-data

Parameters:
- name: string (required) - Organization/person name
- links: string (required) - JSON array of social links
- theme: string (modern|vibrant|business)
- color: string - Theme color
- bg_color: string - Background color
- logo: file (max 10MB) - Logo image
- cover: file (max 10MB) - Cover image
- website: string - Website URL
```

### View Bio Page
```http
GET /bio/{id}

Returns: HTML page with the Bio Page design
```

## File Upload Limits

- **Logo**: Maximum 10MB
- **Cover Image**: Maximum 10MB
- **Supported Formats**: JPG, PNG, GIF, SVG, WEBP

The frontend validates file sizes before upload and displays helpful error messages.

## Project Structure

```
QRgenerator/
├── backend/                 # Laravel application
│   ├── app/
│   │   ├── Http/
│   │   │   └── Controllers/
│   │   │       ├── QrController.php
│   │   │       └── BioPageController.php
│   ├── database/
│   │   └── migrations/
│   ├── resources/
│   │   └── views/           # Bio Page templates
│   │       ├── bio-page-modern.blade.php
│   │       ├── bio-page-vibrant.blade.php
│   │       └── bio-page-business.blade.php
│   ├── routes/
│   │   └── web.php
│   └── storage/
│       └── app/
│           └── public/
│               └── uploads/ # Uploaded logos and covers
├── frontend/                # Static frontend
│   ├── index.html
│   ├── style.css
│   └── script.js
└── README.md
```

## Configuration

### CORS Settings
The backend is configured to accept requests from `http://localhost:8080`. To change this:

1. Edit `backend/config/cors.php`
2. Update the `allowed_origins` array
3. Run `php artisan config:clear`

### Database
The application uses SQLite by default. To switch to MySQL/PostgreSQL:

1. Update `backend/.env` with your database credentials
2. Run migrations: `php artisan migrate`

## Development

### Adding New QR Types
1. Add the type to the frontend dropdown in `frontend/index.html`
2. Create the form fields for the new type
3. Update `frontend/script.js` to handle the new type in `generateQR()`
4. Update `backend/app/Http/Controllers/QrController.php` if needed

### Creating New Bio Page Themes
1. Create a new Blade template in `backend/resources/views/`
2. Follow the naming convention: `bio-page-{theme}.blade.php`
3. Add the theme option to `frontend/index.html`
4. Update the controller's theme validation in `BioPageController.php`

## Troubleshooting

### CORS Errors
- Ensure the backend is running on `http://localhost:8000`
- Check that `config/cors.php` includes your frontend URL
- Clear config cache: `php artisan config:clear`

### File Upload Fails
- Check PHP upload limits in `php.ini`:
  - `upload_max_filesize = 10M`
  - `post_max_size = 40M`
- Verify storage directory permissions
- Ensure `storage:link` has been run

### QR Code Not Generating
- Check browser console for errors
- Verify backend API is accessible
- Ensure all required fields are filled

## License

This project is open-source and available under the [MIT License](LICENSE).

## Credits

- **QR Code Library**: [SimpleSoftwareIO/simple-qrcode](https://github.com/SimpleSoftwareIO/simple-qrcode)
- **Icons**: [Font Awesome](https://fontawesome.com/)
- **Fonts**: [Google Fonts](https://fonts.google.com/)

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## Support

For issues, questions, or suggestions, please open an issue on GitHub.

---

**Made with ❤️ by Dr. Kafle**
