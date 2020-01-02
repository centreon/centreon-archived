/* eslint-disable import/prefer-default-export */

import axios from 'axios';

const wizardFormApi =
  './api/internal.php?object=centreon_configuration_remote&action=linkCentreonRemoteServer';

export function getWizardFormData(callback: Function) {
  axios
    .get(wizardFormApi)
    .then((res) => {
      callback(res);
    })
    .catch((err: Error) => {
      throw err;
    });
}
