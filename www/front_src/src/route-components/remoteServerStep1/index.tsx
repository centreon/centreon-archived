/* eslint-disable react/jsx-no-bind */
/* eslint-disable react/jsx-filename-extension */
/* eslint-disable no-shadow */
/* eslint-disable react/prop-types */

import React, { Component } from 'react';
import { connect } from 'react-redux';
import Form from '../../components/forms/remoteServer/RemoteServerFormStepOne';
import { setPollerWizard } from '../../redux/actions/pollerWizardActions';
import ProgressBar from '../../components/progressBar';
import routeMap from '../../route-maps/route-map';
import axios from '../../axios';

interface Props {
  history: object:
  setPollerWizard: Function;
  pollerData: object;
}

interface State {
  waitList: Array|null;
}

class RemoteServerStepOneRoute extends Component<Props, State> {
  private links = [
    {
      active: true,
      prevActive: true,
      number: 1,
      path: routeMap.serverConfigurationWizard,
    },
    { active: true, number: 2, path: routeMap.remoteServerStep1 },
    { active: false, number: 3 },
    { active: false, number: 4 },
  ];

  public state = {
    waitList: null,
  };

  private wizardFormWaitListApi = axios(
    'internal.php?object=centreon_configuration_remote&action=getWaitList',
  );

  private getWaitList = () => {
    this.wizardFormWaitListApi
      .post()
      .then((response: object) => {
        this.setState({ waitList: response.data });
      })
      .catch(() => {
        this.setState({ waitList: [] });
      });
  };

  private componentDidMount = () => {
    this.getWaitList();
  };

  private handleSubmit = (data) => {
    const { history, setPollerWizard } = this.props;
    setPollerWizard(data);
    history.push(routeMap.remoteServerStep2);
  };

  public render() {
    const { links } = this;
    const { pollerData } = this.props;
    const { waitList } = this.state;
    return (
      <div>
        <ProgressBar links={links} />
        <Form
          waitList={waitList}
          initialValues={{ ...pollerData, centreon_folder: '/centreon/' }}
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

export default connect(
  mapStateToProps,
  mapDispatchToProps,
)(RemoteServerStepOneRoute);
