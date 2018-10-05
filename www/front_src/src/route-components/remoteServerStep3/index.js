import React, { Component } from "react";
import WizardFormInstallingStatus from "../../components/wizardFormInstallingStatus";
import ProgressBar from "../../components/progressBar";
import routeMap from "../../route-maps";

import { connect } from "react-redux";
import axios from "../../axios";

import axiosRemote from "../../axios/remote";

class RemoteServerStepThreeRoute extends Component {
  state = {
    generateStatus: null,
    processingStatus: null
  };

  links = [
    {
      active: true,
      prevActive: true,
      number: 1
    },
    { active: true, prevActive: true, number: 2 },
    { active: true, prevActive: true, number: 3 },
    { active: true, number: 4 }
  ];

  generationInterval = null;
  processingInterval = null;

  getExportTask = axios(
    "internal.php?object=centreon_task_service&action=getTaskStatus"
  );

  getImportTask = () => {
      const { pollerData } = this.props;

      return axiosRemote(
          "http://" +  pollerData.server_ip + "/" + pollerData.centreon_folder + "/internal.php?object=centreon_task_service&action=getTaskStatusByParent"
      );
  };

  UNSAFE_componentWillMount = () => {
      this._setGenerationInterval();
  };

  _setGenerationInterval = () => {
      this.generationInterval = setInterval(
          this.refreshGeneration,
          1000
      )
  };

  _setProcessingInterval = () => {
      this.processingInterval = setInterval(
          this.refreshProcession,
          1000
      )
  };

  refreshProcession = () => {
    const {history, pollerData} = this.props;

      this.getImportTask
          .post("", {"parent_id":pollerData.task_id})
          .then(response => {
              if (response.data.finished === true){
                  clearInterval(this.processingInterval);
                  this.setState({
                      processingStatus:response.data.success
                  }, () => {
                      history.push(routeMap.pollerList)
                  });
              }
          })
          .catch(err => {
              this.setState({ processingStatus: false });
          });

    };

    refreshGeneration = () => {
        const {pollerData} = this.props;
        this.getExportTask
            .post("", {"task_id":pollerData.taskId})
            .then(response => {
                if (response.data.finished == true){
                    clearInterval(this.generationInterval);
                    this._setProcessingInterval();
                    this.setState({
                        generateStatus:response.data.success
                    });
                }
            })
            .catch(err => {
                this.setState({ generateStatus: false });
            });
    };

    render() {
    const { links } = this,
          { pollerData } = this.props,
          { generateStatus, processingStatus } = this.state;
    return (
      <div>
        <ProgressBar links={links} />
        <WizardFormInstallingStatus
          statusCreating={pollerData.submitStatus}
          statusGenerating={generateStatus}
          statusProcessing={processingStatus}
          formTitle={"Finalizing Setup:"}
        />
      </div>
    );
  }
}

const mapStateToProps = ({ pollerForm }) => ({
  pollerData: pollerForm
});

const mapDispatchToProps = {};

export default connect(mapStateToProps, mapDispatchToProps)(
  RemoteServerStepThreeRoute
);
