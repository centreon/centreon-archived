import React, { Component } from "react";
import Form from "../../components/forms/poller/PollerFormStepTwo";
import ProgressBar from "../../components/progressBar";
import routeMap from "../../route-maps";
import axios from "../../axios";
import { connect } from "react-redux";
import { SubmissionError } from "redux-form";
import { setPollerWizard } from "../../redux/actions/pollerWizardActions";

class PollerStepTwoRoute extends Component {
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
    { active: true, prevActive: true, number: 2, path: routeMap.pollerStep1 },
    { active: true, number: 3 },
    { active: false, number: 4 }
  ];

  pollerListApi = axios(
    "internal.php?object=centreon_configuration_remote&action=getRemotesList"
  );
  wizardFormApi = axios(
    "internal.php?object=centreon_configuration_remote&action=linkCentreonRemoteServer"
  );

  getPollers = () => {
    this.pollerListApi.post().then(response => {
      this.setState({ pollers: response.data });
    });
  };

  componentDidMount = () => {
    this.getPollers();
  };

  handleSubmit = data => {
    const { history, pollerData, setPollerWizard } = this.props;
    let postData = { ...data, ...pollerData };
    postData.server_type = 'poller';
    return this.wizardFormApi
      .post("", postData)
      .then(response => {
        setPollerWizard({ submitStatus: response.data.success });
        if (pollerData.linked_remote){
          history.push(routeMap.pollerStep3);
        } else {
          history.push(routeMap.pollerList);
        }
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
          initialValues={{...pollerData, centreon_folder:"/centreon/"}}
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

export default connect(mapStateToProps, mapDispatchToProps)(PollerStepTwoRoute);
