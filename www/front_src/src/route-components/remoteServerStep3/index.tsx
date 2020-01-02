/* eslint-disable react/jsx-filename-extension */
/* eslint-disable camelcase */
/* eslint-disable react/destructuring-assignment */
/* eslint-disable react/prop-types */
/* eslint-disable no-plusplus */

import React, { Component } from 'react';
import { connect } from 'react-redux';
import { I18n } from 'react-redux-i18n';
import WizardFormInstallingStatus from '../../components/wizardFormInstallingStatus/index.tsx';
import ProgressBar from '../../components/progressBar/index.tsx';
import routeMap from '../../route-maps/route-map.ts';

import axios from '../../axios/index.ts';

interface Props {
  pollerData: object;
  history: object;
}

interface State {
  generateStatus: object;
  processingStatus: object;
  error: string;
}

class RemoteServerStepThreeRoute extends Component<Props, State> {
  public state = {
    generateStatus: null,
    processingStatus: null,
    error: null,
  };

  private links = [
    {
      active: true,
      prevActive: true,
      number: 1,
    },
    { active: true, prevActive: true, number: 2 },
    { active: true, prevActive: true, number: 3 },
    { active: true, number: 4 },
  ];

  private generationTimeout = null;

  private remainingGenerationTimeout = 30;

  private processingTimeout = null;

  private remainingProcessingTimeout = 30;

  /**
   * axios call to get task status on central server
   */
  private getExportTask = () =>
    axios('internal.php?object=centreon_task_service&action=getTaskStatus');

  /**
   * axios call to get task status on remote server
   */
  private getImportTask = () =>
    axios(
      'internal.php?object=centreon_task_service&action=getRemoteTaskStatusByParent',
    );

  public componentDidMount = () => {
    this.setGenerationTimeout();
  };

  /**
   * check export files generation step each second (30 tries)
   */
  private setGenerationTimeout = () => {
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
   * check remote server processing step each second (30 tries)
   */
  private setProcessingTimeout = () => {
    if (this.remainingProcessingTimeout > 0) {
      this.remainingProcessingTimeout--;
      this.processingTimeout = setTimeout(this.refreshProcession, 1000);
    } else {
      // display timeout error message
      this.setState({
        processingStatus: false,
        error: 'Remote server processing timeout',
      });
    }
  };

  /**
   * check files generation on central server
   */
  private refreshGeneration = () => {
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
          generateStatus: false,
          error: JSON.stringify(err.response.data),
        });
      });
  };

  /**
   * check endpoint on remote server to get import status
   */
  private refreshProcession = () => {
    const { history } = this.props;
    const { server_ip, centreon_folder, taskId } = this.props.pollerData;

    this.getImportTask()
      .post('', {
        server_ip,
        centreon_folder,
        parent_id: taskId,
      })
      .then((response) => {
        if (response.data.success !== true) {
          this.setState({
            generateStatus: false,
            error: JSON.stringify(response.data),
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
          processingStatus: false,
          error: JSON.stringify(err.response.data),
        });
      });
  };

  public render() {
    const { links } = this;
    const { pollerData } = this.props;
    const { generateStatus, processingStatus, error } = this.state;
    return (
      <div>
        <ProgressBar links={links} />
        <WizardFormInstallingStatus
          statusCreating={pollerData.submitStatus}
          statusGenerating={generateStatus}
          statusProcessing={processingStatus}
          formTitle={`${I18n.t('Finalizing Setup')}:`}
          error={error}
        />
      </div>
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
