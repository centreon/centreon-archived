 var validateIPaddress = (ipaddress) =>
    (/^(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/.test(ipaddress)) ?
    "" : "Not a valid IP address";

export const serverNameValidator = serverName =>
  !serverName ? "The field is required" : "";

export const serverIpAddressValidator = serverIpAddress => {
    let message = "";
    message = !serverIpAddress || serverIpAddress.length < 1 ? "The field is required" : "";
    message = (message === "" ? validateIPaddress(serverIpAddress) : message) ;
    return message;
}

export const centralIpAddressValidator = centralIpAddress => {
    let message = "";
    message = !centralIpAddress || centralIpAddress.length < 1 ? "The field is required" : "";
    message = (message === "" ? validateIPaddress(centralIpAddress) : message) ;
    return message;
}


export const selectRemoteServerValidator = selectRemoteServer =>
   !selectRemoteServer || selectRemoteServer.length < 1 ? "The field is required" : "";

export const databaseUserValidator = databaseUser =>
  !databaseUser ? "The field is required" : "";

export const databasePasswordValidator = databasePassword =>
  !databasePassword ? "The field is required" : "";

export const selectDistantPollersValidator = selectDistantPollers =>
  !selectDistantPollers ? "The field is required" : "";
