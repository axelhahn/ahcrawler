# TARGET: docker/containers/proxy-server/dockerfile-entrypoint.sh
#!/bin/bash

function _h1(){

    echo
    echo
    echo "---===###|  {{APP_NAME}} PROXY  |  $1"
    echo

}
# ensure that ssl key and cert exist
# param string hostname (from config file)
function _createSslCert(){
    local myhost="$1"
    local certdir=/etc/ssl/private

    local keyfile=$certdir/$myhost.key
    local certfile=$certdir/$myhost.crt

    test -d "$certdir" || mkdir "$certdir"  ||  exit 2
    if test -f "${certfile}"; then
        echo "SKIP: cert already exists: ${certfile}"
    else
        echo "INFO: Creating cert ..."
        set -vx
        openssl req -nodes -x509 -newkey rsa:4096 \
            -keyout "${keyfile}" \
            -out "${certfile}" \
            -days 3650 \
            -subj "/CN=${myhost}" \
            -addext "subjectAltName=DNS:${myhost},DNS:localhost,DNS:proxy,IP:127.0.0.1"
        set +vx
    fi

    ls -l "${keyfile}" "${certfile}" || exit 3

}

# ----------------------------------------------------------------------
# MAIN
# ----------------------------------------------------------------------

_h1 "Redirect Apache httpd logs to docker log"
rm -f /var/log/apache2/*log
ln -s /dev/stdout /var/log/apache2/access.log
ln -s /dev/stdout /var/log/apache2/other_vhosts_access.log
ln -s /dev/stderr /var/log/apache2/error.log
ls -l /var/log/apache2/*.log

_h1 "SSL certificate"
_createSslCert "{{APP_NAME}}"

# ----------------------------------------------------------------------

_h1 "Starting '$*'"
exec "$@"

# ----------------------------------------------------------------------
