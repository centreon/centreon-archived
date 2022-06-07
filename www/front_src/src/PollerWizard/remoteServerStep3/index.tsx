import { useState, useRef, useEffect } from 'react';

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
import { exportTaskEndpoint } from '../api/endpoints';

const RemoteServerWizardStepThree = (): JSX.Element => {
  const { t } = useTranslation();

  const [error, setError] = useState<string | null>(null);
  const [generateStatus, setGenerateStatus] = useState<boolean | null>(null);

  const { sendRequest: getExportTask } = useRequest<{
    status: string | null;
    success: boolean;
  }>({
    request: postData,
  });

  const navigate = useNavigate();

  const generationTimeoutRef = useRef<NodeJS.Timeout>();

  const remainingGenerationTimeoutRef = useRef<number>(30);
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

  useEffect(() => {
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
