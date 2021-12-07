import * as React from 'react';

import { connect } from 'react-redux';
import { useTranslation, withTranslation } from 'react-i18next';
import { useNavigate } from 'react-router';
import { equals, not } from 'ramda';

import {
  postData,
  useRequest,
} from '@centreon/centreon-frontend/packages/centreon-ui/src';

import WizardFormInstallingStatus from '../../components/wizardFormInstallingStatus';
import ProgressBar from '../../components/progressBar';
import routeMap from '../../route-maps/route-map';
import BaseWizard from '../../components/forms/baseWizard';

const links = [
  {
    active: true,
    number: 1,
    prevActive: true,
  },
  { active: true, number: 2, prevActive: true },
  { active: true, number: 3, prevActive: true },
  { active: true, number: 4 },
];

interface Props {
  pollerData: Record<string, unknown>;
}

const exportTaskEndpoint =
  'internal.php?object=centreon_task_service&action=getTaskStatus';

const RemoteServerStepThreeRoute = ({ pollerData }: Props): JSX.Element => {
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
    <BaseWizard>
      <ProgressBar links={links} />
      <WizardFormInstallingStatus
        error={error}
        formTitle={`${t('Finalizing Setup')}`}
        statusCreating={pollerData.submitStatus}
        statusGenerating={generateStatus}
      />
    </BaseWizard>
  );
};

const mapStateToProps = ({ pollerForm }): Props => ({
  pollerData: pollerForm,
});

export default withTranslation()(
  connect(mapStateToProps, null)(RemoteServerStepThreeRoute),
);
