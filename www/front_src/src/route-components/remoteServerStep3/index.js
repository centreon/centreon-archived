/* eslint-disable react/jsx-filename-extension */
/* eslint-disable camelcase */
/* eslint-disable react/destructuring-assignment */
/* eslint-disable react/prop-types */
/* eslint-disable no-plusplus */

import React, { Component } from 'react';
import { connect } from 'react-redux';
import { I18n } from 'react-redux-i18n';

import WizardFormInstallingStatus from '../../components/wizardFormInstallingStatus';
import ProgressBar from '../../components/progressBar';
import routeMap from '../../route-maps/route-map';
import axios from '../../axios';
import BaseWizard from '../../components/forms/baseWizard';

class RemoteServerStepThreeRoute extends Component {
  state = {
    error: null,
    generateStatus: null,
    processingStatus: null,
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

  processingTimeout = null;

  remainingProcessingTimeout = 30;

  /**
   * axios call to get task status on central server
   */
  getExportTask = () =>
    axios('internal.php?object=centreon_task_service&action=getTaskStatus');

  /**
   * axios call to get task status on remote server
   */
  getImportTask = () =>
    axios(
      'internal.php?object=centreon_task_service&action=getRemoteTaskStatusByParent',
    );

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
        error: 'Export generation timeout',
        generateStatus: false,
      });
    }
  };

  /**
   * check remote server processing step each second (30 tries)
   */
  setProcessingTimeout = () => {
    if (this.remainingProcessingTimeout > 0) {
      this.remainingProcessingTimeout--;
      this.processingTimeout = setTimeout(this.refreshProcession, 1000);
    } else {
      // display timeout error message
      this.setState({
        error: 'Remote server processing timeout',
        processingStatus: false,
      });
    }
  };

  /**
   * check files generation on central server
   */
  refreshGeneration = () => {
    const { taskId } = this.props.pollerData;

    this.getExportTask()
      .post('', { task_id: taskId })
      .then((response) => {
        if (response.data.success !== true) {
          this.setState({
            error: JSON.stringify(response.data),
            generateStatus: false,
          });
        } else if (response.data.status === 'completed') {
          // when export files is done, check remote server processing
          this.setState({ generateStatus: true }, () => {
            this.setProcessingTimeout();
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

  /**
   * check endpoint on remote server to get import status
   */
  refreshProcession = () => {
    const { history } = this.props;
    const { server_ip, centreon_folder, taskId } = this.props.pollerData;

    this.getImportTask()
      .post('', {
        centreon_folder,
        parent_id: taskId,
        server_ip,
      })
      .then((response) => {
        if (response.data.success !== true) {
          this.setState({
            error: JSON.stringify(response.data),
            generateStatus: false,
          });
        } else if (response.data.status === 'completed') {
          // when remote server processing is done, redirect to poller list page with 2 seconds delay
          this.setState({ processingStatus: true }, () => {
            setTimeout(() => {
              history.push(routeMap.pollerList);
            }, 2000);
          });
        } else {
          // retry if task is not yet completed
          this.setProcessingTimeout();
        }
      })
      .catch((err) => {
        this.setState({
          error: JSON.stringify(err.response.data),
          processingStatus: false,
        });
      });
  };

  render() {
    const { links } = this;
    const { pollerData } = this.props;
    const { generateStatus, processingStatus, error } = this.state;
    return (
      <BaseWizard>
        <ProgressBar links={links} />
        <WizardFormInstallingStatus
          error={error}
          formTitle={`${I18n.t('Finalizing Setup')}:`}
          statusCreating={pollerData.submitStatus}
          statusGenerating={generateStatus}
          statusProcessing={processingStatus}
        />
      </BaseWizard>
    );
  }
}

const mapStateToProps = ({ pollerForm }) => ({
  pollerData: pollerForm,
});

const mapDispatchToProps = {};

export default connect(
  mapStateToProps,
  mapDispatchToProps,
)(RemoteServerStepThreeRoute);
