import { lazy, useState, Suspense, useRef, useEffect } from 'react';

import { equals, isNil, path } from 'ramda';

import makeStyles from '@mui/styles/makeStyles';
import { Box } from '@mui/material';

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

const ServerConfigurationWizard = lazy(
  () => import('../../PollerWizard/serverConfigurationWizard'),
);
const RemoteServerStep1 = lazy(
  () => import('../../PollerWizard/remoteServerStep1'),
);
const PollerStep1 = lazy(() => import('../../PollerWizard/pollerStep1'));
const RemoteServerStep2 = lazy(
  () => import('../../PollerWizard/remoteServerStep2'),
);
const PollerStep2 = lazy(() => import('../../PollerWizard/pollerStep2'));
const RemoteServerStep3 = lazy(
  () => import('../../PollerWizard/remoteServerStep3'),
);
const PollerStep3 = lazy(() => import('../../PollerWizard/pollerStep3'));

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
  wrapper: {
    margin: theme.spacing(2, 4),
  },
}));

const PollerWizard = (): JSX.Element | null => {
  const classes = useStyles();

  const [currentStep, setCurrentStep] = useState(0);
  const [serverType, setServerType] = useState<ServerType>(ServerType.Base);
  const [listingHeight, setListingHeight] = useState(window.innerHeight);
  const listingRef = useRef<HTMLDivElement | null>(null);

  const goToNextStep = (): void => {
    setCurrentStep((step) => step + 1);
  };

  const goToPreviousStep = (): void => {
    setCurrentStep((step) => step - 1);
  };

  const changeServerType = (type: ServerType): void => {
    setServerType(type);
  };

  const resize = (): void => {
    setListingHeight(window.innerHeight);
  };

  useEffect(() => {
    window.addEventListener('resize', resize);

    return () => {
      window.removeEventListener('resize', resize);
    };
  }, []);

  const Form = path<(props: WizardFormProps) => JSX.Element>(
    [currentStep, equals(currentStep, 0) ? ServerType.Base : serverType],
    formSteps,
  );

  if (isNil(Form)) {
    return null;
  }

  const listingContainerHeight =
    listingHeight - (listingRef.current?.getBoundingClientRect().top || 0);

  return (
    <BaseWizard>
      <ProgressBar activeStep={currentStep} steps={steps} />
      <Box
        ref={listingRef}
        sx={{
          maxHeight: listingContainerHeight,
          overflowY: 'auto',
        }}
      >
        <div className={classes.wrapper}>
          <Suspense fallback={<LoadingSkeleton />}>
            <Form
              changeServerType={changeServerType}
              goToNextStep={goToNextStep}
              goToPreviousStep={goToPreviousStep}
            />
          </Suspense>
        </div>
      </Box>
    </BaseWizard>
  );
};

export default PollerWizard;
