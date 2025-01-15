<?php
// ---------------------------------------------------------------------------
//
// Http header definitions
// This file read by httpheader.class.php
//
// Keys:
//   'client' => {bool}
//   'response' => {bool}
//   'fetch-Meta' => {bool}
//   'tags' => {array}
//    'unwantedregex' => {string} regex for unwanted values (eg. version numbers)
//
// ---------------------------------------------------------------------------
return [
    // en: https://en.wikipedia.org/wiki/List_of_HTTP_header_fields
    // de: https://de.wikipedia.org/wiki/Liste_der_HTTP-Headerfelder
    // ... plus https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers
    'http' => [
        '_status' => ['response' => true],
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
        'Alt-Used' => [],
        'Attribution-Reporting-Eligible' => ['tags' => ['experimantal']],
        'Attribution-Reporting-Register-Source' => ['tags' => ['experimantal']],
        'Attribution-Reporting-Register-Trigger' => ['tags' => ['experimantal']],
        'Authentication-Info' => [],
        'Authorization' => [],
        'Cache-Control' => ['tags' => ['cache']],
        'C-Ext' => [],
        'Clear-Site-Data' => [],
        'C-Man' => [],
        'Connection' => ['response' => true],
        'Content-Base' => [],
        'Content-Digest' => [],
        'Content-Disposition' => ['response' => true],
        'Content-DPR' => ['tags' => ['deprecated', 'obsolete']],
        'Content-Encoding' => ['response' => true, 'tags' => ['compression']],
        'Content-ID' => [],
        'Content-Language' => ['response' => true],
        'Content-Length' => ['response' => true],
        'Content-Location' => ['response' => true],
        'Content-MD5' => [],
        'Content-Range' => ['response' => true],
        'Content-Script-Type' => [],
        'Content-Security-Policy' => ['response' => true, 'tags' => ['security'], 'badvalueregex' => 'unsafe\-'],
        'Content-Security-Policy-Report-Only' => [],
        'Content-Style-Type' => [],
        'Content-Type' => [],
        'Content-Version' => [],
        'Cookie' => [],
        'Cookie2' => ['obsolete' => true],
        'C-Opt' => [],
        'C-PEP-Info' => [],
        'Critical-CH' => ['tags' => ['experimantal']],
        'Cross-Origin-Embedder-Policy' => [],
        'Cross-Origin-Opener-Policy' => [],
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
        'DNT' => ['tags' => ['deprecated', 'non-standard']],
        'Downlink' => ['tags' => ['experimantal']],
        'DPR' => ['tags' => ['deprecated', 'non-standard']],
        'Early-Data' => ['tags' => ['experimantal']],
        'ECT' => ['tags' => ['experimantal']],
        'ETag' => ['response' => true, 'tags' => ['cache']],
        'Expect' => ['response' => true],
        'Expect-CT' => ['response' => true, 'tags' => ['deprecated']],
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
        'NEL' => ['tags' => ['experimantal']],
        'No-Vary-Search' => ['tags' => ['experimantal']],
        'Observe-Browsing-Topics' => ['tags' => ['experimantal', 'non-standard']],
        'Opt' => [],
        'Ordering-Type' => [],
        'Origin' => [],
        'Origin-Agent-Cluster' => ['tags' => ['experimantal']],
        'Overwrite' => [],
        'P3P' => [],
        'PEP' => [],
        'Pep-Info' => [],
        'Permissions-Policy' => ['tags' => ['experimantal']],
        'PICS-Label' => [],
        'Position' => [],
        'Pragma' => ['response' => true, 'tags' => ['cache', 'deprecated']],
        'Priority' => [],
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
        'Referrer-Policy' => ['response' => true, 'tags' => ['security']],
        'Refresh' => ['response' => true],
        'Reporting-Endpoints' => ['response' => true, 'tags' => ['experimantal']],
        'Repr-Digest' => ['request' => true, 'response' => true],
        'Retry-After' => ['response' => true],
        'RTT' => ['request' => true, 'tags' => ['experimantal']],
        'Safe' => [],
        'Save-Data' => ['request' => true, 'tags' => ['experimantal']],
        'Sec-Browsing-Topics' => ['request' => true, 'tags' => ['experimantal', 'non-standard']],
        'Sec-CH-Prefers-Color-Scheme' => ['request' => true, 'tags' => ['experimantal']],
        'Sec-CH-Prefers-Reduced-Motion' => ['request' => true, 'tags' => ['experimantal']],
        'Sec-CH-UA' => ['request' => true, 'tags' => ['experimantal']],
        'Sec-CH-UA-Arch' => ['request' => true, 'tags' => ['experimantal']],
        'Sec-CH-UA-Bitness' => ['request' => true, 'tags' => ['experimantal']],
        'Sec-CH-UA-Full-Version' => ['request' => true, 'tags' => ['deprecated']],
        'Sec-CH-UA-Full-Version-List' => ['request' => true, 'tags' => ['experimantal']],
        'Sec-CH-UA-Mobile' => ['request' => true, 'tags' => ['experimantal']],
        'Sec-CH-UA-Model' => ['request' => true, 'tags' => ['experimantal']],
        'Sec-CH-UA-Platform' => ['request' => true, 'tags' => ['experimantal']],
        'Sec-CH-UA-Platform-Version' => ['request' => true, 'tags' => ['experimantal']],
        'Sec-Fetch-Dest' => ['fetch-meta' => true, ],
        'Sec-Fetch-Mode' => ['fetch-meta' => true, ],
        'Sec-Fetch-Site' => ['fetch-meta' => true, ],
        'Sec-Fetch-User' => ['fetch-meta' => true, ],
        'Sec-GPC' => ['fetch-meta' => true, 'tags' => ['experimantal']],
        'Sec-Purpose' => ['fetch-meta' => true, ],
        'Sec-WebSocket-Accept' => ['response' => true, ],
        'Sec-WebSocket-Extensions' => ['request' => true, 'response' => true, ],
        'Sec-WebSocket-Key' => ['request' => true],
        'Sec-WebSocket-Protocol' => ['request' => true, 'response' => true, ],
        'Sec-WebSocket-Version' => ['response' => true, ],
        'Security-Scheme' => [],
        'Server' => ['response' => true, 'unwantedregex' => '[0-9]*\.[0-9\.]*'],
        'Server-Timing' => ['response' => true],
        'Service-Worker' => ['request' => true],
        'Service-Worker-Allowed' => ['response' => true],
        'Service-Worker-Navigation-Preload' => ['request' => true],
        'Set-Cookie' => ['response' => true],
        'Set-Login' => ['response' => true, 'tags' => ['experimantal']],
        'SetProfile' => [],
        'SoapAction' => [],
        'SourceMap' => ['response' => true],
        'Speculation-Rules' => ['response' => true, 'tags' => ['experimantal']],
        'Status' => ['response' => true, 'tags' => ['non-standard']],
        'Status-URI' => [],
        'Strict-Transport-Security' => ['response' => true, 'tags' => ['security']],
        'Supports-Loading-Mode' => ['response' => true, 'tags' => ['experimantal']],
        'Surrogate-Capability' => [],
        'Surrogate-Control' => [],
        'TCN' => [],
        'TE' => ['request' => true],
        'Timeout' => [],
        'Timing-Allow-Origin' => ['response' => true, 'tags' => ['non-standard']],
        'Tk' => ['response' => true, 'tags' => ['deprecated', 'non-standard']],
        'Trailer' => ['request' => true, 'response' => true],
        'Transfer-Encoding' => ['request' => true, 'response' => true],
        'Upgrade' => ['request' => true, 'response' => true],
        'Upgrade-Insecure-Requests' => ['request' => true],
        'URI' => [],
        'User-Agent' => ['request' => true],
        'Variant-Vary' => [],
        'Vary' => ['response' => true, 'tags'=>['experimental']],
        'Via' => ['request' => true,  'response' => true],
        'Viewport-Width' => ['request' => true, 'tags' => ['deprecated', 'non-standard']],
        'Want-Digest' => ['response' => true],
        'Warning' => ['request' => true, 'response' => true, 'tags' => ['deprecated', 'non-standard']],
        'Width' => ['request' => true, 'tags' => ['deprecated', 'non-standard']],
        'WWW-Authenticate' => ['response' => true],

        'X-Content-Duration' => ['response' => true, 'tags' => ['non-standard']],
        'X-Content-Security-Policy' => ['response' => true, 'tags' => ['non-standard']],
        'X-Content-Type-Options' => ['response' => true, 'tags' => ['security']],
        'X-Correlation-ID' => ['response' => true, 'tags' => ['non-standard']],
        'X-DNS-Prefetch-Control' => ['response' => true, 'tags' => ['non-standard']],
        'X-Forwarded-For' => ['request' => true, 'tags' => ['security-risk']],
        'X-Forwarded-Host' => ['request' => true,],
        'X-Forwarded-Proto' => ['request' => true,],
        'X-Frame-Options' => ['response' => true, 'tags' => ['deprecated'], 'badvalueregex' => 'ALLOW-FROM'],
        'X-Permitted-Cross-Domain-Policies' => ['response' => true, 'tags' => ['security']],
        'X-Pingback' => ['response' => true, 'tags' => ['non-standard']], // http://www.hixie.ch/specs/pingback/pingback#TOC2.1
        'X-Powered-By' => ['response' => true, 'tags' => ['non-standard', 'unwanted'], 'unwantedregex' => '[0-9]*\.[0-9\.]*'],
        'X-Request-ID' => ['response' => true, 'tags' => ['non-standard']],
        'X-Robots-Tag' => ['response' => true, 'tags' => ['deprecated', 'non-standard']],

        'X-XSS-Protection' => ['response' => true, 'tags' => ['security']],

        'X-UA-Compatible' => ['response' => true, 'tags' => ['non-standard']],
        'X-WebKit-CSP' => ['response' => true, 'tags' => ['non-standard']],
    ],
];  