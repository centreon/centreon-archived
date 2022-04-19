import { useState, useRef, useEffect } from 'react';

import { connect } from 'react-redux';
import { useTranslation, withTranslation } from 'react-i18next';
import { useNavigate } from 'react-router';
import { equals, not } from 'ramda';

import { postData, useRequest } from '@centreon/ui';

import WizardFormInstallingStatus from '../../components/wizardFormInstallingStatus';
import routeMap from '../../reactRoutes/routeMap';

interface Props {
  pollerData;
}

const exportTaskEndpoint =
  'internal.php?object=centreon_task_service&action=getTaskStatus';

const FormRemoteServerStepThree = ({ pollerData }: Props): JSX.Element => {
  const { t } = useTranslation();

  const [error, setError] = useState<string | null>(null);
  const [generateStatus, setGenerateStatus] = useState<boolean | null>(
    null,
  );

  const { sendRequest: getExportTask } = useRequest<{
    status: string | null;
    success: boolean;
  }>({
    request: postData,
  });

  const navigate = useNavigate();

  const generationTimeoutRef = useRef<NodeJS.Timeout>();

  const remainingGenerationTimeoutRef = useRef<number>(30);

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

  useEffect(() => {
    setGenerationTimeout();
  }, []);

  return (
    <WizardFormInstallingStatus
      error={error}
      formTitle={`${t('Finalizing Setup')}`}
      statusCreating={pollerData.submitStatus}
      statusGenerating={generateStatus}
    />
  );
};

const mapStateToProps = ({ pollerForm }): Props => ({
  pollerData: pollerForm,
});

const mapDispatchToProps = {};

const RemoteServerStepThree = withTranslation()(
  connect(mapStateToProps, mapDispatchToProps)(FormRemoteServerStepThree),
);

export default (): JSX.Element => <RemoteServerStepThree />;
