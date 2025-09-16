<?php 
header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self'; img-src 'self' data:;");
header("Strict-Transport-Security: max-age=31536000; includeSubDomains; preload");
header("Cross-Origin-Opener-Policy: same-origin");
header("X-Frame-Options: SAMEORIGIN");
?>