#!/bin/bash

MANDATORY_OPTIONS="u:t:h:n:"
USERNAME_API=""
CURRENT_NODE_TYPE=""
TARGET_NODE_ADDRESS=""
CURRENT_NODE_NAME=""
API_TOKEN=""
declare -A SUPPORTED_LOG_LEVEL=([DEBUG]=0 [INFO]=1 [WARNING]=2 [ERROR]=3)
declare -A PARSED_URL=([SCHEME]="http" [HOST]="" [PORT]="80")
runtime_log_level="INFO"

###########################################################
#                                                         #
#                    COMMON FUNCTIONS                     #
#                                                         #
###########################################################

#========= begin of function parse_command_options()
# This function will parse the flag passed to the command and assign them to variables
# If mandatories options are missing an error is returned
function parse_command_options() {
  while getopts $MANDATORY_OPTIONS opt; do
    case ${opt} in
      u)
        set_variable "USERNAME_API" "$OPTARG"
        ;;
      t)
        set_variable "CURRENT_NODE_TYPE" "$OPTARG"
        ;;
      h)
        set_variable "TARGET_NODE_ADDRESS" "$OPTARG"
        ;;
      n)
        set_variable "CURRENT_NODE_NAME" "$OPTARG"
        ;;
    esac
  done

  if [[ ! $USERNAME_API \
    || ! $CURRENT_NODE_TYPE \
    || ! $TARGET_NODE_ADDRESS \
    || ! $CURRENT_NODE_NAME \
  ]]; then
    log "ERROR" "Missing Parameters: -u -h -t -n are mandatories\n"
    usage
    exit 1
  fi
}
#========= end of function parse_command_options()


#========= begin of function set_variable()
set_variable()
{
  local varname=$1
  shift
  if [ -z "${!varname}" ]; then
    eval "$varname=\"$*\""
  else
    log "ERROR" "duplicate flag -${opt}"
    exit 1
  fi
}

#========= end of function set_variable()


#========= begin of function get_api_token()
function get_api_token() {
  API_RESPONSE=$(curl -X POST -H "Content-Type: application/json" \
    -d '{"security":{"credentials":{"login":"'"${USERNAME_API}"'", "password":"'"$1"'"}}}' \
    "${TARGET_NODE_ADDRESS}/centreon/api/latest/login")
  API_TOKEN=$( echo "${API_RESPONSE}" | grep -o '"token":"[^"]*' | cut -d'"' -f4)
  if [[ ! $API_TOKEN ]];
  then
    log "ERROR" "${API_RESPONSE}"
    exit 1
  fi
}
#========= end of function get_api_token()


#========= begin of function log()
# print out the message according to the level
# with timestamp
#
# usage:
# log "$LOG_LEVEL" "$message" ($LOG_LEVEL = DEBUG|INFO|WARNING|ERROR)
#
# example:
# log "DEBUG" "This is a DEBUG_LOG_LEVEL message"
# log "INFO" "This is a INFO_LOG_LEVEL message"
#
function log(){
 TIMESTAMP=$(date --rfc-3339=seconds)

 if [[ -z "${1}" || -z "${2}" ]]
 then
	echo "${TIMESTAMP} - ERROR : Missing argument"
	echo "${TIMESTAMP} - ERROR : Usage log \"INFO\" \"Message log\" "
	exit 1
 fi

 # get the message log level
 log_message_level="${1}"

 # get the log message
 log_message="${2}"

 # check if the log_message_level is greater than the runtime_log_level
 [[ ${SUPPORTED_LOG_LEVEL[$log_message_level]} ]] || return 1

 (( ${SUPPORTED_LOG_LEVEL[$log_message_level]} < ${SUPPORTED_LOG_LEVEL[$runtime_log_level]} )) && return 2

 echo -e "${TIMESTAMP} - $log_message_level - $log_message"

}
#========= end of function log()


#========= begin of function parse_fqdn()
function parse_fqdn() {
# extract the protocol
$PARSED_URL[$SCHEME]="$(echo $1 | grep :// | sed -e's,^\(.*://\).*,\1,g')"
# remove the protocol
url="$(echo ${1/$PARSED_URL[$SCHEME]/})"
# extract the user (if any)
user="$(echo $url | grep @ | cut -d@ -f1)"
# extract the host and port
hostport="$(echo ${url/$user@/} | cut -d/ -f1)"
# by request host without port
$PARSED_URL[$HOST]="$(echo $hostport | sed -e 's,:.*,,g')"
# by request - try to extract the port
$PARSED_URL[$PORT]="$(echo $hostport | sed -e 's,^.*:,:,g' -e 's,.*:\([0-9]*\).*,\1,g' -e 's,[^0-9],,g')"
# extract the path (if any)
path="$(echo $url | grep / | cut -d/ -f2-)"

echo "  url: $url"
echo "  proto: $proto"
echo "  user: $user"
echo "  host: $host"
echo "  port: $port"
echo "  path: $path"
}
#========= end of function parse_fqdn()


#========= begin of function usage()
function usage() {
  cat << EOF
This script will register a platform (CURRENT NODE) on another (TARGET NODE).
If you register a CURRENT NODE on a TARGET NODE that is already linked to a Central,
your informations will automatically be forwarded to the Central.
If you register a Remote Server, this script will automatically convert your CURRENT NODE in Remote Server.
After executing the script, please use the wizard on your Central to complete your installation.

Global Options:
  -u <mandatory>              username of your centreon-web account on the TARGET NODE.
  -h <mandatory>              URL of the TARGET NODE
  -t <mandatory>              the server type you want to register (CURRENT NODE):
            - Poller
            - Remote
            - MAP
            - MBI
  -n <mandatory>              name of the CURRENT NODE that will be displayed on the TARGET NODE

  --help <optional>           get information about the parameters available
  --root <optional>           your Centreon root path on TARGET NODE (by default "centreon")
  --node-address <optional>   provide your FQDN or IP of the CURRENT NODE. FQDN must be resolvable on the TARGET NODE
  --insecure <optional>       allow self-signed certificate
  --template <optional>       provide the path of a register topology configuration file to automate the script
             - API_USERNAME             <mandatory> string
             - API_PASSWORD             <mandatory> string
             - CURRENT_NODE_TYPE        <mandatory> string
             - TARGET_NODE_ADDRESS      <mandatory> string (PARENT NODE ADDRESS)
             - CURRENT_NODE_NAME        <mandatory> string (CURRENT NODE NAME)
             - PROXY_USAGE              <mandatory> boolean
             - ROOT_CENTREON_FOLDER     <optional> string (CENTRAL ROOT CENTREON FOLDER)
             - CURRENT_NODE_ADDRESS     <optional> string (CURRENT NODE IP OR FQDN)
             - INSECURE                 <optional> boolean
             - PROXY_HOST               <optional> string
             - PROXY_PORT               <optional> integer
             - PROXY_USERNAME           <optional> string
             - PROXY_PASSWORD           <optional> string

EOF
}
#========= end of function usage()



###########################################################
#                                                         #
#                    SCRIPT EXECUTION                     #
#                                                         #
###########################################################

parse_command_options "$@"
parse_fqdn "$TARGET_NODE_ADDRESS"
### If all mandatory flag are present, Ask for TARGET_NODE API Password to get token
stty -echo
echo "${TARGET_NODE_ADDRESS} : Please enter your password "
read -r API_TARGET_PASSWORD
stty echo
get_api_token "$API_TARGET_PASSWORD"
