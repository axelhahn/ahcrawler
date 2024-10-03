<?php
// ---------------------------------------------------------------------------
//
// Curl exitcodes and descriptions
//
// Sources:
// https://curl.se/libcurl/c/libcurl-errors.html
// https://everything.curl.dev/cmdline/exitcode.html
//
// im ahcrawler - Statuscodes
//   - 0
//      - 56 CURLE_RECV_ERROR
//   - 1
//      - 6 CURLE_COULDNT_RESOLVE_HOST
// ---------------------------------------------------------------------------
return [
    '0' => [
        'CURLE_OK',
        'All fine. Proceed as usual.',
        ''
    ],
    '1' => [
        'CURLE_UNSUPPORTED_PROTOCOL',
        'The URL you passed to libcurl used a protocol that this libcurl does not support. The support might be a compile-time option that you did not use, it can be a misspelled protocol string or just a protocol libcurl has no code for.',
        '',
    ],
    '2' => [
        'CURLE_FAILED_INIT',
        'Early initialization code failed. This is likely to be an internal error or problem, or a resource problem where something fundamental could not get done at init time.',
        '',
    ],
    '3' => [
        'CURLE_URL_MALFORMAT',
        'The URL was not properly formatted.',
        '',
    ],
    '4' => [
        'CURLE_NOT_BUILT_IN',
        'A requested feature, protocol or option was not found built-in in this libcurl due to a build-time decision. This means that a feature or option was not enabled or explicitly disabled when libcurl was built and in order to get it to function you have to get a rebuilt libcurl.',
        '',
    ],
    '5' => [
        'CURLE_COULDNT_RESOLVE_PROXY',
        'Could not resolve proxy. The given proxy host could not be resolved.',
        '',
    ],
    '6' => [
        'CURLE_COULDNT_RESOLVE_HOST',
        'Could not resolve host. The given remote host was not resolved.',
        'Could not resolve host. The given remote host\'s address was not resolved. The address of the given server could not be resolved. Either the given hostname is just wrong, or the DNS server is misbehaving and does not know about this name when it should or perhaps even the system you run curl on is misconfigured so that it does not find/use the correct DNS server.',
    ],
    '7' => [
        'CURLE_COULDNT_CONNECT',
        'Failed to connect() to host or proxy.',
        '',
    ],
    '8' => [
        'CURLE_WEIRD_SERVER_REPLY',
        'The server sent data libcurl could not parse. This error code was known as CURLE_FTP_WEIRD_SERVER_REPLY before 7.51.0.',
        '',
    ],
    '9' => [
        'CURLE_REMOTE_ACCESS_DENIED',
        'We were denied access to the resource given in the URL. For FTP, this occurs while trying to change to the remote directory.',
        '',
    ],
    '10' => [
        'CURLE_FTP_ACCEPT_FAILED',
        'While waiting for the server to connect back when an active FTP session is used, an error code was sent over the control connection or similar.',
        '',
    ],
    '11' => [
        'CURLE_FTP_WEIRD_PASS_REPLY',
        'After having sent the FTP password to the server, libcurl expects a proper reply. This error code indicates that an unexpected code was returned.',
        '',
    ],
    '12' => [
        'CURLE_FTP_ACCEPT_TIMEOUT',
        'During an active FTP session while waiting for the server to connect, the CURLOPT_ACCEPTTIMEOUT_MS (or the internal default) timeout expired.',
        '',
    ],
    '13' => [
        'CURLE_FTP_WEIRD_PASV_REPLY',
        'libcurl failed to get a sensible result back from the server as a response to either a PASV or a EPSV command. The server is flawed.',
        '',
    ],
    '14' => [
        'CURLE_FTP_WEIRD_227_FORMAT',
        'FTP servers return a 227-line as a response to a PASV command. If libcurl fails to parse that line, this return code is passed back.',
        '',
    ],
    '15' => [
        'CURLE_FTP_CANT_GET_HOST',
        'An internal failure to lookup the host used for the new connection.',
        '',
    ],
    '16' => [
        'CURLE_HTTP2',
        'A problem was detected in the HTTP2 framing layer. This is somewhat generic and can be one out of several problems, see the error buffer for details.',
        '',
    ],
    '17' => [
        'CURLE_FTP_COULDNT_SET_TYPE',
        'Received an error when trying to set the transfer mode to binary or ASCII.',
        '',
    ],
    '18' => [
        'CURLE_FTP_PARTIAL_FILE',
        'A file transfer was shorter or larger than expected. This happens when the server first reports an expected transfer size larger than the actual size of the file.',
        '',
    ],
    '19' => [
        'CURLE_FTP_COULDNT_RETR_FILE',
        'This was either a weird reply to a "RETR" command or a zero byte transfer complete.',
        '',
    ],
    '20' => [
        'Obsolete error ',
        'Not used in modern versions.',
        '',
    ],
    '21' => [
        'CURLE_QUOTE_ERROR',
        'When sending custom "QUOTE" commands to the remote server, one of the commands returned an error code that was 400 or higher (for FTP) or otherwise indicated unsuccessful completion of the command.',
        '',
    ],
    '22' => [
        'CURLE_HTTP_RETURNED_ERROR',
        'This is returned if CURLOPT_FAILONERROR is set TRUE and the HTTP server returns an error code that is >= 400.',
        '',
    ],
    '23' => [
        'CURLE_WRITE_ERROR',
        'An error occurred when writing received data to a local file, or an error was returned to libcurl from a write callback.',
        '',
    ],
    '24' => [
        'Obsolete error ',
        'Not used in modern versions.',
        '',
    ],
    '25' => [
        'CURLE_UPLOAD_FAILED',
        'Failed starting the upload. For FTP, the server typically denied the STOR command. The error buffer usually contains the server\'s explanation for this.',
        '', 
    ],
    '26' => [
        'CURLE_READ_ERROR',
        'There was a problem reading a local file or an error returned by the read callback.',
        '',
    ],
    '27' => [
        'CURLE_OUT_OF_MEMORY',
        'A memory allocation request failed. This is serious badness and things are severely screwed up if this ever occurs.',
        '',
    ],
    '28' => [
        'CURLE_OPERATION_TIMEDOUT',
        'Operation timeout. The specified time-out period was reached according to the conditions.',
        '',
    ],
    '29' => [
        'Obsolete error',
        'Not used in modern versions.',
        '',
    ],
    '30' => [
        'CURLE_FTP_PORT_FAILED',
        'The FTP PORT command returned error. This mostly happens when you have not specified a good enough address for libcurl to use. See CURLOPT_FTPPORT.',
        '',
    ],

    '31' => [
        'CURLE_FTP_COULDNT_USE_REST',
        'The FTP REST command returned error. This should never happen if the server is sane.',
        '',
    ],
    '32' => [
        'Obsolete error',
        'Not used in modern versions.',
        '',
    ],
    '33' => [
        'CURLE_RANGE_ERROR',
        'The server does not support or accept range requests.',
        '',
    ],
    '34' => [
        'CURLE_HTTP_POST_ERROR',
        'This is an odd error that mainly occurs due to internal confusion.',
        '',
    ],
    '35' => [
        'CURLE_SSL_CONNECT_ERROR',
        'A problem occurred somewhere in the SSL/TLS handshake. You really want the error buffer and read the message there as it pinpoints the problem slightly more. Could be certificates (file formats, paths, permissions), passwords, and others.',
        '',
    ],
    '36' => [
        'CURLE_BAD_DOWNLOAD_RESUME',
        'The download could not be resumed because the specified offset was out of the file boundary.',
        '',
    ],
    '37' => [
        'CURLE_FILE_COULDNT_READ_FILE',
        'A file given with FILE:// could not be opened. Most likely because the file path does not identify an existing file. Did you check file permissions?',
        '',
    ],
    '38' => [
        'CURLE_LDAP_CANNOT_BIND',
        'LDAP cannot bind. LDAP bind operation failed.',
        '',
    ],
    '39' => [
        'CURLE_LDAP_SEARCH_FAILED',
        'LDAP search failed.',
        '',
    ],
    '40' => [
        'Obsolete error',
        'Not used in modern versions.',
        '',
    ],
    '41' => [
        'CURLE_FUNCTION_NOT_FOUND',
        'Function not found. A required zlib function was not found.',
        '',
    ],
    '42' => [
        'CURLE_ABORTED_BY_CALLBACK',
        'Aborted by callback. A callback returned "abort" to libcurl.',
        '',
    ],
    '43' => [
        'CURLE_BAD_FUNCTION_ARGUMENT',
        'Internal error. A function was called with a bad parameter.',
        '',
    ],
    '44' => [
        'Obsolete error',
        'Not used in modern versions.',
        '',
    ],    
    '45' => [
        'CURLE_INTERFACE_FAILED',
        'Interface error. A specified outgoing interface could not be used. Set which interface to use for outgoing connections\' source IP address with CURLOPT_INTERFACE.',
        '',
    ],
    '46' => [
        'Obsolete error',
        'Not used in modern versions.',
        '',
    ],
    '47' => [
        'CURLE_TOO_MANY_REDIRECTS',
        'Too many redirects. When following redirects, libcurl hit the maximum amount. Set your limit with CURLOPT_MAXREDIRS.',
        '',
    ],
    '48' => [
        'CURLE_UNKNOWN_OPTION',
        'An option passed to libcurl is not recognized/known. Refer to the appropriate documentation. This is most likely a problem in the program that uses libcurl. The error buffer might contain more specific information about which exact option it concerns.',
        '',
    ],
    '49' => [
        'CURLE_SETOPT_OPTION_SYNTAX',
        'An option passed in to a setopt was wrongly formatted. See error message for details about what option.',
        '',
    ],
    '50' => [
        'Obsolete error',
        'Not used in modern versions.',
        '',
    ],
    '51' => [
        'Obsolete error',
        'Not used in modern versions.',
        '',
    ],
    '52' => [
        'CURLE_GOT_NOTHING',
        'Nothing was returned from the server, and under the circumstances, getting nothing is considered an error.',
        '',
    ],
    '53' => [
        'CURLE_SSL_ENGINE_NOTFOUND',
        'The specified crypto engine was not found.',
        '',
    ],
    '54' => [
        'CURLE_SSL_ENGINE_SETFAILED',
        'Failed setting the selected SSL crypto engine as default.',
        '',
    ],
    '55' => [
        'CURLE_SEND_ERROR',
        'Failed sending network data.',
        '',
    ],
    '56' => [
        'CURLE_RECV_ERROR',
        'Failure with receiving network data.',
        'Failure in receiving network data. Receiving data over the network is a crucial part of most curl operations and when curl gets an error from the lowest networking layers that the receiving of data failed, this exit status gets returned. To pinpoint why this happens, some serious digging is usually required. Start with enabling verbose mode, do tracing and if possible check the network traffic with a tool like Wireshark or similar.',
    ],
    '57' => [
        'Obsolete error',
        'Not used in modern versions.',
        '',
    ],
    '58' => [
        'CURLE_SSL_CERTPROBLEM',
        'problem with the local client certificate.',
        '',
    ],
    '59' => [
        'CURLE_SSL_CIPHER',
        'Could not use specified cipher.',
        '',
    ],
    '60' => [
        'CURLE_PEER_FAILED_VERIFICATION',
        'The remote server\'s SSL certificate or SSH fingerprint was deemed not OK. This error code has been unified with CURLE_SSL_CACERT since 7.62.0. Its previous value was 51.',
        '',
    ],
    '61' => [
        'CURLE_BAD_CONTENT_ENCODING',
        'Unrecognized transfer encoding.',
        '',
    ],
    '62' => [
        'Obsolete error',
        'Not used in modern versions.',
        '',
    ],
    '63' => [
        'CURLE_FILESIZE_EXCEEDED',
        'Maximum file size exceeded.',
        '',
    ],
    '64' => [
        'CURLE_USE_SSL_FAILED',
        'Requested FTP SSL level failed.',
        '',
    ],
    '65' => [
        'CURLE_SEND_FAIL_REWIND',
        'When doing a send operation curl had to rewind the data to retransmit, but the rewinding operation failed.',
        '',
    ],
    '66' => [
        'CURLE_SSL_ENGINE_INITFAILED',
        'Initiating the SSL Engine failed.',
        '',
    ],
    '67' => [
        'CURLE_LOGIN_DENIED',
        'The remote server denied curl to login (Added in 7.13.1)',
        '',
    ],
    '68' => [
        'CURLE_TFTP_NOTFOUND',
        'File not found on TFTP server.',
        '',
    ],
    '69' => [
        'CURLE_TFTP_PERM',
        'Permission problem on TFTP server.',
        '',
    ]
];

/*

    CURLE_REMOTE_DISK_FULL (70)
    
    Out of disk space on the server.
    
    CURLE_TFTP_ILLEGAL (71)
    
    Illegal TFTP operation.
    
    CURLE_TFTP_UNKNOWNID (72)
    
    Unknown TFTP transfer ID.
    
    CURLE_REMOTE_FILE_EXISTS (73)
    
    File already exists and is not overwritten.
    
    CURLE_TFTP_NOSUCHUSER (74)
    
    This error should never be returned by a properly functioning TFTP server.
    
    Obsolete error (75-76)
    
    Not used in modern versions.
    
    CURLE_SSL_CACERT_BADFILE (77)
    
    Problem with reading the SSL CA cert (path? access rights?)
    
    CURLE_REMOTE_FILE_NOT_FOUND (78)
    
    The resource referenced in the URL does not exist.
    
    CURLE_SSH (79)
    
    An unspecified error occurred during the SSH session.
    
    CURLE_SSL_SHUTDOWN_FAILED (80)
    
    Failed to shut down the SSL connection.
    
    CURLE_AGAIN (81)
    
    Socket is not ready for send/recv. Wait until it is ready and try again. This return code is only returned from curl_easy_recv and curl_easy_send (Added in 7.18.2)
    
    CURLE_SSL_CRL_BADFILE (82)
    
    Failed to load CRL file (Added in 7.19.0)
    
    CURLE_SSL_ISSUER_ERROR (83)
    
    Issuer check failed (Added in 7.19.0)
    
    CURLE_FTP_PRET_FAILED (84)
    
    The FTP server does not understand the PRET command at all or does not support the given argument. Be careful when using CURLOPT_CUSTOMREQUEST, a custom LIST command is sent with the PRET command before PASV as well. (Added in 7.20.0)
    
    CURLE_RTSP_CSEQ_ERROR (85)
    
    Mismatch of RTSP CSeq numbers.
    
    CURLE_RTSP_SESSION_ERROR (86)
    
    Mismatch of RTSP Session Identifiers.
    
    CURLE_FTP_BAD_FILE_LIST (87)
    
    Unable to parse FTP file list (during FTP wildcard downloading).
    
    CURLE_CHUNK_FAILED (88)
    
    Chunk callback reported error.
    
    CURLE_NO_CONNECTION_AVAILABLE (89)
    
    (For internal use only, is never returned by libcurl) No connection available, the session is queued. (added in 7.30.0)
    
    CURLE_SSL_PINNEDPUBKEYNOTMATCH (90)
    
    Failed to match the pinned key specified with CURLOPT_PINNEDPUBLICKEY.
    
    CURLE_SSL_INVALIDCERTSTATUS (91)
    
    Status returned failure when asked with CURLOPT_SSL_VERIFYSTATUS.
    
    CURLE_HTTP2_STREAM (92)
    
    Stream error in the HTTP/2 framing layer.
    
    CURLE_RECURSIVE_API_CALL (93)
    
    An API function was called from inside a callback.
    
    CURLE_AUTH_ERROR (94)
    
    An authentication function returned an error.
    
    CURLE_HTTP3 (95)
    
    A problem was detected in the HTTP/3 layer. This is somewhat generic and can be one out of several problems, see the error buffer for details.
    
    CURLE_QUIC_CONNECT_ERROR (96)
    
    QUIC connection error. This error may be caused by an SSL library error. QUIC is the protocol used for HTTP/3 transfers.
    
    CURLE_PROXY (97)
    
    Proxy handshake error. CURLINFO_PROXY_ERROR provides extra details on the specific problem.
    
    CURLE_SSL_CLIENTCERT (98)
    
    SSL Client Certificate required.
    
    CURLE_UNRECOVERABLE_POLL (99)
    
    An internal call to poll() or select() returned error that is not recoverable.
    
    CURLE_TOO_LARGE (100)
    
    A value or data field grew larger than allowed.
    
    CURLE_ECH_REQUIRED (101)
    
    ECH was attempted but failed. 
*/