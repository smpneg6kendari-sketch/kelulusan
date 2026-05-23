<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="description" content="Website Pengumuman Kelulusan SMP Negeri 6 Kendari - Tahun Ajaran 2024/2025">
    <meta name="theme-color" content="#0A84FF">
    <title><?= isset($pageTitle) ? $pageTitle . ' - ' : '' ?>Pengumuman Kelulusan | SMP Negeri 6 Kendari</title>
    <?php
        $shareTitle = (isset($pageTitle) ? $pageTitle . ' - ' : '') . 'Pengumuman Kelulusan | SMP Negeri 6 Kendari';
        $shareDescription = $pageDescription ?? 'Website Pengumuman Kelulusan SMP Negeri 6 Kendari - Tahun Ajaran 2024/2025';
        $shareImage = $pageImage ?? 'assets/images/share-thumbnail.png';
        $forwardedProto = $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '';
        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || strtolower($forwardedProto) === 'https';
        $scheme = $isHttps ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? '';
        $origin = $host !== '' ? $scheme . '://' . $host : '';
        $shareUrl = $origin . ($_SERVER['REQUEST_URI'] ?? '/');
        $shareImageUrl = preg_match('/^https?:\/\//i', $shareImage) ? $shareImage : $origin . '/' . ltrim($shareImage, '/');
    ?>
    <link rel="icon" href="/favicon.ico" type="image/x-icon">
    <link rel="canonical" href="<?= htmlspecialchars($shareUrl, ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="id_ID">
    <meta property="og:title" content="<?= htmlspecialchars($shareTitle, ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:description" content="<?= htmlspecialchars($shareDescription, ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:url" content="<?= htmlspecialchars($shareUrl, ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:image" content="<?= htmlspecialchars($shareImageUrl, ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:image:secure_url" content="<?= htmlspecialchars($shareImageUrl, ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:image:type" content="image/png">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= htmlspecialchars($shareTitle, ENT_QUOTES, 'UTF-8') ?>">
    <meta name="twitter:description" content="<?= htmlspecialchars($shareDescription, ENT_QUOTES, 'UTF-8') ?>">
    <meta name="twitter:image" content="<?= htmlspecialchars($shareImageUrl, ENT_QUOTES, 'UTF-8') ?>">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- TailwindCSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#0A84FF',
                        'primary-dark': '#0066CC',
                        secondary: '#4DA3FF',
                        'bg-main': '#EEF5FF',
                        success: '#22C55E',
                        'success-light': '#DCFCE7',
                        danger: '#FF5A5A',
                        'danger-light': '#FEE2E2',
                        warning: '#FFD84D',
                        'text-dark': '#1E293B',
                        'text-muted': '#64748B',
                    },
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'system-ui', 'sans-serif'],
                    },
                    borderRadius: {
                        '2xl': '1rem',
                        '3xl': '1.5rem',
                        '4xl': '1.875rem',
                    },
                    boxShadow: {
                        'card': '0 4px 24px -2px rgba(10, 132, 255, 0.10)',
                        'card-lg': '0 8px 40px -4px rgba(10, 132, 255, 0.15)',
                        'btn': '0 4px 14px -2px rgba(10, 132, 255, 0.4)',
                        'btn-hover': '0 6px 20px -2px rgba(10, 132, 255, 0.55)',
                        'inner-soft': 'inset 0 2px 4px 0 rgba(0, 0, 0, 0.04)',
                    }
                }
            }
        }
    </script>
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="font-sans bg-bg-main text-text-dark min-h-screen antialiased">
