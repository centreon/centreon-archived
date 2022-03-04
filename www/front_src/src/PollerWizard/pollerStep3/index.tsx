import * as React from 'react';

import { useAtomValue } from 'jotai/utils';
import { useTranslation } from 'react-i18next';

import { pollerAtom } from '../PollerAtoms';
import WizardFormInstallingStatus from '../../components/wizardFormInstallingStatus';

interface PollerData {
  centreon_central_ip?: string;
  linked_remote_master?: string;
  linked_remote_slaves?: Array<string>;
  open_broker_flow?: boolean;
  server_ip?: string;
  server_name?: string;
  server_type?: string;
  submitStatus?: boolean | null;
}

const FormPollerStepThree = (): JSX.Element => {
  const { t } = useTranslation();

  const pollerData = useAtomValue<PollerData>(pollerAtom);

  return (
    <WizardFormInstallingStatus
      error={null}
      formTitle={`${t('Finalizing Setup')}`}
      statusCreating={pollerData.submitStatus ? pollerData.submitStatus : null}
      statusGenerating={null}
    />
  );
};

export default FormPollerStepThree;
