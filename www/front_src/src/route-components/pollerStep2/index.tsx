/* eslint-disable react/jsx-no-bind */
/* eslint-disable react/jsx-filename-extension */
/* eslint-disable no-shadow */
/* eslint-disable react/prop-types */

import React, { Component } from 'react';
import { connect } from 'react-redux';
import { SubmissionError } from 'redux-form';
import Form from '../../components/forms/poller/PollerFormStepTwo.tsx';
import ProgressBar from '../../components/progressBar/index.tsx';
import routeMap from '../../route-maps/route-map.ts';
import axios from '../../axios/index.ts';
import { setPollerWizard } from '../../redux/actions/pollerWizardActions.ts';

interface Props {
  history: object;
  setPollerWizard: Function;
  pollerData: object;
}

interface State {
  pollers: Array;
}

class PollerStepTwoRoute extends Component<Props, State> {
  public state = {
    pollers: [],
  };

  private links = [
    {
      active: true,
      prevActive: true,
      number: 1,
      path: routeMap.serverConfigurationWizard,
    },
    { active: true, prevActive: true, number: 2, path: routeMap.pollerStep1 },
    { active: true, number: 3 },
    { active: false, number: 4 },
  ];

  private pollerListApi = axios(
    'internal.php?object=centreon_configuration_remote&action=getRemotesList',
  );

  private wizardFormApi = axios(
    'internal.php?object=centreon_configuration_remote&action=linkCentreonRemoteServer',
  );

  private getPollers = () => {
    this.pollerListApi.post().then((response) => {
      this.setState({ pollers: response.data });
    });
  };

  public componentDidMount = () => {
    this.getPollers();
  };

  private handleSubmit = (data: object) => {
    const { history, pollerData, setPollerWizard } = this.props;
    const postData = { ...data, ...pollerData };
    postData.server_type = 'poller';
    return this.wizardFormApi
      .post('', postData)
      .then((response) => {
        setPollerWizard({ submitStatus: response.data.success });
        if (pollerData.linked_remote_master) {
          history.push(routeMap.pollerStep3);
        } else {
          history.push(routeMap.pollerList);
        }
      })
      .catch((err) => {
        throw new SubmissionError({ _error: new Error(err.response.data) });
      });
  };

  public render() {
    const { links } = this;
    const { pollerData } = this.props;
    const { pollers } = this.state;
    return (
      <div>
        <ProgressBar links={links} />
        <Form
          pollers={pollers}
          initialValues={pollerData}
          onSubmit={this.handleSubmit.bind(this)}
        />
      </div>
    );
  }
}

const mapStateToProps = ({ pollerForm }) => ({
  pollerData: pollerForm,
});

const mapDispatchToProps = {
  setPollerWizard,
};

export default connect(mapStateToProps, mapDispatchToProps)(PollerStepTwoRoute);
