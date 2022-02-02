/* eslint-disable class-methods-use-this */
/* eslint-disable react/no-unused-class-component-methods */
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
import axios from 'axios';
import BaseWizard from '../../components/forms/baseWizard';

class RemoteServerStepThreeRoute extends Component {
  state = {
    error: null,
    generateStatus: null,
  };

  links = [
    {
      active: true,
      number: 1,
      prevActive: true,
    },
    { active: true, number: 2, prevActive: true },
    { active: true, number: 3, prevActive: true },
    { active: true, number: 4 },
  ];

  generationTimeout = null;

  remainingGenerationTimeout = 30;

  componentDidMount() {
    this.setGenerationTimeout();
  }

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
        error: 'Export generation timeout',
        generateStatus: false,
      });
    }
  };

  /**
   * check files generation on central server
   */
  refreshGeneration = () => {
    const { history } = this.props;
    const { taskId } = this.props.pollerData;

    axios.post('./api/internal.php?object=centreon_task_service&action=getTaskStatus', { task_id: taskId })
      .then((response) => {
        if (response.data.success !== true) {
          this.setState({
            error: JSON.stringify(response.data),
            generateStatus: false,
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
          error: JSON.stringify(err.response.data),
          generateStatus: false,
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
          error={error}
          formTitle={`${t('Finalizing Setup')}`}
          statusCreating={pollerData.submitStatus}
          statusGenerating={generateStatus}
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
