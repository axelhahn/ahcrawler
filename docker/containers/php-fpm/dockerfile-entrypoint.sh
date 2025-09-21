#!/bin/bash
set -e
function _h1(){
    echo
    echo
    echo "---===###|  ahcrawler PHP-FPM  |  $1"
    echo
}

# ----------------------------------------------------------------------
# MAIN
# ----------------------------------------------------------------------


if [ -z "" ]; then
    echo "SKIP: No service script was set as APP_ONSTARTUP"
else
    _h1 "Start service script"
    echo ""
    eval " &"
fi

# ----------------------------------------------------------------------

_h1 "Starting '$*'"
# first arg is `-f` or `--some-option`
if [ "${1#-}" != "$1" ]; then
	set -- php "$@"
fi

exec "$@"

# ----------------------------------------------------------------------
