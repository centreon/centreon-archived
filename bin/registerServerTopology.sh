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
REMOTE_API_TOKEN=""
RESPONSE_MESSAGE=""

declare -A SUPPORTED_LOG_LEVEL=([INFO]=0 [ERROR]=1)
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
    -d '{"security":{"credentials":{"login":"'"$2"'", "password":"'"$3"'"}}}' \
    "$1/$4/api/latest/login")

  API_TOKEN=$( echo "${API_RESPONSE}" | grep -o '"token":"[^"]*' | cut -d'"' -f4)

  if [[ ! $API_RESPONSE ]];
    then
      log "ERROR" "Couldn't connect to $1"
      exit 1
  fi
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
# log "$LOG_LEVEL" "$message" ($LOG_LEVEL = INFO|ERROR)
#
# example:
# log "ERROR" "This is a ERROR_LOG_LEVEL message"
# log "INFO" "This is a INFO_LOG_LEVEL message"
#
function log() {
  TIMESTAMP=$(date --rfc-3339=seconds)

  if [[ -z "${1}" || -z "${2}" ]]
  then
    echo "${TIMESTAMP} - ERROR : Missing argument"
    echo "${TIMESTAMP} - ERROR : Usage log \"TYPE\" \"Message log\" "
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
  # extract the user (if any)
  userpass="$(echo $1 | grep @ | cut -d@ -f1)"
  pass="$(echo $userpass | grep : | cut -d: -f2)"
  if [ -n "$pass" ]; then
    user="$(echo $userpass | grep : | cut -d: -f1)"
  else
    user=$userpass
  fi
  url="$(echo ${1/${userpass}"@"/})"

  # extract the Scheme
  SCHEME="$(echo $url | grep :// | cut -d: -f1)"
  if [ -n "$SCHEME" ]; then
    PARSED_URL[SCHEME]=$SCHEME;
  fi

  # extract the host
  PARSED_URL[HOST]="$(echo ${url/${PARSED_URL[SCHEME]}"://"/} | cut -d: -f1)"

  # extract the port
  PORT="$(echo ${url/PARSED_URL[HOST]/} | grep : | cut -d: -f3)"
  if [ -n "$PORT" ]; then
    PARSED_URL[PORT]=$PORT
  fi
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
              - API_TARGET_PASSWORD      <mandatory> string
              - CURRENT_NODE_TYPE        <mandatory> string
              - TARGET_NODE_ADDRESS      <mandatory> string (PARENT NODE ADDRESS)
              - CURRENT_NODE_NAME        <mandatory> string (CURRENT NODE NAME)
              - CURRENT_NODE_ADDRESS     <mandatory> string (CURRENT NODE IP OR FQDN)
              - ROOT_CENTREON_FOLDER     <optional> string (CENTRAL ROOT CENTREON FOLDER)
              - INSECURE                 <optional> boolean

              Additional Properties for Remote:
              - API_CURRENT_NODE_USERNAME           <mandatory> string
              - API_CURRENT_NODE_PASSWORD           <mandatory> string
              - API_CURRENT_NODE_PROTOCOL           <optional> string
              - API_CURRENT_NODE_PORT               <optional> string
              - API_CURRENT_NODE_CENTREON_FOLDER    <optional> string
              - PROXY_PORT                          <optional> integer
              - PROXY_HOST                          <optional> string
              - PROXY_USERNAME                      <optional> string
              - PROXY_PASSWORD                      <optional> string

EOF
}
#========= end of function usage()


#========= begin of get_api_password()
function get_api_password() {
  stty -echo
  echo "$1 : Please enter your password:"
  read -r API_TARGET_PASSWORD
  stty echo
}
#========= end of get_api_password()


#========= begin of get_current_node_ip()
function get_current_node_ip() {
  CURRENT_NODE_ADDRESS=$(hostname -I | xargs)

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
  # We set IFS to \n to correctly parse and extract HTTP Code and message
  IFS=$'\n' API_RESPONSE=($(curl -s -X POST ${INSECURE:+--insecure} -i -H "Content-Type: application/json" -H "X-AUTH-TOKEN: ${API_TOKEN}" \
    -d "${PAYLOAD}" \
    "${TARGET_NODE_ADDRESS}/${ROOT_CENTREON_FOLDER}/api/latest/platform/topology" | grep -E "(HTTP/|message)"))

  HTTP_CODE="$(echo ${API_RESPONSE[0]} | cut -d ' ' -f2)"
  RESPONSE_MESSAGE=${API_RESPONSE[1]}

  if [[ $HTTP_CODE == "201" ]];
  then
    log "INFO" "The CURRENT NODE ${CURRENT_NODE_TYPE}: '${CURRENT_NODE_NAME}@${CURRENT_NODE_ADDRESS}' linked to TARGET NODE: ${TARGET_NODE_ADDRESS} has been added"
  elif [[  -n $RESPONSE_MESSAGE ]];
  then
    log "ERROR" "${RESPONSE_MESSAGE}"
    exit 1
  else
    log "ERROR" "An error occurred while contacting the API using: '${TARGET_NODE_ADDRESS}/${ROOT_CENTREON_FOLDER}/api/latest/platform/topology' "
    exit 1
  fi
}
#========= begin of register_server()


#========= begin of set_variable_from_template()
function set_variable_from_template() {
  source "$1"

  if [[ ! $API_USERNAME \
    || ! $API_TARGET_PASSWORD \
    || ! $CURRENT_NODE_TYPE \
    || ! $TARGET_NODE_ADDRESS \
    || ! $CURRENT_NODE_NAME \
    || ! $CURRENT_NODE_ADDRESS \
  ]]; then
    log "ERROR" "Missing Parameters: please fill all the template's mandatory parameters"
    usage
    exit 1
  else
    parse_fqdn "$TARGET_NODE_ADDRESS"
  fi

  PAYLOAD='{"name":"'"${CURRENT_NODE_NAME}"'","hostname":"'"${HOSTNAME}"'","type":"'"${CURRENT_NODE_TYPE}"'","address":"'"${CURRENT_NODE_ADDRESS}"'","parent_address":"'"${PARSED_URL[HOST]}"'"}'
  REMOTE_PAYLOAD=
}
#========= end of set_variable_from_template()


#========= begin of prepare_remote_payload()
function prepare_remote_payload() {
  # set default variables
  API_CURRENT_NODE_PROTOCOL="http"
  API_CURRENT_NODE_PORT="80"
  API_CURRENT_NODE_CENTREON_FOLDER="centreon"

  # if no template are used, ask for information
  if [[ ! $TEMPLATE_FILE ]]; then
    set_remote_parameters_manually
  fi

  # set peerValidation information
  if [ -n $INSECURE ]; then
    PEER_VALIDATION='"peerValidation": false'
  else
    PEER_VALIDATION='"peerValidation": true'
  fi
}
#========= end of prepare_remote_payload()


#========= begin of request_to_remote()
function request_to_remote() {
  # Prepare Proxy Payload
  if [[ -n $PROXY_HOST ]]; then
    PROXY_PAYLOAD=', "proxy":{"host":"'"${PROXY_HOST}"'","port":'"${PROXY_PORT}"
    if [[ -n $PROXY_USERNAME ]]; then
      PROXY_PAYLOAD="${PROXY_PAYLOAD}"',"user":"'"${PROXY_USERNAME}"'","password":"'"${PROXY_PASSWORD}"'"'
    fi
    PROXY_PAYLOAD="${PROXY_PAYLOAD}"'}'
  fi

  # Prepare Remote Payload
  REMOTE_PAYLOAD='{"isRemote":true,"platformName":"'"${CURRENT_NODE_NAME}"'","centralServerAddress":"'"${TARGET_NODE_ADDRESS}"'","apiUsername":"'"${API_USERNAME}"'","apiCredentials":"'"${API_TARGET_PASSWORD}"'","apiScheme":"'"${PARSED_URL[SCHEME]}"'","apiPort":'"${PARSED_URL[PORT]}"',"apiPath":"'"${ROOT_CENTREON_FOLDER}"'",'"${PEER_VALIDATION}"
  if [[ -n PROXY_PAYLOAD ]]; then
    REMOTE_PAYLOAD="${REMOTE_PAYLOAD}""${PROXY_PAYLOAD}"
  fi
  REMOTE_PAYLOAD="${REMOTE_PAYLOAD}}"

  #get response
  IFS=$'\n' REMOTE_API_RESPONSE=($(curl -s -X PATCH ${INSECURE:+--insecure} -i -H "Content-Type: application/json" -H "X-AUTH-TOKEN: ${API_TOKEN}" \
    -d "${REMOTE_PAYLOAD}" \
    "${API_CURRENT_NODE_PROTOCOL}://${CURRENT_NODE_ADDRESS}:${API_CURRENT_NODE_PORT}/${ROOT_CENTREON_FOLDER}/api/latest/platform" | grep -E "(HTTP/|message)"))

  HTTP_CODE="$(echo ${REMOTE_API_RESPONSE[0]} | cut -d ' ' -f2)"
  RESPONSE_MESSAGE=${REMOTE_API_RESPONSE[1]}

  if [[ $HTTP_CODE == "204" ]];
  then
    log "INFO" "The CURRENT NODE ${CURRENT_NODE_TYPE}: '${CURRENT_NODE_NAME}@${CURRENT_NODE_ADDRESS}' has been converted and registered successfully."
  elif [[ $RESPONSE_MESSAGE != "" ]];
  then
    log "ERROR" "${RESPONSE_MESSAGE}"
    exit 1
  else
    log "ERROR" "An error occurred while contacting the API using: '${CURRENT_NODE_ADDRESS}/${ROOT_CENTREON_FOLDER}/api/latest/platform' "
    exit 1
  fi
}
#========= end of request_to_remote()


#========= begin of set_remote_parameters_manually()
function set_remote_parameters_manually() {
    # ask information to connect to Remote API
    echo "A few more information are required to convert your platform into Remote : "
    echo "${CURRENT_NODE_ADDRESS} : Please enter your username:"
    read -r API_CURRENT_NODE_USERNAME
    get_api_password "$CURRENT_NODE_ADDRESS"
    API_CURRENT_NODE_PASSWORD=$API_TARGET_PASSWORD
    echo "${CURRENT_NODE_ADDRESS} : Protocol [http]:"
    read -r INPUT_PROTOCOL
    echo "${CURRENT_NODE_ADDRESS} : Port [80]:"
    read -r INPUT_PORT
    echo "${CURRENT_NODE_ADDRESS} : centreon root folder [centreon]:"
    read -r INPUT_CENTREON_FOLDER
    if [[ -n $INPUT_PROTOCOL ]]; then
      API_CURRENT_NODE_PROTOCOL=$INPUT_PROTOCOL
    fi
    if [[ -n $INPUT_PORT ]]; then
      API_CURRENT_NODE_PORT=$INPUT_PORT
    fi
    if [[ -n $INPUT_PORT ]]; then
      API_CURRENT_NODE_CENTREON_FOLDER=$INPUT_CENTREON_FOLDER
    fi
      # Set Proxy informations
    echo "Are you using a proxy ? (y/n)"
    read -r PROXY_USAGE
    if [[ $PROXY_USAGE == 'y' || $PROXY_USAGE == true ]]; then
      PROXY_PORT="3128"
      echo 'enter your proxy Host:'
      read -r PROXY_HOST
      echo 'enter your proxy Port [3128]:'
      read -r INPUT_PROXY_PORT
      if [[ -n $INPUT_PROXY_PORT ]]; then
        PROXY_PORT=$INPUT_PROXY_PORT
      fi

      echo 'Are you using a username/password ? (y/n)'
      read -r PROXY_CREDENTIALS
      if [[ $PROXY_CREDENTIALS == 'y' ]]; then
        echo 'enter your username:'
        read -r PROXY_USERNAME
        stty -echo
        echo 'enter your password:'
        read -r PROXY_PASSWORD
        stty echo
      fi
    fi
}
#========= end of set_remote_parameters_manually()
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
  get_api_password "$TARGET_NODE_ADDRESS"

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

if [[ $CURRENT_NODE_TYPE == 'remote' ]]; then
  prepare_remote_payload
  # get token of Remote API
  get_api_token "$CURRENT_NODE_ADDRESS" "$API_CURRENT_NODE_USERNAME" "$API_CURRENT_NODE_PASSWORD" "$API_CURRENT_NODE_CENTREON_FOLDER"
  # send request to update informations and convert remote
  request_to_remote
else
  # Get the API TARGET Token
  get_api_token "$TARGET_NODE_ADDRESS" "$API_USERNAME" "$API_TARGET_PASSWORD" "$ROOT_CENTREON_FOLDER"
  # Send cURL to POST Register
  register_server
fi

exit 0