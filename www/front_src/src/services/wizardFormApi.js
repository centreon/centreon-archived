import axios from "axios";

const wizardFormApi =
  "./api/internal.php?object=centreon_configuration_remote&action=linkCentreonRemoteServer";

export function getWizardFormData(callback) {
  axios
    .get(wizardFormApi)
    .then(res => {
      callback(res);
    })
    .catch(err => {
      throw err;
    });
}
