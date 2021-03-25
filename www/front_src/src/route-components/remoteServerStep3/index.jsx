/* eslint-disable react/jsx-filename-extension */
/* eslint-disable camelcase */
/* eslint-disable react/destructuring-assignment */
/* eslint-disable react/prop-types */
/* eslint-disable no-plusplus */

import React, { Component } from 'react';

import { connect } from 'react-redux';
import { withTranslation } from 'react-i18next';

import WizardFormInstallingStatus from '../../components/wizardFormInstallingStatus';
import ProgressBar from '../../components/progressBar';
import routeMap from '../../route-maps/route-map';
import axios from '../../axios';
import BaseWizard from '../../components/forms/baseWizard';

class RemoteServerStepThreeRoute extends Component {
  state = {
    generateStatus: null,
    error: null,
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

  generationTimeout = null;

  remainingGenerationTimeout = 30;

  /**
   * axios call to get task status on central server
   */
  getExportTask = () =>
    axios('internal.php?object=centreon_task_service&action=getTaskStatus');

  componentDidMount = () => {
    this.setGenerationTimeout();
  };

  /**
   * check export files generation step each second (30 tries)
   */
  setGenerationTimeout = () => {
    if (this.remainingGenerationTimeout > 0) {
      this.remainingGenerationTimeout--;
      this.generationTimeout = setTimeout(this.refreshGeneration, 1000);
    } else {
      // display timeout error message
      this.setState({
        generateStatus: false,
        error: 'Export generation timeout',
      });
    }
  };

  /**
   * check files generation on central server
   */
  refreshGeneration = () => {
    const { history } = this.props;
    const { taskId } = this.props.pollerData;

    this.getExportTask()
      .post('', { task_id: taskId })
      .then((response) => {
        if (response.data.success !== true) {
          this.setState({
            generateStatus: false,
            error: JSON.stringify(response.data),
          });
        } else if (response.data.status === 'completed') {
          // when export files is done, redirect to poller list page with 2 seconds delay
          this.setState({ generateStatus: true }, () => {
            setTimeout(() => {
              history.push(routeMap.pollerList);
            }, 2000);
          });
        } else {
          // retry if task is not yet completed
          this.setGenerationTimeout();
        }
      })
      .catch((err) => {
        this.setState({
          generateStatus: false,
          error: JSON.stringify(err.response.data),
        });
      });
  };

  render() {
    const { links } = this;
    const { pollerData, t } = this.props;
    const { generateStatus, error } = this.state;
    return (
      <BaseWizard>
        <ProgressBar links={links} />
        <WizardFormInstallingStatus
          statusCreating={pollerData.submitStatus}
          statusGenerating={generateStatus}
          formTitle={`${t('Finalizing Setup')}:`}
          error={error}
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
  connect(mapStateToProps, mapDispatchToProps)(RemoteServerStepThreeRoute),
);
