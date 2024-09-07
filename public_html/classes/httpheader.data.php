<?php
// ---------------------------------------------------------------------------
//
// Http header definitions
// This file read by httpheader.class.php
//
// ---------------------------------------------------------------------------
return [
    // en: https://en.wikipedia.org/wiki/List_of_HTTP_header_fields
    // de: https://de.wikipedia.org/wiki/Liste_der_HTTP-Headerfelder
    // ... plus https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers
    'http' => [
        '_status' => [],
        'Accept' => ['client' => true],
        'Accept-Additions' => [],
        'Accept-CH' => ['client' => true],
        'Accept-Charset' => ['client' => true],
        'Accept-CH-Lifetime' => ['client' => true],
        'Accept-Encoding' => ['client' => true],
        'Accept-Features' => [],
        'Accept-Language' => ['client' => true],
        'Accept-Patch' => ['response' => true],
        'Accept-Ranges' => ['response' => true],
        'Access-Control-Allow-Credentials' => ['response' => true],
        'Access-Control-Allow-Headers' => ['response' => true],
        'Access-Control-Allow-Methods' => ['response' => true],
        'Access-Control-Allow-Origin' => ['response' => true],
        'Access-Control-Expose-Headers' => ['response' => true],
        'Access-Control-Max-Age' => ['response' => true],
        'Access-Control-Request-Headers' => [],
        'Access-Control-Request-Method' => [],
        'Age' => ['tags' => ['cache']],
        'A-IM' => [],
        'Allow' => [],
        'Alternates' => [],
        'Alt-Svc' => [],
        'Authentication-Info' => [],
        'Authorization' => [],
        'Cache-Control' => ['tags' => ['cache']],
        'C-Ext' => [],
        'Clear-Site-Data' => [],
        'C-Man' => [],
        'Connection' => ['response' => true],
        'Content-Base' => [],
        'Content-Disposition' => ['response' => true],
        'Content-Encoding' => ['response' => true, 'tags' => ['compression']],
        'Content-ID' => [],
        'Content-Language' => ['response' => true],
        'Content-Length' => ['response' => true],
        'Content-Location' => ['response' => true],
        'Content-MD5' => [],
        'Content-Range' => ['response' => true],
        'Content-Script-Type' => [],
        'Content-Security-Policy' => [],
        'Content-Security-Policy-Report-Only' => [],
        'Content-Style-Type' => [],
        'Content-Type' => [],
        'Content-Version' => [],
        'Cookie' => [],
        'Cookie2' => ['obsolete' => true],
        'C-Opt' => [],
        'C-PEP-Info' => [],
        'Cross-Origin-Resource-Policy' => [],
        'Date' => [],
        'DAV' => [],
        'Default-Style' => [],
        'Delta-Base' => [],
        'Depth' => [],
        'Derived-From' => [],
        'Destination' => [],
        'Device-Memory' => [],
        'Differential-ID' => [],
        'Digest' => ['response' => true],
        'DNT' => [],
        'DPR' => [],
        'Early-Data' => [],
        'ETag' => ['response' => true, 'tags' => ['cache']],
        'Expect' => ['response' => true],
        'Expect-CT' => [],
        'Expires' => ['response' => true, 'tags' => ['cache']],
        'Ext' => [],
        'Feature-Policy' => [
            'response' => true,
            'tags' => ['feature', 'security'],
            "directives" => [
                "accelerometer",
                "ambient-light-sensor",
                "autoplay",
                "battery",
                "camera",
                "display-capture",
                "document-domain",
                "encrypted-media",
                "fullscreen",
                "geolocation",
                "gyroscope",
                "layout-animations",
                "legacy-image-formats",
                "magnetometer",
                "microphone",
                "midi",
                "oversized-images",
                "payment",
                "picture-in-picture",
                "publickey-credentials-get",
                "sync-xhr",
                "unoptimized-images",
                "unsized-media",
                "usb",
                "vibrate",
                "vr",
                "wake-lock",
                "xr",
                "xr-spatial-tracking"
            ]
        ],
        'Forwarded' => [],
        'From' => ['response' => true],
        'GetProfile' => [],
        'Host' => ['response' => true],
        'If' => [],
        'If-Match' => ['response' => true],
        'If-Modified-Since' => ['response' => true],
        'If-None-Match' => ['response' => true],
        'If-Range' => ['response' => true],
        'If-Unmodified-Since' => ['response' => true],
        'IM' => [],
        'Index' => [],
        'Keep-Alive' => ['response' => true],
        'Label' => [],
        'Large-Allocation' => [],
        'Last-Modified' => ['response' => true],
        'Link' => ['response' => true],
        'Location' => ['response' => true],
        'Lock-Token' => [],
        'Man' => [],
        'Max-Forwards' => [],
        'Meter' => [],
        'MIME-Version' => [],
        'Negotiate' => [],
        'NEL' => [],
        'Opt' => [],
        'Ordering-Type' => [],
        'Origin' => [],
        'Overwrite' => [],
        'P3P' => [],
        'PEP' => [],
        'Pep-Info' => [],
        'PICS-Label' => [],
        'Position' => [],
        'Pragma' => ['response' => true, 'tags' => ['cache', 'deprecated']],
        'ProfileObject' => [],
        'Protocol' => [],
        'Protocol-Info' => [],
        'Protocol-Query' => [],
        'Protocol-Request' => [],
        'Proxy-Authenticate' => ['response' => true],
        'Proxy-Authentication-Info' => [],
        'Proxy-Authorization' => ['response' => true],
        'Proxy-Features' => [],
        'Proxy-Instruction' => [],
        'Public' => [],
        'Public-Key-Pins' => ['tags' => ['deprecated', 'obsolete']],
        'Public-Key-Pins-Report-Only' => ['tags' => ['deprecated', 'obsolete']],
        'Range' => ['response' => true],
        'Referer' => ['response' => true],
        'Referrer-Policy' => [],
        'Retry-After' => ['response' => true],
        'Safe' => [],
        'Save-Data' => [],
        'Sec-Fetch-Dest' => [],
        'Sec-Fetch-Mode' => [],
        'Sec-Fetch-Site' => [],
        'Sec-Fetch-User' => [],
        'Security-Scheme' => [],
        'Sec-WebSocket-Accept' => [],
        'Server' => ['response' => true, 'unwantedregex' => '[0-9]*\.[0-9\.]*'],
        'Server-Timing' => [],
        'Set-Cookie' => ['response' => true],
        'Set-Cookie2' => ['obsolete' => true, 'response' => true],
        'SetProfile' => [],
        'SoapAction' => [],
        'SourceMap' => [],
        'Status-URI' => [],
        'Strict-Transport-Security' => [],
        'Surrogate-Capability' => [],
        'Surrogate-Control' => [],
        'TCN' => [],
        'TE' => ['response' => true],
        'Timeout' => [],
        'Tk' => [],
        'Trailer' => ['response' => true],
        'Transfer-Encoding' => ['response' => true],
        'Upgrade' => [],
        'Upgrade-Insecure-Requests' => [],
        'URI' => [],
        'User-Agent' => ['response' => true],
        'Variant-Vary' => [],
        'Vary' => ['response' => true],
        'Via' => ['response' => true],
        'Want-Digest' => ['response' => true],
        'Warning' => ['response' => true],
        'WWW-Authenticate' => ['response' => true],
        'X-Content-Type-Options' => [],
        'X-DNS-Prefetch-Control' => [],
        'X-Frame-Options' => [],
        'X-XSS-Protection' => [],
        // ],
        // see  https://en.wikipedia.org/wiki/List_of_HTTP_header_fields#Common_non-standard_response_fields
        // 'non-standard' => [
        'Refresh' => ['response' => true, 'tags' => ['non-standard']],
        'Status' => ['response' => true, 'tags' => ['non-standard']],
        'Timing-Allow-Origin' => ['response' => true, 'tags' => ['non-standard']],
        'X-Content-Duration' => ['response' => true, 'tags' => ['non-standard']],
        'X-Content-Security-Policy' => ['response' => true, 'tags' => ['non-standard']],
        'X-Correlation-ID' => ['response' => true, 'tags' => ['non-standard']],
        'X-Forwarded-For' => ['response' => true, 'tags' => ['non-standard']],
        'X-Forwarded-Host' => ['response' => true, 'tags' => ['non-standard']],
        'X-Forwarded-Proto' => ['response' => true, 'tags' => ['non-standard']],
        'X-Pingback' => ['response' => true, 'tags' => ['non-standard']], // http://www.hixie.ch/specs/pingback/pingback#TOC2.1
        'X-Powered-By' => ['response' => true, 'tags' => ['non-standard', 'unwanted'], 'unwantedregex' => '[0-9]*\.[0-9\.]*'],
        'X-Request-ID' => ['response' => true, 'tags' => ['non-standard']],
        'X-Robots-Tag' => ['response' => true, 'tags' => ['non-standard']],
        'X-UA-Compatible' => ['response' => true, 'tags' => ['non-standard']],
        'X-WebKit-CSP' => ['response' => true, 'tags' => ['non-standard']],
        // ],
        // see https://www.owasp.org/index.php/OWASP_Secure_Headers_Project#tab=Headers
        // 'security' => [
        'Content-Security-Policy' => ['response' => true, 'tags' => ['security'], 'badvalueregex' => 'unsafe\-'],
        'Expect-CT' => ['response' => true, 'tags' => ['security']],
        'Feature-Policy' => ['response' => true, 'tags' => ['feature', 'security']],
        'Public-Key-Pins' => ['response' => true, 'tags' => ['security', 'deprecated']],
        'Referrer-Policy' => ['response' => true, 'tags' => ['security']],
        'Strict-Transport-Security' => ['response' => true, 'tags' => ['security']],
        'X-Content-Type-Options' => ['response' => true, 'tags' => ['security']],
        'X-Frame-Options' => ['response' => true, 'tags' => ['security'], 'badvalueregex' => 'ALLOW-FROM'],
        'X-Permitted-Cross-Domain-Policies' => ['response' => true, 'tags' => ['security']],
        'X-XSS-Protection' => ['response' => true, 'tags' => ['security']],
    ],
];  