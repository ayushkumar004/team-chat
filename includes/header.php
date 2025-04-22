<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TeamChat - Real-time Team Communication</title>
    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom Tailwind Config -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#f0f4fe',
                            100: '#d9e2fd',
                            200: '#bccbfc',
                            300: '#92a9f9',
                            400: '#647df4',
                            500: '#4f5bed',
                            600: '#3a3ee0',
                            700: '#3132c0',
                            800: '#2c2d9b',
                            900: '#282a7a',
                            950: '#1a1b4b',
                        },
                    }
                }
            }
        }
    </script>
    <!-- Custom styles -->
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body class="bg-gray-50 min-h-screen">