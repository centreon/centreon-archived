import * as React from 'react';

import { useTranslation, withTranslation } from 'react-i18next';
import { connect } from 'react-redux';

import WizardFormInstallingStatus from '../../components/wizardFormInstallingStatus';

interface Props {
  pollerData;
}

const FormPollerStepThree = ({ pollerData }: Props): JSX.Element => {
  const { t } = useTranslation();

  return (
    <WizardFormInstallingStatus
      formTitle={`${t('Finalizing Setup')}`}
      statusCreating={pollerData.submitStatus}
      statusGenerating={null}
    />
  );
};

const mapStateToProps = ({ pollerForm }): Props => ({
  pollerData: pollerForm,
});

const mapDispatchToProps = {};

const PollerStepThree = withTranslation()(
  connect(mapStateToProps, mapDispatchToProps)(FormPollerStepThree),
);

export default (): JSX.Element => <PollerStepThree />;
