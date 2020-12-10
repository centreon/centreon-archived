#!/bin/bash

MANDATORY_OPTIONS="u:t:h:n:"
OPTIONAL_OPTIONS=("help","root:","node-address:","insecure","template:")
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
        if [[ ! USERNAME_API ]];
        then
          USERNAME_API="$OPTARG"
        else
          err "duplicate flag -u"
          exit 1
        fi
        ;;
      t)
        if [[ ! CURRENT_NODE_TYPE ]];
        then
          CURRENT_NODE_TYPE=$OPTARG
        else
          err "duplicate flag -t"
          exit 1
        ;;
      h)
        if [[ ! TARGET_NODE_ADDRESS ]];
        then
          TARGET_NODE_ADDRESS=$OPTARG
        else
          err "duplicate flag -h"
          exit 1
        ;;
      n)
        if [[ ! CURRENT_NODE_NAME ]];
        then
          CURRENT_NODE_NAME=$OPTARG
        else
          err "duplicate flag -n"
          exit 1
        ;;
    esac
  done

  if [[ ! $USERNAME_API \
    || ! $CURRENT_NODE_TYPE \
    || ! $TARGET_NODE_ADDRESS \
    || ! $CURRENT_NODE_NAME \
  ]]; then
    err "Missing Parameters: -u -h -t -n are mandatories\n"
    display_help_message
    exit 1
  fi
}
#========= end of function parse_command_options()


#========= begin of function get_api_token()
function get_api_token() {
  API_RESPONSE=$(curl -X POST -H "Content-Type: application/json" \
    -d '{"security":{"credentials":{"login":"'${USERNAME_API}'", "password":"'$1'"}}}' \
    "${TARGET_NODE_ADDRESS}/centreon/api/latest/login")
  API_TOKEN=$( echo "${API_RESPONSE}" | grep -o '"token":"[^"]*' | cut -d'"' -f4)
  if [[ ! $API_TOKEN ]];
  then
    err "${API_RESPONSE}"
    exit 1
  fi
  echo $API_TOKEN
}
#========= end of function get_api_token()


#========= begin of function err()
function err() {
  printf "[$(date +%F_%T)]: ERROR - %s\n\n" "$1"
}
#========= end of function err()


#========= begin of function parse_fqdn()

#========= end of function parse_fqdn()


#========= begin of function display_help_message()
function display_help_message() {
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
#========= end of function display_help_message()



###########################################################
#                                                         #
#                    SCRIPT EXECUTION                     #
#                                                         #
###########################################################

parse_command_options "$@"


### If all mandatory flag are present, Ask for TARGET_NODE API Password to get token
stty -echo
echo "${TARGET_NODE_ADDRESS} : Please enter your password "
read API_TARGET_PASSWORD
stty echo
API_TOKEN=$(get_api_token "$API_TARGET_PASSWORD")
exit 0
