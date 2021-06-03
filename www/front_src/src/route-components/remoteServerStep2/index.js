/* eslint-disable react/jsx-filename-extension */
/* eslint-disable no-shadow */
/* eslint-disable react/prop-types */
/* eslint-disable no-plusplus */
/* eslint-disable no-underscore-dangle */

import React, { Component } from 'react';

import { connect } from 'react-redux';
import { SubmissionError } from 'redux-form';

import Form from '../../components/forms/remoteServer/RemoteServerFormStepTwo';
import routeMap from '../../route-maps/route-map';
import ProgressBar from '../../components/progressBar';
import axios from '../../axios';
import { setPollerWizard } from '../../redux/actions/pollerWizardActions';
import BaseWizard from '../../components/forms/baseWizard';

class RemoteServerStepTwoRoute extends Component {
  state = {
    pollers: null,
  };

  links = [
    {
      active: true,
      number: 1,
      path: routeMap.serverConfigurationWizard,
      prevActive: true,
    },
    {
      active: true,
      number: 2,
      path: routeMap.remoteServerStep1,
      prevActive: true,
    },
    { active: true, number: 3 },
    { active: false, number: 4 },
  ];

  pollerListApi = axios(
    'internal.php?object=centreon_configuration_poller&action=list',
  );

  wizardFormApi = axios(
    'internal.php?object=centreon_configuration_remote&action=linkCentreonRemoteServer',
  );

  _spliceOutDefaultPoller = (itemArr) => {
    for (let i = 0; i < itemArr.items.length; i++) {
      if (itemArr.items[i].id === '1') itemArr.items.splice(i, 1);
    }
    return itemArr;
  };

  _filterOutDefaultPoller = (itemArr, clbk) => {
    clbk(this._spliceOutDefaultPoller(itemArr));
  };

  getPollers = () => {
    this.pollerListApi.get().then((response) => {
      this._filterOutDefaultPoller(response.data, (pollers) => {
        this.setState({ pollers });
      });
    });
  };

  componentDidMount = () => {
    this.getPollers();
  };

  handleSubmit = (data) => {
    const { history, pollerData, setPollerWizard } = this.props;
    const postData = { ...data, ...pollerData };
    postData.server_type = 'remote';
    return this.wizardFormApi
      .post('', postData)
      .then((response) => {
        if (response.data.success && response.data.task_id) {
          setPollerWizard({
            submitStatus: response.data.success,
            taskId: response.data.task_id,
          });
          history.push(routeMap.remoteServerStep3);
        } else {
          history.push(routeMap.pollerList);
        }
      })
      .catch((err) => {
        throw new SubmissionError({ _error: new Error(err.response.data) });
      });
  };

  render() {
    const { links } = this;
    const { pollers } = this.state;
    return (
      <BaseWizard>
        <ProgressBar links={links} />
        <Form pollers={pollers} onSubmit={this.handleSubmit} />
      </BaseWizard>
    );
  }
}

const mapStateToProps = ({ pollerForm }) => ({
  pollerData: pollerForm,
});

const mapDispatchToProps = {
  setPollerWizard,
};

export default connect(
  mapStateToProps,
  mapDispatchToProps,
)(RemoteServerStepTwoRoute);
