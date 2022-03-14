import * as React from 'react';

import { useAtomValue } from 'jotai/utils';
import { useTranslation } from 'react-i18next';

import { pollerAtom, PollerData } from '../pollerAtoms';
import WizardFormSetupStatus from '../../components/WizardFormSetupStatus';
import { labelFinalStep } from '../translatedLabels';

const PollerWizardStepThree = (): JSX.Element => {
  const { t } = useTranslation();

  const pollerData = useAtomValue<PollerData>(pollerAtom);

  return (
    <WizardFormSetupStatus
      error={null}
      formTitle={t(labelFinalStep)}
      statusCreating={pollerData.submitStatus ? pollerData.submitStatus : null}
      statusGenerating={null}
    />
  );
};

export default PollerWizardStepThree;
