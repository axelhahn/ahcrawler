#!/bin/bash
# ======================================================================
#
# DOCKER PHP DEV ENVIRONMENT :: INIT
#
# ----------------------------------------------------------------------
# 2021-11-nn  v1.0  <axel.hahn@iml.unibe.ch>
# 2022-07-19  v1.1  <axel.hahn@iml.unibe.ch>  support multiple dirs for setfacl
# 2022-11-16  v1.2  <www.axel-hahn.de>        use docker-compose -p "$APP_NAME"
# 2022-12-18  v1.3  <www.axel-hahn.de>        add -p "$APP_NAME" in other docker commands
# 2022-12-20  v1.4  <axel.hahn@unibe.ch>      replace fgrep with grep -F
# 2023-03-06  v1.5  <www.axel-hahn.de>        up with and without --build
# 2023-08-17  v1.6  <www.axel-hahn.de>        menu selection with single key (without return)
# 2023-11-10  v1.7  <axel.hahn@unibe.ch>      replace docker-compose with "docker compose"
# 2023-11-13  v1.8  <axel.hahn@unibe.ch>      UNDO "docker compose"; update infos
# 2023-11-15  v1.9  <axel.hahn@unibe.ch>      add help; execute multiple actions by params; new menu item: open app
# 2023-12-07  v1.10 <www.axel-hahn.de>        simplyfy console command; add php linter
# 2024-07-01  v1.11 <www.axel-hahn.de>        diff with colored output; suppress errors on port check
# 2024-07-19  v1.12 <axel.hahn@unibe.ch>      apply shell fixes
# 2024-07-22  v1.13 <axel.hahn@unibe.ch>      show info if there is no database container; speedup replacements
# 2024-07-22  v1.14 <axel.hahn@unibe.ch>      show colored boxes with container status
# 2024-07-24  v1.15 <axel.hahn@unibe.ch>      update menu output
# 2024-07-26  v1.16 <axel.hahn@unibe.ch>      hide unnecessary menu items (WIP)
# 2024-07-29  v1.17 <www.axel-hahn.de>        hide unnecessary menu items; reorder functions
# 2024-08-14  v1.18 <www.axel-hahn.de>        update container view
# 2024-09-20  v1.19 <www.axel-hahn.de>        detect dockerd-rootless (hides menu item to set permissions)
# 2024-10-16  v1.20 <axel.hahn@unibe.ch>      add db import and export
# 2024-10-25  v1.21 <axel.hahn@unibe.ch>      create missing subdir dbdumps
# ======================================================================

cd "$( dirname "$0" )" || exit 1

# init used vars
gittarget=
frontendurl=

_self=$( basename "$0" )

# shellcheck source=/dev/null
. "${_self}.cfg" || exit 1

_version="1.21"

# git@git-repo.iml.unibe.ch:iml-open-source/docker-php-starterkit.git
selfgitrepo="docker-php-starterkit.git"

fgGray="\e[1;30m"
fgRed="\e[31m"
fgGreen="\e[32m"
fgBrown="\e[33m"
fgBlue="\e[34m"

fgInvert="\e[7m"
fgReset="\e[0m"

# ----- status varsiables
# running containers
DC_WEB_UP=0
DC_DB_UP=0

# repo of docker-php-starterkit is here?
DC_REPO=1

DC_CONFIG_CHANGED=0

DC_WEB_URL=""

DC_DUMP_DIR=dbdumps

isDockerRootless=0
ps -ef | grep  dockerd-rootless | grep -q $USER && isDockerRootless=1

# ----------------------------------------------------------------------
# FUNCTIONS
# ----------------------------------------------------------------------

# ----------------------------------------------------------------------
# STATUS FUNCTIONS

# get container status and set global variable DC_REPO
# DC_REPO = 0 nothing to do - repo was changed to project
# DC_REPO = 1 if repo is in selfgitrepo (must be deleted)
function _getStatus_repo(){
    DC_REPO=0
    git config --get remote.origin.url 2>/dev/null | grep -q $selfgitrepo && DC_REPO=1
}

# check if any of the templates has a change that must be applied
function _getStatus_template(){
    _generateFiles "dryrun"
}

# get container status and set global variables
# DC_WEB_UP - web container 
# DC_DB_UP  - database container
#   0 = down
#   1 = up
function _getStatus_docker(){
    local _out
    _out=$( docker-compose -p "$APP_NAME" ps)    

    DC_WEB_UP=0
    DC_DB_UP=0
    grep -q "${APP_NAME}-server" <<< "$_out" && DC_WEB_UP=1
    grep -q "${APP_NAME}-db"     <<< "$_out"  && DC_DB_UP=1

    if [ "$DB_ADD" != "false" ] && [ ! -d "${DC_DUMP_DIR}" ]; then
        echo "INFO: creating subdir ${DC_DUMP_DIR} to import/ export databases ..."
        mkdir "${DC_DUMP_DIR}" || exit 1
    return
fi

}

function _getWebUrl(){
    DC_WEB_URL="$frontendurl"
    grep -q "${APP_NAME}-server" /etc/hosts && DC_WEB_URL="https://${APP_NAME}-server/"
}

# ----------------------------------------------------------------------
# OUTPUT

# draw a headline 2
function h2(){
    echo
    echo -e "$fgBrown>>>>> $*$fgReset"
}

# draw a headline 3
function h3(){
    echo
    echo -e "$fgBlue----- $*$fgReset"
}

# helper for menu: print an inverted key
function  _key(){
    echo -en "\e[4;7m ${1} \e[0m"
}

# show menu in interactive mode and list keys in help with param -h
# param  string  optional: set to "all" to show all menu items
function showMenu(){

    local _bAll=0
    test -n "$1" && _bAll=1

    local _spacer="    "

    echo
    if [ $DC_REPO -eq 1 ] || [ $_bAll -eq 1 ]; then
        echo "${_spacer}$( _key g ) - remove git data of starterkit"
        echo
    fi

    if [ $isDockerRootless -eq 1 ] || [ $_bAll -eq 1 ]; then
        echo "${_spacer}$( _key i ) - init application: set permissions"
    fi

    if [ $DC_CONFIG_CHANGED -eq 1 ] || [ $_bAll -eq 1 ]; then
        echo "${_spacer}$( _key t ) - generate files from templates"
    fi
    if [ $DC_CONFIG_CHANGED -eq 0 ] || [ $_bAll -eq 1 ]; then
        echo "${_spacer}$( _key T ) - remove generated files"
    fi
    echo
    if [ $DC_WEB_UP -eq 0 ] || [ $DC_DB_UP -eq 0 ] || [ $_bAll -eq 1 ]; then
        if [ $DC_CONFIG_CHANGED -eq 0 ] || [ $_bAll -eq 1 ]; then
            echo "${_spacer}$( _key u ) - startup containers    docker-compose ... up -d"
            echo "${_spacer}$( _key U ) - startup containers    docker-compose ... up -d --build"
            echo
            echo "${_spacer}$( _key r ) - remove containers     docker-compose rm -f"
        fi
    fi
    if [ $DC_WEB_UP -eq 1 ] || [ $DC_DB_UP -eq 1 ] || [ $_bAll -eq 1 ]; then
        echo "${_spacer}$( _key s ) - shutdown containers   docker-compose stop"
        echo
        echo "${_spacer}$( _key m ) - more infos"
        echo "${_spacer}$( _key o ) - open app [${APP_NAME}] $DC_WEB_URL"
        echo "${_spacer}$( _key c ) - console (bash)"
    fi
    if [ $DC_WEB_UP -eq 1 ] || [ $_bAll -eq 1 ]; then
        echo "${_spacer}$( _key p ) - console check with php linter"
    fi
    if [ $DC_DB_UP -eq 1 ] || [ $_bAll -eq 1 ]; then
        echo "${_spacer}$( _key d ) - Dump container database"
        echo "${_spacer}$( _key D ) - Import Dump into container database"
    fi
    echo
    echo "${_spacer}$( _key q ) - quit"

}
function showHelp(){
    cat <<EOH

INITIALIZER FOR DOCKER APP v$_version

A helper script written in Bash to bring up a PHP+Mysql application in docker.

📄 Source : https://git-repo.iml.unibe.ch/iml-open-source/docker-php-starterkit
📗 Docs   : https://os-docs.iml.unibe.ch/docker-php-starterkit/
📜 License: GNU GPL 3.0
(c) Institute for Medical Education; University of Bern


SYNTAX:
  $_self [-h|-v]
  $_self [menu key [.. menu key N]]

OPTIONS:
  -h   show this help and exit
  -v   show version exit

MENU KEYS:
  In the interactive menu are some keys to init an action.
  The same keys can be put as parameter to start this action.
  You can add multiples keys to apply multiple actions.

$( showMenu "all" )

EXAMPLES:

  $_self           starts interactive mode
  $_self u         bring up docker container(s) and stay in interactive mode
  $_self i q       set write permissions and quit
  $_self p q       start php linter and exit

EOH
}


# show urls for app container
function _showBrowserurl(){
    echo "In a web browser open:"
    echo "  $DC_WEB_URL"
}

# detect + show ports and urls for app container and db container
function _showInfos(){
    _showContainers long
    h2 INFO

    h3 "processes webserver"
    # docker-compose top
    docker top "${APP_NAME}-server"
    if [ ! "$DB_ADD" = "false" ]; then
        h3 "processes database"
        docker top "${APP_NAME}-db"
    fi

    h3 "What to open in browser"
    if echo >"/dev/tcp/localhost/${APP_PORT}"; then
        # echo "OK, app port ${APP_PORT} is reachable"
        # echo
        _showBrowserurl
    else
        echo "ERROR: app port ${APP_PORT} is not available"
    fi 2>/dev/null

    if [ "$DB_ADD" != "false" ]; then
        h3 "Check database port"
        if echo >"/dev/tcp/localhost/${DB_PORT}"; then
            echo "OK, db port ${DB_PORT} is reachable"
            echo
            echo "In a local DB admin tool you can connect it:"
            echo "  host    : localhost"
            echo "  port    : ${DB_PORT}"
            echo "  user    : root"
            echo "  password: ${MYSQL_ROOT_PASS}"
        else
            echo "NO, db port ${DB_PORT} is not available"
        fi 2>/dev/null

    fi
    echo
}

# ----------------------------------------------------------------------
# ACTIONS

# set acl on local directory
function _setWritepermissions(){
    h2 "set write permissions on ${gittarget} ..."

    local _user; _user=$( id -gn )
    local _user_uid; typeset -i _user_uid=0

    test -f /etc/subuid && _user_uid=$( grep "$_user" /etc/subuid 2>/dev/null | cut -f 2 -d ':' )-1
    local DOCKER_USER_OUTSIDE; typeset -i DOCKER_USER_OUTSIDE=$_user_uid+$DOCKER_USER_UID

    set -vx

    for mywritedir in ${WRITABLEDIR}
    do 

        echo "--- ${mywritedir}"
        # remove current acl
        sudo setfacl -bR "${mywritedir}"

        # default permissions: both the host user and the user with UID 33 (www-data on many systems) are owners with rwx perms
        sudo setfacl -dRm "u:${DOCKER_USER_OUTSIDE}:rwx,${_user}:rwx" "${mywritedir}"

        # permissions: make both the host user and the user with UID 33 owner with rwx perms for all existing files/directories
        sudo setfacl -Rm "u:${DOCKER_USER_OUTSIDE}:rwx,${_user}:rwx" "${mywritedir}"
    done

    set +vx
}

# cleanup starterkit git data
function _removeGitdata(){
    h2 "Remove git data of starterkit"
    echo -n "Current git remote url: "
    git config --get remote.origin.url
    if git config --get remote.origin.url 2>/dev/null | grep -q $selfgitrepo; then
        echo
        echo -n "Delete local .git and .gitignore? [y/N] > "
        read -r answer
        test "$answer" = "y" && ( echo "Deleting ... " && rm -rf ../.git ../.gitignore )
    else
        echo "It was done already - $selfgitrepo was not found."
    fi

}

# helper function: cut a text file starting from database start marker
# see _generateFiles()
function _fix_no-db(){
    local _file=$1
    if [ "$DB_ADD" = "false" ]; then
        local iStart; typeset -i iStart
        iStart=$( grep -Fn "$CUTTER_NO_DATABASE" "${_file}" | cut -f 1 -d ':' )-1
        if [ $iStart -gt 0 ]; then
            sed -ni "1,${iStart}p" "${_file}"
        fi
    fi
}

# helper function to generate replacements using sed
# it loops over all vars in the config file
# used in _generateFiles
function _getreplaces(){
    # loop over vars to make the replacement
    grep "^[a-zA-Z]" "$_self.cfg" | while read -r line
    do
        # echo replacement: $line
        mykey=$( echo "$line" | cut -f 1 -d '=' )
        myvalue="$( eval echo \"\$"$mykey"\" )"

        # TODO: multiline values fail here in replacement with sed 
        echo -e "s#{{$mykey}}#${myvalue}#g"

    done
}

# loop over all files in templates subdir make replacements and generate
# a target file.
# It skips if 
#   - 1st line is not starting with "# TARGET: filename"
#   - target file has no updated lines
# If the 1st parameter is set to "dryrun" it will not generate files.
# param string dryrun optional: set to "dryrun" to not generate files
function _generateFiles(){

    local _dryrun="$1"
    DC_CONFIG_CHANGED=0

    # shellcheck source=/dev/null
    . "${_self}.cfg" || exit 1    

    params=$( _getreplaces | while read -r line; do echo -n "-e '$line' ";  done )

    local _tmpfile=/tmp/newfilecontent$$.tmp
    
    test "$_dryrun" = "dryrun" || h2 "generate files from templates..."
    for mytpl in templates/*
    do
        # h3 $mytpl
        local _doReplace=1

        # fetch traget file from first line
        target=$( head -1 "$mytpl" | grep "^# TARGET:" | cut -f 2- -d ":" | awk '{ print $1 }' )

        if [ -z "$target" ]; then
            if [ "$_dryrun" != "dryrun" ]; then
                echo "SKIP: $mytpl - target was not found in 1st line"
            fi
            _doReplace=0
        fi

        # write generated files to target
        if [ $_doReplace -eq 1 ]; then

            # write file from line 2 to a tmp file
            sed -n '2,$p' "$mytpl" >"$_tmpfile"

            # add generator
            # sed -i "s#{{generator}}#generated by $0 - template: $mytpl - $( date )#g" $_tmpfile
            local _md5; _md5=$( md5sum $_tmpfile | awk '{ print $1 }' )
            sed -i "s#{{generator}}#GENERATED BY $_self - template: $mytpl - $_md5#g" $_tmpfile

            # apply all replacements to the tmp file
            eval sed -i "$params" "$_tmpfile" || exit

            _fix_no-db $_tmpfile

            # echo "changes for $target:"
            if diff --color=always "../$target"  "$_tmpfile" 2>/dev/null | grep -v "$_md5" | grep -v "^---" | grep . || [ ! -f "../$target" ]; then
                if [ "$_dryrun" = "dryrun" ]
                then
                    DC_CONFIG_CHANGED=1
                else
                    echo -n "$mytpl - changes detected - writing [$target] ... "
                    mkdir -p "$( dirname  ../"$target" )" || exit 2
                    mv "$_tmpfile" "../$target" || exit 2
                    echo OK
                    echo
                fi
            else
                rm -f $_tmpfile
                if [ "$_dryrun" != "dryrun" ]; then
                    echo "SKIP: $mytpl - Nothing to do."
                fi
            fi
        fi
    done

}

# loop over all files in templates subdir make replacements and generate
# a traget file.
function _removeGeneratedFiles(){
    h2 "remove generated files..."
    for mytpl in templates/*
    do
        h3 "$mytpl"

        # fetch traget file from first line
        target=$( head -1 "$mytpl" | grep "^# TARGET:" | cut -f 2- -d ":" | awk '{ print $1 }' )

        if [ -n "$target" ] && [ -f "../$target" ]; then
            echo -n "REMOVING "
            ls -l "../$target" || exit 2
            rm -f "../$target" || exit 2
            echo OK
        else
            echo "SKIP: $target"
        fi
        
    done
}


# show running containers
function _showContainers(){
    local bLong=$1

    local _out

    local sUp=".. UP"
    local sDown=".. down"

    local Status=
    local StatusWeb="$sDown"
    local StatusDb="$sDown"
    local colWeb=
    local colDb=

    colDb="$fgRed"
    colWeb="$fgRed"

    if [ $DC_WEB_UP -eq 1 ]; then
        colWeb="$fgGreen"
        StatusWeb="$sUp"
    fi
    
    if [ $DC_DB_UP -eq 1 ]; then
        colDb="$fgGreen"
        StatusDb="$sUp"
    fi

    if [ "$DB_ADD" = "false" ]; then
        colDb="$fgGray"
        local StatusDb=".. N/A"
        Status="This app has no database container."
    fi

    h2 CONTAINERS

    echo
    printf "  $colWeb$fgInvert  %-32s  $fgReset   $colDb$fgInvert  %-32s  $fgReset\n"      "WEB ${StatusWeb}"  "DB ${StatusDb}"
    printf "    %-32s  $fgReset     %-32s  $fgReset\n"      "PHP ${APP_PHP_VERSION}"      "${MYSQL_IMAGE}"
    printf "    %-32s  $fgReset     %-32s  $fgReset\n"      ":${APP_PORT}"                ":${DB_PORT}"

    echo

    if [ -n "$Status" ]; then
        echo "  $Status"
        echo
    fi

    if [ -n "$bLong" ]; then
        echo "$_out"

        h2 STATS
        docker stats --no-stream
        echo
    fi

}

# helper: wait for a return key
function _wait(){
    local _wait=15
    echo -n "... press RETURN ... or wait $_wait sec > "; read -r -t $_wait
    echo
}

# DB TOOL - dump db from container
function _dbDump(){
    local _iKeepDumps;
    typeset -i _iKeepDumps=5
    local _iStart;
    typeset -i _iStart=$_iKeepDumps+1;

    if [ $DC_DB_UP -eq 0 ]; then
        echo "Database container is not running. Aborting."
        return
    fi
    outfile=${DC_DUMP_DIR}/${MYSQL_DB}_$( date +%Y%m%d_%H%M%S ).sql
    echo -n "dumping ${MYSQL_DB} ... "
    if docker exec -i "${APP_NAME}-db" mysqldump -uroot -p${MYSQL_ROOT_PASS} ${MYSQL_DB} > "$outfile"; then
        echo -n "OK ... Gzip ... "
        if gzip "${outfile}"; then
            echo "OK"
            ls -l "$outfile.gz"

            # CLEANUP
            echo
            echo "--- Cleanup: keep $_iKeepDumps files."
            ls -1t ${DC_DUMP_DIR}/* | sed -n "$_iStart,\$p" | while read -r delfile
            do 
                echo "CLEANUP: Deleting $delfile ... "
                rm -f "$delfile"
            done
            echo
            echo -n "Size of dump directory: "
            du -hs ${DC_DUMP_DIR} | awk '{ print $1 }'

        else
            echo "ERROR"
            rm -f "$outfile"
        fi
    else
        echo "ERROR"
        rm -f "$outfile"
    fi
}

# DB TOOL - import local database dump into container
function _dbImport(){
    echo "--- Available dumps:"
    ls -ltr ${DC_DUMP_DIR}/*.gz | sed "s#^#    #g"
    if [ $DC_DB_UP -eq 0 ]; then
        echo "Database container is not running. Aborting."
        return
    fi
    echo -n "Dump file to import into ${MYSQL_DB} > "
    read -r dumpfile
    if [ -z "$dumpfile" ]; then
        echo "Abort - no value was given."
        return
    fi
    if [ ! -f "$dumpfile" ]; then
        echo "Abort - wrong filename."
        return
    fi

    echo -n "Importing $dumpfile ... "
    if zcat "$dumpfile" | docker exec -i "${APP_NAME}-db" mysql -uroot -p${MYSQL_ROOT_PASS} "${MYSQL_DB}"
    then
        echo "OK"
    else
        echo "ERROR"
    fi
}

# ----------------------------------------------------------------------
# MAIN
# ----------------------------------------------------------------------

action=$1; shift 1

while true; do

    _getStatus_repo
    _getStatus_docker
    _getStatus_template
    _getWebUrl

    if [ -z "$action" ]; then

        echo "_______________________________________________________________________________"
        echo
        printf "  %-70s ______\n" "${APP_NAME^^}  ::  Initializer for docker"
        echo "________________________________________________________________________/ $_version"
        echo

        _showContainers

        h2 MENU       
        showMenu
        echo
        echo -n "  select >"
        read -rn 1 action 
        echo
    fi

    case "$action" in
        "-h") showHelp; exit 0 ;;
        "-v") echo "$_self $_version"; exit 0 ;;
        g)
            _removeGitdata
            ;;
        i)
            # _gitinstall
            _setWritepermissions
            ;;
        t)
            _generateFiles
            ;;
        T)
            _removeGeneratedFiles
            rm -rf containers
            ;;
        m)
            _showInfos
            _wait
            ;;
        u|U)
            h2 "Bring up..."
            dockerUp="docker-compose -p $APP_NAME --verbose up -d --remove-orphans"
            if [ "$action" = "U" ]; then
                dockerUp+=" --build"
            fi
            echo "$dockerUp"
            if $dockerUp; then
                _showBrowserurl
            else
                echo "ERROR: docker-compose up failed :-/"
                docker-compose -p "$APP_NAME" logs | tail
            fi
            echo

            ;;
        s)
            h2 "Stopping..."
            docker-compose -p "$APP_NAME" stop
            ;;
        r)
            h2 "Removing..."
            docker-compose -p "$APP_NAME" rm -f
            ;;
        c)
            h2 "Console"
            _containers=$( docker-compose -p "$APP_NAME" ps | sed -n "2,\$p" | awk '{ print $1}' )
            if [ "$DB_ADD" = "false" ]; then
                dockerid=$_containers
            else
                echo "Select a container:"
                sed "s#^#    #g" <<< "$_containers"
                echo -n "id or name >"
                read -r dockerid
            fi
            test -z "$dockerid" || (
                echo
                echo "> docker exec -it $dockerid /bin/bash     (type 'exit' + Return when finished)"
                docker exec -it "$dockerid" /bin/bash
            )
            ;;
        p)
            h2 "PHP $APP_PHP_VERSION linter"

            dockerid="${APP_NAME}-server"
            echo -n "Scanning ... "
            typeset -i _iFiles
            _iFiles=$( docker exec -it "$dockerid" /bin/bash -c "find . -name '*.php' " | wc -l )

            if [ $_iFiles -gt 0 ]; then
                echo "found $_iFiles [*.php] files ... errors from PHP $APP_PHP_VERSION linter:"
                time if echo "$APP_PHP_VERSION" | grep -E "([567]\.|8\.[012])" >/dev/null ; then
                    docker exec -it "$dockerid" /bin/bash -c "find . -name '*.php' -exec php -l {} \; | grep -v '^No syntax errors detected'"
                else
                    docker exec -it "$dockerid" /bin/bash -c "php -l \$( find . -name '*.php' ) | grep -v '^No syntax errors detected' "
                fi
                echo
                _wait
            else
                echo "Start your docker container first."
            fi
            ;;
        d) 
            h2 "DB tools :: dump"
            _dbDump
            ;;
        D) 
            h2 "DB tools :: import"
            _dbImport
            ;;
        o) 
            h2 "Open app ..."
            xdg-open "$DC_WEB_URL"
            ;;
        q)
            h2 "Bye!"
            exit 0;
            ;;
        *) 
            test -n "$action" && ( echo "  ACTION FOR [$action] NOT IMPLEMENTED."; sleep 1 )
    esac
    action=$1; shift 1
done


# ----------------------------------------------------------------------
