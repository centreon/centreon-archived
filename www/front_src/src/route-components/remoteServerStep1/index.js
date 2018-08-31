import React, { Component } from "react";
import Form from '../../components/forms/remoteServer/RemoteServerFormStepOne';
import { setPollerWizard } from '../../redux/actions/pollerWizardActions';
import ProgressBar from '../../components/progressBar';
import routeMap from '../../route-maps';
import axios from "../../axios";
import { connect } from 'react-redux';

class RemoteServerStepOneRoute extends Component {

  links = [
    { active: true, prevActive: true, number: 1, path: routeMap.serverConfigurationWizard },
    { active: true, number: 2, path: routeMap.remoteServerStep1 },
    { active: false, number: 3 },
    { active: false, number: 4 },
  ];

  state = {
    waitList: null
  }
  wizardFormWaitListApi = axios('internal.php?object=centreon_configuration_remote&action=getWaitList');

  getWaitList = () => {
    this.wizardFormWaitListApi.post()
      .then(response => {
        this.setState({ waitList: JSON.parse(response.data) })
      })
      .catch(err => {
        this.setState({ waitList: [] })
      });
  }

  componentDidMount = () => {
    this.getWaitList()
  }

  handleSubmit = data => {
    const { history, setPollerWizard } = this.props;
    setPollerWizard(data);
    history.push(routeMap.remoteServerStep2);
  };

  render() {
    const { links } = this;
    const {pollerData} = this.props;
    const { waitList } = this.state;
    return (
      <div>
        <ProgressBar links={links} />
        <Form waitList={waitList} initialValues={pollerData} onSubmit={this.handleSubmit.bind(this)} />
      </div>
    )
  }
}

const mapStateToProps = ({ pollerForm }) => ({
  pollerData: pollerForm
});

const mapDispatchToProps = {
  setPollerWizard
}

export default connect(mapStateToProps, mapDispatchToProps)(RemoteServerStepOneRoute);
