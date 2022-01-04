import * as React from 'react';

import { equals, isNil, path } from 'ramda';
import makeStyles from '@mui/styles/makeStyles';

import { ServerType, WizardFormProps } from '../../PollerWizard/models';
import BaseWizard from '../../PollerWizard/forms/baseWizard';
import ProgressBar from '../../components/progressBar';
import LoadingSkeleton from '../../PollerWizard/LoadingSkeleton';

import {
  labelAddAdvancedConfiguration,
  labelConfigureServer,
  labelFinishTheSetup,
  labelSelectServerType,
} from './translatedLabels';

const ServerConfigurationWizard = React.lazy(
  () => import('../../PollerWizard/serverConfigurationWizard'),
);
const RemoteServerStep1 = React.lazy(
  () => import('../../PollerWizard/remoteServerStep1'),
);
const PollerStep1 = React.lazy(() => import('../../PollerWizard/pollerStep1'));
const RemoteServerStep2 = React.lazy(
  () => import('../../PollerWizard/remoteServerStep2'),
);
const PollerStep2 = React.lazy(() => import('../../PollerWizard/pollerStep2'));
const RemoteServerStep3 = React.lazy(
  () => import('../../PollerWizard/remoteServerStep3'),
);
const PollerStep3 = React.lazy(() => import('../../PollerWizard/pollerStep3'));

const formSteps = [
  { [ServerType.Base]: ServerConfigurationWizard },
  { [ServerType.Remote]: RemoteServerStep1, [ServerType.Poller]: PollerStep1 },
  { [ServerType.Remote]: RemoteServerStep2, [ServerType.Poller]: PollerStep2 },
  { [ServerType.Remote]: RemoteServerStep3, [ServerType.Poller]: PollerStep3 },
];

const steps = [
  labelSelectServerType,
  labelConfigureServer,
  labelAddAdvancedConfiguration,
  labelFinishTheSetup,
];

const useStyles = makeStyles((theme) => ({
  formContainer: {
    margin: theme.spacing(2, 4),
  },
}));

const PollerWizard = (): JSX.Element | null => {
  const classes = useStyles();

  const [currentStep, setCurrentStep] = React.useState(0);
  const [serverType, setServerType] = React.useState<ServerType>(
    ServerType.Base,
  );

  const goToNextStep = (): void => {
    setCurrentStep((step) => step + 1);
  };

  const goToPreviousStep = (): void => {
    setCurrentStep((step) => step - 1);
  };

  const changeServerType = (type: ServerType): void => {
    setServerType(type);
  };

  const Form = path<(props: WizardFormProps) => JSX.Element>(
    [currentStep, equals(currentStep, 0) ? ServerType.Base : serverType],
    formSteps,
  );

  if (isNil(Form)) {
    return null;
  }

  return (
    <BaseWizard>
      <ProgressBar activeStep={currentStep} steps={steps} />
      <div className={classes.formContainer}>
        <React.Suspense fallback={<LoadingSkeleton />}>
          <Form
            changeServerType={changeServerType}
            goToNextStep={goToNextStep}
            goToPreviousStep={goToPreviousStep}
          />
        </React.Suspense>
      </div>
    </BaseWizard>
  );
};

export default PollerWizard;
