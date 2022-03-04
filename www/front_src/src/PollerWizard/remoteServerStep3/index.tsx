import * as React from 'react';

import { useAtomValue } from 'jotai/utils';
import { useTranslation } from 'react-i18next';
import { useNavigate } from 'react-router';
import { equals, not } from 'ramda';

import { postData, useRequest } from '@centreon/ui';

import WizardFormInstallingStatus from '../../components/wizardFormInstallingStatus';
import routeMap from '../../reactRoutes/routeMap';
import { remoteServerAtom } from '../PollerAtoms';

interface RemoteServerData {
  centreon_central_ip?: string;
  centreon_folder?: string;
  db_password?: string;
  db_user?: string;
  // linked_pollers?: Array<string>;
  no_check_certificate?: boolean;
  no_proxy?: boolean;
  server_ip?: string;
  server_name?: string;
  server_type?: string;
  submitStatus?: boolean | null;
  taskId?: number | string;
}

const exportTaskEndpoint =
  'internal.php?object=centreon_task_service&action=getTaskStatus';

const FormRemoteServerStepThree = (): JSX.Element => {
  const { t } = useTranslation();

  const [error, setError] = React.useState<string | null>(null);
  const [generateStatus, setGenerateStatus] = React.useState<boolean | null>(
    null,
  );

  const { sendRequest: getExportTask } = useRequest<{
    status: string | null;
    success: boolean;
  }>({
    request: postData,
  });

  const navigate = useNavigate();

  const generationTimeoutRef = React.useRef<NodeJS.Timeout>();

  const remainingGenerationTimeoutRef = React.useRef<number>(30);
  const pollerData = useAtomValue<RemoteServerData>(remoteServerAtom);

  const refreshGeneration = (): void => {
    const { taskId } = pollerData;

    getExportTask({
      data: { task_id: taskId },
      endpoint: exportTaskEndpoint,
    })
      .then((data) => {
        if (not(data.success)) {
          setError(JSON.stringify(data));
          setGenerateStatus(false);

          return;
        }
        if (equals(data.status, 'completed')) {
          setGenerateStatus(true);
          setTimeout(() => {
            navigate(routeMap.pollerList);
          }, 2000);

          return;
        }
        setGenerationTimeout();
      })
      .catch((err) => {
        setError(JSON.stringify(err.response.data));
        setGenerateStatus(false);
      });
  };

  const setGenerationTimeout = (): void => {
    if (remainingGenerationTimeoutRef.current > 0) {
      remainingGenerationTimeoutRef.current -= 1;
      generationTimeoutRef.current = setTimeout(refreshGeneration, 1000);

      return;
    }
    setError('Export generation timeout');
    setGenerateStatus(false);
  };

  React.useEffect(() => {
    setGenerationTimeout();
  }, []);

  return (
    <WizardFormInstallingStatus
      error={error}
      formTitle={`${t('Finalizing Setup')}`}
      statusCreating={pollerData.submitStatus ? pollerData.submitStatus : null}
      statusGenerating={generateStatus}
    />
  );
};

export default FormRemoteServerStepThree;
