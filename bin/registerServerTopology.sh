#!/bin/bash

# Set value empty to prevent already set value while re executing the script
API_USERNAME=""
CURRENT_NODE_TYPE=""
CURRENT_NODE_ADDRESS=""
TARGET_NODE_ADDRESS=""
CURRENT_NODE_NAME=""
CENTREON_BASE_URI=""
INSECURE=""
TEMPLATE_FILE=""
API_TOKEN=""
RESPONSE_MESSAGE=""

declare -A SUPPORTED_LOG_LEVEL=([INFO]=0 [ERROR]=1)
declare -A PARSED_URL=([SCHEME]="http" [HOST]="" [PORT]="80")
declare -A PARSED_CURRENT_NODE_URL=([SCHEME]="" [HOST]="" [PORT]="")
declare -A NODE_TYPE=([remote]=1 [poller]=1 [map]=1 [mbi]=1)
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
          if [[ -z ${NODE_TYPE[$2]} ]];then
            log "ERROR" "Invalid Type '$2'"
            usage
            exit 1
          fi
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
          set_variable "CENTREON_BASE_URI" "$2"
          shift 2
          ;;
        --node-address)
          set_variable "CURRENT_NODE_ADDRESS" "$2"
          parse_current_node_fqdn "$CURRENT_NODE_ADDRESS"
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
    log "ERROR" "$1: ${API_RESPONSE}"
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
  url="$(echo ${1/${userpass}"@"/})"
  # extract the Scheme
  SCHEME="$(echo $url | grep :// | cut -d: -f1)"
  if [ -n "$SCHEME" ]; then
    PARSED_URL[SCHEME]=$SCHEME;
  fi

  # extract the host
  PARSED_URL[HOST]="$(echo ${url/${PARSED_URL[SCHEME]}"://"/} | cut -d: -f1)"

  # extract the port
  PORT="$(echo ${url/${PARSED_URL[SCHEME]}"://"/} | cut -d: -f2)"

  if [ "${PARSED_URL[HOST]}" != "$PORT" ]; then
    PARSED_URL[PORT]=$PORT;
    if [ "$PORT" == "443" ]; then
      PARSED_URL[SCHEME]="https";
    fi
  elif [ "${PARSED_URL[SCHEME]}" == "https" ]; then
    PARSED_URL[PORT]="443";
  elif [ "${PARSED_URL[SCHEME]}" == "http" ]; then
    PARSED_URL[PORT]="80";
  fi
}
#========= end of function parse_fqdn()

#========= begin of function parse_current_node_fqdn()
function parse_current_node_fqdn() {
  # extract the user (if any)
  userpass="$(echo $1 | grep @ | cut -d@ -f1)"
  url="$(echo ${1/${userpass}"@"/})"
  # extract the Scheme
  SCHEME="$(echo $url | grep :// | cut -d: -f1)"
  if [ -n "$SCHEME" ]; then
    PARSED_CURRENT_NODE_URL[SCHEME]=$SCHEME;
  else
    PARSED_CURRENT_NODE_URL[SCHEME]="http";
  fi

  # extract the host
  PARSED_CURRENT_NODE_URL[HOST]="$(echo ${url/${PARSED_CURRENT_NODE_URL[SCHEME]}"://"/} | cut -d: -f1)"

  # extract the port
  PORT="$(echo ${url/${PARSED_CURRENT_NODE_URL[SCHEME]}"://"/} | cut -d: -f2)"

  if [ "${PARSED_CURRENT_NODE_URL[HOST]}" != "$PORT" ]; then
    PARSED_CURRENT_NODE_URL[PORT]=$PORT;
    if [ "$PORT" == "443" ]; then
      PARSED_CURRENT_NODE_URL[SCHEME]="https";
    fi
  elif [ "${PARSED_CURRENT_NODE_URL[SCHEME]}" == "https" ]; then
    PARSED_CURRENT_NODE_URL[PORT]="443";
  elif [ "${PARSED_CURRENT_NODE_URL[SCHEME]}" == "http" ]; then
    PARSED_CURRENT_NODE_URL[PORT]="80";
  fi
}
#========= end of function parse_current_node_fqdn()

#========= begin of function usage()
# Display the usage message
function usage() {
  cat << EOF
  This script will register a platform (CURRENT NODE) on another (TARGET NODE).
  If you register a CURRENT NODE on a TARGET NODE that is already linked to a Central,
  your informations will automatically be forwarded to the Central.
  If you register a Remote Server, this script will automatically convert your CURRENT NODE in Remote Server.
  After executing the script, please use the wizard on your Central to complete your installation.

  Global Options: (Be aware that all options are case sensitive)
    -u [--user] <mandatory>              username of your centreon-web account on the TARGET NODE.
    -h [--host] <mandatory>              URL of the TARGET NODE
    -t [--type] <mandatory>              the server type you want to register (CURRENT NODE):
              - poller
              - remote
              - map
              - mbi
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
              - CENTREON_BASE_URI     <optional> string (CENTRAL ROOT CENTREON FOLDER)
              - INSECURE                 <optional> boolean

              Additional Properties for Remote:
              - API_CURRENT_NODE_USERNAME           <mandatory> string
              - API_CURRENT_NODE_PASSWORD           <mandatory> string
              - API_CURRENT_NODE_BASE_URI           <optional> string
              - PROXY_PORT                          <optional> integer
              - PROXY_HOST                          <optional> string
              - PROXY_USERNAME                      <optional> string
              - PROXY_PASSWORD                      <optional> string

EOF
}
#========= end of function usage()

#========= begin of get_current_node_ip()
function get_current_node_ip() {
  PARSED_CURRENT_NODE_URL[HOST]=$(hostname -I | xargs)

  ips=(${PARSED_CURRENT_NODE_URL[HOST]})
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
      PARSED_CURRENT_NODE_URL[HOST]=${ips[$choice]}
    else
      get_current_node_ip
    fi
  fi
}
#========= end of get_current_node_ip()


#========= begin of prepare_register_payload()
# Format all the information in JSON Format and display a reminder of all sent information
function prepare_register_payload() {
  PAYLOAD='{"name":"'"${CURRENT_NODE_NAME}"'","hostname":"'"${HOSTNAME}"'","type":"'"${CURRENT_NODE_TYPE}"'","address":"'"${PARSED_CURRENT_NODE_URL[HOST]}"'","parent_address":"'"${PARSED_URL[HOST]}"'"}'

  cat << EOD

  Summary of the information that will be sent:

  Api Connection:
  username: ${API_USERNAME}
  password: ******
  target server: ${PARSED_URL[HOST]}

  Pending Registration Server:
  name: ${CURRENT_NODE_NAME}
  hostname: ${HOSTNAME}
  type: ${CURRENT_NODE_TYPE}
  address: ${PARSED_CURRENT_NODE_URL[HOST]}

EOD

  read -p 'Do you want to register this server with the previous information? (y/n): ' IS_VALID

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
    "${PARSED_URL[SCHEME]}://${PARSED_URL[HOST]}:${PARSED_URL[PORT]}/${CENTREON_BASE_URI}/api/latest/platform/topology" | grep -E "(HTTP/|message)"))

  HTTP_CODE="$(echo ${API_RESPONSE[0]} | cut -d ' ' -f2)"
  RESPONSE_MESSAGE=${API_RESPONSE[1]}

  if [[ $HTTP_CODE == "201" ]];
  then
    log "INFO" "The CURRENT NODE ${CURRENT_NODE_TYPE}: '${CURRENT_NODE_NAME}@${PARSED_CURRENT_NODE_URL[HOST]}' linked to TARGET NODE: ${PARSED_URL[SCHEME]}://${PARSED_URL[HOST]}:${PARSED_URL[PORT]} has been added"
  elif [[  -n $RESPONSE_MESSAGE ]];
  then
    log "ERROR" "${PARSED_URL[SCHEME]}://${PARSED_URL[HOST]}:${PARSED_URL[PORT]}: ${RESPONSE_MESSAGE}"
    exit 1
  else
    log "ERROR" "An error occurred while contacting the API using: '${PARSED_URL[SCHEME]}://${PARSED_URL[HOST]}:${PARSED_URL[PORT]}/${CENTREON_BASE_URI}/api/latest/platform/topology' "
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
    parse_current_node_fqdn "$CURRENT_NODE_ADDRESS"
  fi

  if [[ -z ${NODE_TYPE[$CURRENT_NODE_TYPE]} ]];then
    log "ERROR" "Invalid Type '$CURRENT_NODE_TYPE'"
    usage
    exit 1
  fi

  if [[ ! $API_CURRENT_NODE_BASE_URI ]];then
    API_CURRENT_NODE_BASE_URI="centreon";
  fi

  PAYLOAD='{"name":"'"${CURRENT_NODE_NAME}"'","hostname":"'"${HOSTNAME}"'","type":"'"${CURRENT_NODE_TYPE}"'","address":"'"${PARSED_CURRENT_NODE_URL[HOST]}"'","parent_address":"'"${PARSED_URL[HOST]}"'"}'
  REMOTE_PAYLOAD=
}
#========= end of set_variable_from_template()


#========= begin of prepare_remote_payload()
function prepare_remote_payload() {
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
  REMOTE_PAYLOAD='{"isRemote":true,"platformName":"'"${CURRENT_NODE_NAME}"'","centralServerAddress":"'"${PARSED_URL[HOST]}"'","apiUsername":"'"${API_USERNAME}"'","apiCredentials":"'"${API_TARGET_PASSWORD}"'","apiScheme":"'"${PARSED_URL[SCHEME]}"'","apiPort":'"${PARSED_URL[PORT]}"',"apiPath":"'"${CENTREON_BASE_URI}"'",'"${PEER_VALIDATION}"
  if [[ -n PROXY_PAYLOAD ]]; then
    REMOTE_PAYLOAD="${REMOTE_PAYLOAD}""${PROXY_PAYLOAD}"
  fi
  REMOTE_PAYLOAD="${REMOTE_PAYLOAD}}"

  #get response
  IFS=$'\n' REMOTE_API_RESPONSE=($(curl -s -X PATCH ${INSECURE:+--insecure} -i -H "Content-Type: application/json" -H "X-AUTH-TOKEN: ${API_TOKEN}" \
    -d "${REMOTE_PAYLOAD}" \
    "${PARSED_CURRENT_NODE_URL[SCHEME]}://${PARSED_CURRENT_NODE_URL[HOST]}:${PARSED_CURRENT_NODE_URL[PORT]}/${API_CURRENT_NODE_BASE_URI}/api/latest/platform" | grep -E "(HTTP/|message)"))

  HTTP_CODE="$(echo ${REMOTE_API_RESPONSE[0]} | cut -d ' ' -f2)"
  RESPONSE_MESSAGE=${REMOTE_API_RESPONSE[1]}

  if [[ $HTTP_CODE == "204" ]];
  then
    log "INFO" "The CURRENT NODE ${CURRENT_NODE_TYPE}: '${CURRENT_NODE_NAME}@${PARSED_CURRENT_NODE_URL[HOST]}' has been converted and registered successfully."
  elif [[ $RESPONSE_MESSAGE != "" ]];
  then
    log "ERROR" "${PARSED_CURRENT_NODE_URL[HOST]}: ${RESPONSE_MESSAGE}"
    exit 1
  else
    log "ERROR" "An error occurred while contacting the API using: '${PARSED_CURRENT_NODE_URL[SCHEME]}://${PARSED_CURRENT_NODE_URL[HOST]}:${PARSED_CURRENT_NODE_URL[PORT]}/${CENTREON_BASE_URI}/api/latest/platform' "
    exit 1
  fi
}
#========= end of request_to_remote()


#========= begin of set_remote_parameters_manually()
function set_remote_parameters_manually() {
    # ask information to connect to Remote API
    echo "More information is required to convert your platform into Remote : "
    read -p "${PARSED_CURRENT_NODE_URL[HOST]} : Please enter your username: " API_CURRENT_NODE_USERNAME
    read -sp "Please enter the password of ${PARSED_CURRENT_NODE_URL[HOST]}: " API_CURRENT_NODE_PASSWORD; echo ""
    if [ -z ${PARSED_CURRENT_NODE_URL[SCHEME]} ];then
      read -p "${PARSED_CURRENT_NODE_URL[HOST]} : Protocol [http]: " PARSED_CURRENT_NODE_URL[SCHEME]
    fi
    if [ -z ${PARSED_CURRENT_NODE_URL[PORT]} ];then
      read -p "${PARSED_CURRENT_NODE_URL[HOST]} : Port [80]: " PARSED_CURRENT_NODE_URL[PORT]
    fi
    read -p "${PARSED_CURRENT_NODE_URL[HOST]} : centreon root folder [centreon]: " API_CURRENT_NODE_BASE_URI
    if [ -z ${PARSED_CURRENT_NODE_URL[SCHEME]} ]; then
      PARSED_CURRENT_NODE_URL[SCHEME]="http"
    fi
    if [ -z ${PARSED_CURRENT_NODE_URL[PORT]} ]; then
      PARSED_CURRENT_NODE_URL[PORT]="80"
    fi
    if [[ -z $API_CURRENT_NODE_BASE_URI ]]; then
      API_CURRENT_NODE_BASE_URI="centreon"
    fi

    # Set Proxy informations
    read -p "Are you using a proxy ? (y/n): " PROXY_USAGE
    if [[ $PROXY_USAGE == 'y' || $PROXY_USAGE == true ]]; then
      PROXY_PORT="3128"
      read -p "Enter your proxy Host: " PROXY_HOST
      read -p "Enter your proxy Port [3128]: " INPUT_PROXY_PORT
      if [[ -n $INPUT_PROXY_PORT ]]; then
        PROXY_PORT=$INPUT_PROXY_PORT
      fi

      read -p 'Are you using a username/password ? (y/n): ' PROXY_CREDENTIALS
      if [[ $PROXY_CREDENTIALS == 'y' ]]; then
        read -p "Enter your username: " PROXY_USERNAME
        read -sp 'Enter your password: ' PROXY_PASSWORD; echo ""
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
  read -sp "Please enter the password of $TARGET_NODE_ADDRESS: " API_TARGET_PASSWORD; echo ""

  if [[ ! $CURRENT_NODE_ADDRESS ]];
  then
    get_current_node_ip
  fi
  # Prepare Payload & Display Summary
  prepare_register_payload
fi

if [[ ! $CENTREON_BASE_URI ]]
then
  CENTREON_BASE_URI="centreon"
fi

if [[ $CURRENT_NODE_TYPE == 'remote' ]]; then
  prepare_remote_payload
  # get token of Remote API
  get_api_token "${PARSED_CURRENT_NODE_URL[SCHEME]}://${PARSED_CURRENT_NODE_URL[HOST]}:${PARSED_CURRENT_NODE_URL[PORT]}" "$API_CURRENT_NODE_USERNAME" "$API_CURRENT_NODE_PASSWORD" "$API_CURRENT_NODE_BASE_URI"
  # send request to update informations and convert remote
  request_to_remote
else
  # Get the API TARGET Token
  get_api_token "${PARSED_URL[SCHEME]}://${PARSED_URL[HOST]}:${PARSED_URL[PORT]}" "$API_USERNAME" "$API_TARGET_PASSWORD" "$CENTREON_BASE_URI"
  # Send cURL to POST Register
  register_server
fi

exit 0
