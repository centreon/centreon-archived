#!/bin/bash

# Set value empty to prevent already set value while re executing the script
API_USERNAME=""
CURRENT_NODE_TYPE=""
CURRENT_NODE_ADDRESS=""
TARGET_NODE_ADDRESS=""
CURRENT_NODE_NAME=""
ROOT_CENTREON_FOLDER=""
INSECURE=""
TEMPLATE_FILE=""
API_TOKEN=""
RESPONSE_MESSAGE=""

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
  while (($# > 0)); do
    case $1 in
        -t|--type)
          set_variable "CURRENT_NODE_TYPE" "$2"
          # TODO: If Remote call endpoint to register remote
          shift 2
          ;;
        -n|--name)
          set_variable "CURRENT_NODE_NAME" "$2"
          shift 2
          ;;
        -h|--host)
          set_variable "TARGET_NODE_ADDRESS" "$2"
          parse_fqdn "$TARGET_NODE_ADDRESS"
          shift 2
          ;;
        -u|--user)
          set_variable "API_USERNAME" "$2"
          shift 2
          ;;
        --root)
          set_variable "ROOT_CENTREON_FOLDER" "$2"
          shift 2
          ;;
        --node-address)
          set_variable "CURRENT_NODE_ADDRESS" "$2"
          shift 2
          ;;
        --insecure)
          set_variable "INSECURE" true
          shift 1
          ;;
        --template)
          set_variable "TEMPLATE_FILE" "$2"
          set_variable_from_template "$TEMPLATE_FILE"
          shift 2
          ;;
        --help)
          usage
          exit 0
          ;;
        *)
          log "ERROR" "Unrecognized parameter ${1}"
          usage
          exit 1
          ;;
    esac
  done

  # Return an error if mandatory parameters are missing
  if [[ ! $API_USERNAME \
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
function set_variable() {
  local varname=$1
  shift
  if [ -z "${!varname}" ]; then
    eval "$varname=\"$*\""
  else
    log "ERROR" "duplicate flag $1"
    exit 1
  fi
}
#========= end of function set_variable()


#========= begin of function get_api_token()
# Get the X-AUTH-TOKEN used in register request
function get_api_token() {
  API_RESPONSE=$(curl -s -X POST ${INSECURE:+--insecure} -H "Content-Type: application/json" \
    -d '{"security":{"credentials":{"login":"'"${API_USERNAME}"'", "password":"'"$1"'"}}}' \
    "${TARGET_NODE_ADDRESS}/${ROOT_CENTREON_FOLDER}/api/latest/login")

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
function log() {
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
# Parse the -h flag to explode it as SCHEME HOST PORT
function parse_fqdn() {
  PARSED_URL[SCHEME]="$(echo $1 | grep :// | sed -e's,^\(.*://\).*,\1,g')"
  # remove the protocol
  url="$(echo ${1/${PARSED_URL[SCHEME]}/})"
  # extract the user (if any)
  userpass="$(echo $url | grep @ | cut -d@ -f1)"
  pass="$(echo $userpass | grep : | cut -d: -f2)"
  if [ -n "$pass" ]; then
    user="$(echo $userpass | grep : | cut -d: -f1)"
  else
      user=$userpass
  fi

  # extract the host
  PARSED_URL[HOST]="$(echo ${url/$user@/} | cut -d: -f1)"
  # by request - try to extract the port
  PARSED_URL[PORT]="$(echo ${url/PARSED_URL[HOST]/} | grep : | cut -d: -f2)"
}
#========= end of function parse_fqdn()


#========= begin of function usage()
# Display the usage message
function usage() {
  cat << EOF
  This script will register a platform (CURRENT NODE) on another (TARGET NODE).
  If you register a CURRENT NODE on a TARGET NODE that is already linked to a Central,
  your informations will automatically be forwarded to the Central.
  If you register a Remote Server, this script will automatically convert your CURRENT NODE in Remote Server.
  After executing the script, please use the wizard on your Central to complete your installation.

  Global Options:
    -u [--user] <mandatory>              username of your centreon-web account on the TARGET NODE.
    -h [--host] <mandatory>              URL of the TARGET NODE
    -t [--type] <mandatory>              the server type you want to register (CURRENT NODE):
              - Poller
              - Remote
              - MAP
              - MBI
    -n [--name] <mandatory>              name of the CURRENT NODE that will be displayed on the TARGET NODE

    --help <optional>           get information about the parameters available
    --root <optional>           your Centreon root path on TARGET NODE (by default "centreon")
    --node-address <optional>   provide your FQDN or IP of the CURRENT NODE. FQDN must be resolvable on the TARGET NODE
    --insecure <optional>       allow self-signed certificate
    --template <optional>       provide the path of a register topology configuration file to automate the script
              - API_USERNAME             <mandatory> string
              - API_TARGET_PASSWORD             <mandatory> string
              - CURRENT_NODE_TYPE        <mandatory> string
              - TARGET_NODE_ADDRESS      <mandatory> string (PARENT NODE ADDRESS)
              - CURRENT_NODE_NAME        <mandatory> string (CURRENT NODE NAME)
              - CURRENT_NODE_ADDRESS     <mandatory> string (CURRENT NODE IP OR FQDN)
              - ROOT_CENTREON_FOLDER     <optional> string (CENTRAL ROOT CENTREON FOLDER)
              - INSECURE                 <optional> boolean

EOF
}
#========= end of function usage()


#========= begin of get_api_password()
function get_api_password() {
  stty -echo
  echo "${TARGET_NODE_ADDRESS} : Please enter your password"
  read -r API_TARGET_PASSWORD
  stty echo
}
#========= end of get_api_password()

#========= begin of get_current_node_ip()
function get_current_node_ip() {
  CURRENT_NODE_ADDRESS=$(hostname -I)

  ips=($CURRENT_NODE_ADDRESS)
  count_available_ips=${#ips[@]}

  if [[ $count_available_ips -gt 1 ]];
  then
    echo "Which IP do you want to use as CURRENT NODE IP ?"
    for i in "${!ips[@]}";
    do
      printf "%s) %s\n" "$i" "${ips[$i]}"
    done

    read -r choice

    if [[ $choice -ge 0 && $choice -le $count_available_ips-1 ]];
    then
      CURRENT_NODE_ADDRESS=${ips[$choice]}
    else
      get_current_node_ip
    fi
  fi
}
#========= end of get_current_node_ip()


#========= begin of prepare_register_payload()
# Format all the information in JSON Format and display a reminder of all sent information
function prepare_register_payload() {
  PAYLOAD='{"name":"'"${CURRENT_NODE_NAME}"'","hostname":"'"${HOSTNAME}"'","type":"'"${CURRENT_NODE_TYPE}"'","address":"'"${CURRENT_NODE_ADDRESS}"'","parent_address":"'"${PARSED_URL[HOST]}"'"}'

  cat << EOD

  Summary of the information that will be send:

  Api Connection:
  username: ${API_USERNAME}
  password: ******
  target server: ${PARSED_URL[HOST]}

  Pending Registration Server:
  name: ${CURRENT_NODE_NAME}
  hostname: ${HOSTNAME}
  type: ${CURRENT_NODE_TYPE}
  address: ${CURRENT_NODE_ADDRESS}

EOD

  echo 'Do you want to register this server with those information? (y/n) '
  read -r IS_VALID

  if [[ $IS_VALID != 'y' ]];
  then
    log "INFO" "Registration aborted"
    exit 0
  fi
}
#========= end of prepare_register_payload()


#========= begin of register_server()
# Send the request to register the server
function register_server() {
  IFS=$'\n' API_RESPONSE=($(curl -s -X POST ${INSECURE:+--insecure} -i -H "Content-Type: application/json" -H "X-AUTH-TOKEN: ${API_TOKEN}" \
    -d "${PAYLOAD}" \
    "${TARGET_NODE_ADDRESS}/${ROOT_CENTREON_FOLDER}/api/latest/platform/topology" | grep -E "(HTTP/|message)"))

  HTTP_CODE="$(echo ${API_RESPONSE[0]} | cut -d ' ' -f2)"
  RESPONSE_MESSAGE=${API_RESPONSE[1]}

  if [[ $HTTP_CODE == "201" ]];
  then
    log "INFO" "The CURRENT NODE ${CURRENT_NODE_TYPE}: '${CURRENT_NODE_NAME}@${CURRENT_NODE_ADDRESS}' linked to TARGET NODE: ${TARGET_NODE_ADDRESS} has been added"
  elif [[ $RESPONSE_MESSAGE != "" ]];
  then
    log "ERROR" "${RESPONSE_MESSAGE}"
    exit 1
  else
    log "ERROR" "An error occurred while contacting the API using: '${TARGET_NODE_ADDRESS}/centreon/api/latest/platform/topology' "
    exit 1
  fi
}
#========= begin of register_server()


#========= begin of set_variable_from_template()
function set_variable_from_template() {

  source "$1"

  if [[ ! $API_USERNAME \
    || ! $CURRENT_NODE_TYPE \
    || ! $TARGET_NODE_ADDRESS \
    || ! $CURRENT_NODE_NAME \
    || ! $CURRENT_NODE_ADDRESS \
  ]]; then
    log "ERROR" "Missing Parameters: please fill all the template's mandatory fields"
    usage
    exit 1
  else
    parse_fqdn "$TARGET_NODE_ADDRESS"
  fi

  PAYLOAD='{"name":"'"${CURRENT_NODE_NAME}"'","hostname":"'"${HOSTNAME}"'","type":"'"${CURRENT_NODE_TYPE}"'","address":"'"${CURRENT_NODE_ADDRESS}"'","parent_address":"'"${PARSED_URL[HOST]}"'"}'
}
#========= end of set_variable_from_template()

###########################################################
#                                                         #
#                    SCRIPT EXECUTION                     #
#                                                         #
###########################################################

# Get all the flag and assign them to variable
parse_command_options "$@"

if [[ ! $TEMPLATE_FILE ]];
then
  # Ask for API TARGET Password
  get_api_password

  if [[ ! $CURRENT_NODE_ADDRESS ]];
  then
    get_current_node_ip
  fi
  # Prepare Payload & Display Summary
  prepare_register_payload
fi

if [[ ! $ROOT_CENTREON_FOLDER ]]
then
  ROOT_CENTREON_FOLDER="centreon"
fi

# Get the API TARGET Token
get_api_token "$API_TARGET_PASSWORD"

# Send cURL to POST Register
register_server
exit 0