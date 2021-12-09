import * as React from 'react';

import PollerStep1 from '../../PollerWizard/pollerStep1';
import RemoteServerStep1 from '../../PollerWizard/remoteServerStep1';
import PollerStep2 from '../../PollerWizard/pollerStep2';
import RemoteServerStep2 from '../../PollerWizard/remoteServerStep2';
import PollerStep3 from '../../PollerWizard/pollerStep3';
import RemoteServerStep3 from '../../PollerWizard/remoteServerStep3';
import ServerConfigurationWizard from '../../PollerWizard/serverConfigurationWizard';

const formSteps = [
  [ServerConfigurationWizard],
  [RemoteServerStep1, PollerStep1],
  [RemoteServerStep2, PollerStep2],
  [RemoteServerStep3, PollerStep3],
];

const PollerWizard = (): JSX.Element => {
  const [currentStep, setCurrentStep] = React.useState(0);
  const [serverType, setServerType] = React.useState<number | null>(null);

  const goToNextStep = (): void => {
    setCurrentStep(currentStep + 1);
  };

  const changeServerType = (type: number): void => {
    setServerType(type);
  };

  const TestForm = formSteps[currentStep][serverType || 0];

  return (
    <TestForm changeServerType={changeServerType} goToNextStep={goToNextStep} />
  );
};

export default PollerWizard;
