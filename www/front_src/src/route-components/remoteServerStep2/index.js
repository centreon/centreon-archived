import React, { Component } from "react";
import Form from "../../components/forms/remoteServer/RemoteServerFormStepTwo";
import routeMap from "../../route-maps";
import ProgressBar from "../../components/progressBar";
import axios from "../../axios";
import { connect } from "react-redux";
import { SubmissionError } from "redux-form";
import { setPollerWizard } from "../../redux/actions/pollerWizardActions";

class RemoteServerStepTwoRoute extends Component {
  state = {
    pollers: null
  };

  links = [
    {
      active: true,
      prevActive: true,
      number: 1,
      path: routeMap.serverConfigurationWizard
    },
    {
      active: true,
      prevActive: true,
      number: 2,
      path: routeMap.remoteServerStep1
    },
    { active: true, number: 3 },
    { active: false, number: 4 }
  ];

  pollerListApi = axios(
    "internal.php?object=centreon_configuration_poller&action=list"
  );
  wizardFormApi = axios(
    "internal.php?object=centreon_configuration_remote&action=linkCentreonRemoteServer"
  );

  getPollers = () => {
    this.pollerListApi.get().then(response => {
      this.setState({ pollers: response.data });
    });
  };

  componentDidMount = () => {
    this.getPollers();
  };

  handleSubmit = data => {
    const { history, pollerData, setPollerWizard } = this.props;
    let postData = { ...data, ...pollerData };
    return this.wizardFormApi
      .post("", postData)
      .then(response => {
        setPollerWizard({ submitStatus: response.data.success });
        history.push(routeMap.remoteServerStep3);
      })
      .catch(err => {
        throw new SubmissionError({ _error: new Error(err.response.data) });
      });
  };

  render() {
    const { links } = this,
          { pollerData } = this.props,
          { pollers } = this.state;
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
  pollerData: pollerForm
});

const mapDispatchToProps = {
  setPollerWizard
};

export default connect(mapStateToProps, mapDispatchToProps)(
  RemoteServerStepTwoRoute
);
