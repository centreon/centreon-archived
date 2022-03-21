import * as React from 'react';

import { useAtomValue } from 'jotai/utils';
import { useTranslation } from 'react-i18next';
import { useNavigate } from 'react-router';
import { equals, not } from 'ramda';

import { postData, useRequest } from '@centreon/ui';

import WizardFormSetupStatus from '../../components/WizardFormSetupStatus';
import routeMap from '../../reactRoutes/routeMap';
import { remoteServerAtom, RemoteServerData } from '../pollerAtoms';
import {
  labelExportGenerationTimeout,
  labelFinalStep,
} from '../translatedLabels';

const exportTaskEndpoint =
  'internal.php?object=centreon_task_service&action=getTaskStatus';

const RemoteServerWizardStepThree = (): JSX.Element => {
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
  const pollerData = useAtomValue<RemoteServerData | null>(
    remoteServerAtom,
  ) as RemoteServerData;

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
    setError(t(labelExportGenerationTimeout));
    setGenerateStatus(false);
  };

  React.useEffect(() => {
    setGenerationTimeout();
  }, []);

  return (
    <WizardFormSetupStatus
      error={error}
      formTitle={t(labelFinalStep)}
      statusCreating={pollerData.submitStatus ? pollerData.submitStatus : null}
      statusGenerating={generateStatus}
    />
  );
};

export default RemoteServerWizardStepThree;
