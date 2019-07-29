/* eslint-disable import/prefer-default-export */

import axios from 'axios';

const navUrl = './api/internal.php?object=centreon_menu&action=menu';

export function getNavItems(callback) {
  axios
    .get(navUrl)
    .then((res) => {
      callback(res);
    })
    .catch((err) => {
      throw err;
    });
}
