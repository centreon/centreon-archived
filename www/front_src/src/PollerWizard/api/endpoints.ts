const legacyBaseEndpoint = './api/internal.php';

export const pollerWaitListEndpoint = `${legacyBaseEndpoint}?object=centreon_configuration_remote&action=getPollerWaitList`;
export const getPollersEndpoint = `${legacyBaseEndpoint}?object=centreon_configuration_remote&action=getRemotesList`;
export const remoteServerWaitListEndpoint = `${legacyBaseEndpoint}?object=centreon_configuration_remote&action=getWaitList`;
export const getRemoteServersEndpoint = `${legacyBaseEndpoint}?object=centreon_configuration_remote&action=getRemotesList`;
export const wizardFormEndpoint = `${legacyBaseEndpoint}?object=centreon_configuration_remote&action=linkCentreonRemoteServer`;
export const exportTaskEndpoint = `${legacyBaseEndpoint}?object=centreon_task_service&action=getTaskStatus`;
