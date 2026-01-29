<?php

return [
    'website' => [
        'label' => 'Website',
        'icon' => 'fa-solid fa-globe',
        'url_prefix' => '',
        'placeholder' => 'https://yoursite.com',
    ],
    'facebook' => [
        'label' => 'Facebook',
        'icon' => 'fa-brands fa-facebook',
        'url_prefix' => '',
        'placeholder' => 'https://facebook.com/your-page',
    ],
    'instagram' => [
        'label' => 'Instagram',
        'icon' => 'fa-brands fa-instagram',
        'url_prefix' => '',
        'placeholder' => 'https://instagram.com/your-handle',
    ],
    'twitter' => [
        'label' => 'Twitter',
        'icon' => 'fa-brands fa-twitter',
        'url_prefix' => '',
        'placeholder' => 'https://twitter.com/your-handle',
    ],
    'linkedin' => [
        'label' => 'LinkedIn',
        'icon' => 'fa-brands fa-linkedin',
        'url_prefix' => '',
        'placeholder' => 'https://linkedin.com/in/your-profile',
    ],
    'youtube' => [
        'label' => 'YouTube',
        'icon' => 'fa-brands fa-youtube',
        'url_prefix' => '',
        'placeholder' => 'https://youtube.com/c/your-channel',
    ],
    'tiktok' => [
        'label' => 'TikTok',
        'icon' => 'fa-brands fa-tiktok',
        'url_prefix' => '',
        'placeholder' => 'https://tiktok.com/@your-handle',
    ],
    'whatsapp' => [
        'label' => 'WhatsApp',
        'icon' => 'fa-brands fa-whatsapp',
        'url_prefix' => 'https://wa.me/',
        'placeholder' => 'Phone number (e.g., 15551234567)',
        'type' => 'whatsapp' // Special handling identifier
    ],
    'google_reviews' => [
        'label' => 'Google Reviews',
        'icon' => 'fa-brands fa-google',
        'url_prefix' => '',
        'placeholder' => 'https://g.page/r/...',
    ],
    'yelp' => [
        'label' => 'Yelp',
        'icon' => 'fa-brands fa-yelp',
        'url_prefix' => '',
        'placeholder' => 'https://yelp.com/biz/...',
    ],
    'phone' => [
        'label' => 'Phone',
        'icon' => 'fa-solid fa-phone',
        'url_prefix' => 'tel:',
        'placeholder' => '+1234567890',
        'type' => 'phone'
    ],
    'sms' => [
        'label' => 'SMS',
        'icon' => 'fa-solid fa-comment-sms',
        'url_prefix' => 'sms:',
        'placeholder' => '+1234567890',
        'type' => 'sms'
    ],
    'text' => [
        'label' => 'Plain Text',
        'icon' => 'fa-solid fa-align-left', // Fallback/Generic
        'url_prefix' => '',
        'placeholder' => 'Enter text to display',
        'type' => 'text'
    ],
];
