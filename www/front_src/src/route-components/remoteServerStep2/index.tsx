/* eslint-disable react/jsx-filename-extension */
/* eslint-disable no-shadow */
/* eslint-disable react/prop-types */
/* eslint-disable no-plusplus */
/* eslint-disable no-underscore-dangle */

import React, { Component } from 'react';
import { connect } from 'react-redux';
import { SubmissionError } from 'redux-form';
import Form from '../../components/forms/remoteServer/RemoteServerFormStepTwo.tsx';
import routeMap from '../../route-maps/route-map.ts';
import ProgressBar from '../../components/progressBar/index.tsx';
import axios from '../../axios/index.ts';
import { setPollerWizard } from '../../redux/actions/pollerWizardActions.ts';

interface Props {
  history: object;
  setPollerWizard: Function;
  pollerData: object;
}

interface State {
  pollers: Array | null;
}

class RemoteServerStepTwoRoute extends Component<Props, State> {
  public state = {
    pollers: null,
  };

  private links = [
    {
      active: true,
      prevActive: true,
      number: 1,
      path: routeMap.serverConfigurationWizard,
    },
    {
      active: true,
      prevActive: true,
      number: 2,
      path: routeMap.remoteServerStep1,
    },
    { active: true, number: 3 },
    { active: false, number: 4 },
  ];

  private pollerListApi = axios(
    'internal.php?object=centreon_configuration_poller&action=list',
  );

  private wizardFormApi = axios(
    'internal.php?object=centreon_configuration_remote&action=linkCentreonRemoteServer',
  );

  private _spliceOutDefaultPoller = (itemArr: Array) => {
    for (let i = 0; i < itemArr.items.length; i++) {
      if (itemArr.items[i].id === '1') itemArr.items.splice(i, 1);
    }
    return itemArr;
  };

  private _filterOutDefaultPoller = (itemArr: Array, clbk: Function) => {
    clbk(this._spliceOutDefaultPoller(itemArr));
  };

  private getPollers = () => {
    this.pollerListApi.get().then((response: object) => {
      this._filterOutDefaultPoller(response.data, (pollers: Array) => {
        this.setState({ pollers });
      });
    });
  };

  public componentDidMount = () => {
    this.getPollers();
  };

  private handleSubmit = (data: object) => {
    const { history, pollerData, setPollerWizard } = this.props;
    const postData = { ...data, ...pollerData };
    postData.server_type = 'remote';
    return this.wizardFormApi
      .post('', postData)
      .then((response: object) => {
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
      .catch((err: Error) => {
        throw new SubmissionError({ _error: new Error(err.response.data) });
      });
  };

  public render() {
    const { links } = this;
    const { pollers } = this.state;
    return (
      <div>
        <ProgressBar links={links} />
        <Form pollers={pollers} onSubmit={this.handleSubmit} />
      </div>
    );
  }
}

interface ReduxState {
  pollerForm: object;
}

const mapStateToProps = ({ pollerForm }: ReduxState) => ({
  pollerData: pollerForm,
});

const mapDispatchToProps = {
  setPollerWizard,
};

export default connect(
  mapStateToProps,
  mapDispatchToProps,
)(RemoteServerStepTwoRoute);
