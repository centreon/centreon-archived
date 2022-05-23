const legacyBaseEndpoint = './api/internal.php';

const baseRemoteConfigurationEndpoint = `${legacyBaseEndpoint}?object=centreon_configuration_remote`;

export const pollerWaitListEndpoint = `${baseRemoteConfigurationEndpoint}&action=getPollerWaitList`;
export const remoteServersEndpoint = `${baseRemoteConfigurationEndpoint}&action=getRemotesList`;
export const remoteServerWaitListEndpoint = `${baseRemoteConfigurationEndpoint}&action=getWaitList`;
export const pollersEndpoint = `./api/internal.php?object=centreon_configuration_poller&action=list`;
export const wizardFormEndpoint = `${baseRemoteConfigurationEndpoint}&action=linkCentreonRemoteServer`;
export const exportTaskEndpoint = `${legacyBaseEndpoint}?object=centreon_task_service&action=getTaskStatus`;
