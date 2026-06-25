<!-- Favicon -->
<link rel="icon" type="image/png" href="{{ asset('images/chariot-logo.png') }}">
<link rel="apple-touch-icon" href="{{ asset('images/chariot-logo.png') }}">

<!-- Open Graph / Facebook -->
<meta property="og:type" content="website">
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:title" content="@yield('title', 'Chariot') — RCCG Camp Chariot">
<meta property="og:description" content="Chariot Ride Booking Service — RCCG Camp Ground. Find rides, share seats, move together.">
<meta property="og:image" content="{{ asset('images/chariot-logo.png') }}">
<meta property="og:image:type" content="image/png">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">

<!-- Twitter -->
<meta property="twitter:card" content="summary_large_image">
<meta property="twitter:url" content="{{ url()->current() }}">
<meta property="twitter:title" content="@yield('title', 'Chariot') — RCCG Camp Chariot">
<meta property="twitter:description" content="Chariot Ride Booking Service — RCCG Camp Ground. Find rides, share seats, move together.">
<meta property="twitter:image" content="{{ asset('images/chariot-logo.png') }}">
