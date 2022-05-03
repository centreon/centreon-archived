const legacyBaseEndpoint = './api/internal.php';

const baseRemoteConfigurationEndpoint = `${legacyBaseEndpoint}?action=centreon_configuration_remote`;

export const pollerWaitListEndpoint = `${baseRemoteConfigurationEndpoint}&action=getPollerWaitList`;
export const getPollersEndpoint = `${baseRemoteConfigurationEndpoint}&action=getRemotesList`;
export const remoteServerWaitListEndpoint = `${baseRemoteConfigurationEndpoint}&action=getWaitList`;
export const getRemoteServersEndpoint = `${baseRemoteConfigurationEndpoint}&action=getRemotesList`;
export const wizardFormEndpoint = `${baseRemoteConfigurationEndpoint}&action=linkCentreonRemoteServer`;
export const exportTaskEndpoint = `${legacyBaseEndpoint}?object=centreon_task_service&action=getTaskStatus`;
