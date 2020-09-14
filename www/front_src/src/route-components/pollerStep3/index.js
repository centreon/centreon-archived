/* eslint-disable react/jsx-filename-extension */
/* eslint-disable react/prop-types */

import React, { Component } from 'react';
import { withTranslation } from 'react-i18next';
import { connect } from 'react-redux';

import WizardFormInstallingStatus from '../../components/wizardFormInstallingStatus';
import ProgressBar from '../../components/progressBar';
import BaseWizard from '../../components/forms/baseWizard';

class PollerStepThreeRoute extends Component {
  state = {
    generateStatus: null,
    processingStatus: null,
  };

  links = [
    {
      active: true,
      prevActive: true,
      number: 1,
    },
    { active: true, prevActive: true, number: 2 },
    { active: true, prevActive: true, number: 3 },
    { active: true, number: 4 },
  ];

  render() {
    const { links } = this;
    const { pollerData, t } = this.props;
    const { generateStatus, processingStatus } = this.state;
    return (
      <BaseWizard>
        <ProgressBar links={links} />
        <WizardFormInstallingStatus
          statusCreating={pollerData.submitStatus}
          statusGenerating={generateStatus}
          statusProcessing={processingStatus}
          formTitle={`${t('Finalizing Setup')}:`}
        />
      </BaseWizard>
    );
  }
}

const mapStateToProps = ({ pollerForm }) => ({
  pollerData: pollerForm,
});

const mapDispatchToProps = {};

export default withTranslation()(
  connect(mapStateToProps, mapDispatchToProps)(PollerStepThreeRoute),
);
