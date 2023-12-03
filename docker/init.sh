#!/bin/bash
# ======================================================================
#
# DOCKER PHP DEV ENVIRONMENT :: INIT
#
# ----------------------------------------------------------------------
# 2021-11-nn  v1.0 <axel.hahn@iml.unibe.ch>
# 2022-07-19  v1.1 <axel.hahn@iml.unibe.ch>  support multiple dirs for setfacl
# 2022-11-16  v1.2 <www.axel-hahn.de>        use docker-compose -p "$APP_NAME"
# 2022-12-18  v1.3 <www.axel-hahn.de>        add -p "$APP_NAME" in other docker commands
# 2022-12-20  v1.4 <axel.hahn@unibe.ch>      replace fgrep with grep -F
# 2023-03-06  v1.5 <www.axel-hahn.de>        up with and without --build
# 2023-08-17  v1.6 <www.axel-hahn.de>        menu selection with single key (without return)
# 2023-11-10  v1.7 <axel.hahn@unibe.ch>      replace docker-compose with "docker compose"
# 2023-11-13  v1.8 <axel.hahn@unibe.ch>      UNDO "docker compose"; update infos
# 2023-11-15  v1.9 <axel.hahn@unibe.ch>      add help; execute multiple actions by params; new menu item: open app
# ======================================================================

cd $( dirname $0 )
. $( basename $0 ).cfg

# git@git-repo.iml.unibe.ch:iml-open-source/docker-php-starterkit.git
selfgitrepo="docker-php-starterkit.git"

_version="1.9"

# ----------------------------------------------------------------------
# FUNCTIONS
# ----------------------------------------------------------------------

# draw a headline 2
function h2(){
    echo
    echo -e "\e[33m>>>>> $*\e[0m"
}

# draw a headline 3
function h3(){
    echo
    echo -e "\e[34m----- $*\e[0m"
}

# show help for param -h
function showMenu(){
    echo "  $( _key g ) - remove git data of starterkit"
    echo
    echo "  $( _key i ) - init application: set permissions"
    echo "  $( _key t ) - generate files from templates"
    echo "  $( _key T ) - remove generated files"
    echo
    echo "  $( _key u ) - startup containers    docker-compose ... up -d"
    echo "  $( _key U ) - startup containers    docker-compose ... up -d --build"
    echo "  $( _key s ) - shutdown containers   docker-compose stop"
    echo "  $( _key r ) - remove containers     docker-compose rm -f"
    echo
    echo "  $( _key m ) - more infos"
    echo "  $( _key o ) - open app [${APP_NAME}] $frontendurl"
    echo "  $( _key c ) - console (bash)"
    echo
    echo "  $( _key q ) - quit"
}
function showHelp(){
    local _self=$( basename "$0" )
    cat <<EOH
INITIALIZER FOR DOCKER APP v$_version

A helper script written in Bash to bring up a PHP+Mysql application in docker.

Source : https://git-repo.iml.unibe.ch/iml-open-source/docker-php-starterkit
Docs   : https://os-docs.iml.unibe.ch/docker-php-starterkit/
License: GNU GPL 3.0
(c) Institute for Medical Education; University of Bern


SYNTAX:
  $_self [-h|-v]
  $_self [menu key]

OPTIONS:
  -h   show this help and exit
  -v   show version exit

MENU KEYS:
  In the interactive menu are some keys to init an action.
  The same keys can be put as parameter to start this action.
  You can add multiples keys to apply multiple actions.

$( showMenu )

EXAMPLES:

  $_self           starts interactive mode
  $_self u         bring up docker container(s) and stay in interactive mode
  $_self i q       set write permissions and quit

EOH
}
# function _gitinstall(){
#     h2 "install/ update app from git repo ${gitrepo} in ${gittarget} ..."
#     test -d ${gittarget} && ( cd ${gittarget}  && git pull )
#     test -d ${gittarget} || git clone -b ${gitbranch} ${gitrepo} ${gittarget} 
# }

# set acl on local directory
function _setWritepermissions(){
    h2 "set write permissions on ${gittarget} ..."

    local _user=$( id -gn )
    typeset -i local _user_uid=0
    test -f /etc/subuid && _user_uid=$( grep $_user /etc/subuid 2>/dev/null | cut -f 2 -d ':' )-1
    typeset -i local DOCKER_USER_OUTSIDE=$_user_uid+$DOCKER_USER_UID

    set -vx

    for mywritedir in ${WRITABLEDIR}
    do 

        echo "--- ${mywritedir}"
        # remove current acl
        sudo setfacl -bR "${mywritedir}"

        # default permissions: both the host user and the user with UID 33 (www-data on many systems) are owners with rwx perms
        sudo setfacl -dRm u:${DOCKER_USER_OUTSIDE}:rwx,${_user}:rwx "${mywritedir}"

        # permissions: make both the host user and the user with UID 33 owner with rwx perms for all existing files/directories
        sudo setfacl -Rm u:${DOCKER_USER_OUTSIDE}:rwx,${_user}:rwx "${mywritedir}"
    done

    set +vx
}

# cleanup starterkit git data
function _removeGitdata(){
    h2 "Remove git data of starterkit"
    echo -n "Current git remote url: "
    git config --get remote.origin.url
    git config --get remote.origin.url 2>/dev/null | grep $selfgitrepo >/dev/null
    if [ $? -eq 0 ]; then
        echo
        echo -n "Delete local .git and .gitignore? [y/N] > "
        read answer
        test "$answer" = "y" && ( echo "Deleting ... " && rm -rf ../.git ../.gitignore )
    else
        echo "It was done already - $selfgitrepo was not found."
    fi

}

# helper function: cut a text file starting from database start marker
# see _generateFiles()
function _fix_no-db(){
    local _file=$1
    if [ $DB_ADD = false ]; then
        typeset -i local iStart=$( cat ${_file} | grep -Fn "$CUTTER_NO_DATABASE" | cut -f 1 -d ':' )-1
        if [ $iStart -gt 0 ]; then
            sed -ni "1,${iStart}p" ${_file}
        fi
    fi
}

# loop over all files in templates subdir make replacements and generate
# a target file.
# It skips if 
#   - 1st line is not starting with "# TARGET: filename"
#   - target file has no updated lines
function _generateFiles(){

    # re-read config vars
    . $( basename $0 ).cfg

    local _tmpfile=/tmp/newfilecontent$$.tmp
    h2 "generate files from templates..."
    for mytpl in $( ls -1 ./templates/* )
    do
        # h3 $mytpl
        local _doReplace=1

        # fetch traget file from first line
        target=$( head -1 $mytpl | grep "^# TARGET:" | cut -f 2- -d ":" | awk '{ print $1 }' )

        if [ -z "$target" ]; then
            echo SKIP: $mytpl - target was not found in 1st line
            _doReplace=0
        fi

        # write generated files to target
        if [ $_doReplace -eq 1 ]; then

            # write file from line 2 to a tmp file
            sed -n '2,$p' $mytpl >$_tmpfile

            # add generator
            # sed -i "s#{{generator}}#generated by $0 - template: $mytpl - $( date )#g" $_tmpfile
            local _md5=$( md5sum $_tmpfile | awk '{ print $1 }' )
            sed -i "s#{{generator}}#GENERATED BY $( basename $0 ) - template: $mytpl - $_md5#g" $_tmpfile

            # loop over vars to make the replacement
            grep "^[a-zA-Z]" $( basename $0 ).cfg | while read line
            do
                # echo replacement: $line
                mykey=$( echo $line | cut -f 1 -d '=' )
                myvalue="$( eval echo \"\${$mykey}\" )"
                # grep "{{$mykey}}" $_tmpfile

                # TODO: multiline values fail here in replacement with sed 
                sed -i "s#{{$mykey}}#${myvalue}#g" $_tmpfile
            done
            _fix_no-db $_tmpfile

            # echo "changes for $target:"
            diff  "../$target"  "$_tmpfile" | grep -v "$_md5" | grep -v "^---" | grep .
            if [ $? -eq 0 -o ! -f "../$target" ]; then
                echo -n "$mytpl - changes detected - writing [$target] ... "
                mkdir -p $( dirname  "../$target" ) || exit 2
                mv "$_tmpfile" "../$target" || exit 2
                echo OK
            else
                rm -f $_tmpfile
                echo "SKIP: $mytpl - Nothing to do."
            fi
        fi
        echo
    done

}

# loop over all files in templates subdir make replacements and generate
# a traget file.
function _removeGeneratedFiles(){
    h2 "remove generated files..."
    for mytpl in $( ls -1 ./templates/* )
    do
        h3 $mytpl

        # fetch traget file from first line
        target=$( head -1 $mytpl | grep "^# TARGET:" | cut -f 2- -d ":" | awk '{ print $1 }' )

        if [ ! -z "$target" -a -f "../$target" ]; then
            echo -n "REMOVING "
            ls -l "../$target" || exit 2
            rm -f "../$target" || exit 2
            echo OK
        else
            echo SKIP: $target
        fi
        
    done
}

# show running containers
function _showContainers(){
    local bLong=$1
    h2 CONTAINERS
    if [ -z "$bLong" ]; then
        docker-compose -p "$APP_NAME" ps
    else
        docker ps | grep $APP_NAME
    fi
}


# show urls for app container
function _showBrowserurl(){
    echo "In a web browser open:"
    echo "  $frontendurl"
    if grep "${APP_NAME}-server" /etc/hosts >/dev/null; then
        echo "  https://${APP_NAME}-server/"
    fi
}

# detect + show ports and urls for app container and db container
function _showInfos(){
    _showContainers long
    h2 INFO

    h3 "processes"
    docker-compose top

    h3 "Check app port"
    >/dev/tcp/localhost/${APP_PORT} 2>/dev/null && (
        echo "OK, app port ${APP_PORT} is reachable"
        echo
        _showBrowserurl
    )
    if [ "$DB_ADD" != "false" ]; then
        h3 "Check database port"
        >/dev/tcp/localhost/${DB_PORT} >/dev/null 2>&1 && (
            echo "OK, db port ${DB_PORT} is reachable"
            echo
        )
        echo "In a local DB admin tool:"
        echo "  host    : localhost"
        echo "  port    : ${DB_PORT}"
        echo "  user    : root"
        echo "  password: ${MYSQL_ROOT_PASS}"
    fi
    echo
}

# helper for menu: print an inverted key
function  _key(){
    printf "\e[4;7m ${1} \e[0m"
}

# helper: wait for a return key
function _wait(){
    echo -n "... press RETURN > "; read -r -t 15
}

# ----------------------------------------------------------------------
# MAIN
# ----------------------------------------------------------------------

action=$1; shift 1

while true; do

    if [ -z "$action" ]; then

        echo
        echo -e "\e[32m===== INITIALIZER FOR DOCKER APP [$APP_NAME] v$_version ===== \e[0m\n\r"

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
        "-v") echo $(basename $0) $_version; exit 0 ;;
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
            dockerUp="docker-compose -p "$APP_NAME" --verbose up -d --remove-orphans"
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

            _wait
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
            docker ps
            echo -n "id or name >"
            read dockerid
            test -z "$dockerid" || docker exec -it $dockerid /bin/bash
            ;;
        o) 
            h2 "Open app ..."
            xdg-open "$frontendurl"
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
