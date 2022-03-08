import * as React from 'react';

import { useAtomValue } from 'jotai/utils';
import { useTranslation } from 'react-i18next';

import { pollerAtom, PollerData } from '../PollerAtoms';
import WizardFormInstallingStatus from '../../components/wizardFormInstallingStatus';
import { labelFinalStep } from '../translatedLabels';

const FormPollerStepThree = (): JSX.Element => {
  const { t } = useTranslation();

  const pollerData = useAtomValue<PollerData>(pollerAtom);

  return (
    <WizardFormInstallingStatus
      error={null}
      formTitle={t(labelFinalStep)}
      statusCreating={pollerData.submitStatus ? pollerData.submitStatus : null}
      statusGenerating={null}
    />
  );
};

export default FormPollerStepThree;
