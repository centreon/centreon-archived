import axios from "axios";

const navUrl = "/_CENTREON_PATH_PLACEHOLDER_/api/internal.php?object=centreon_menu&action=menu";

export function getNavItems(callback) {
  axios
    .get(navUrl)
    .then(res => {
      callback(res);
    })
    .catch(err => {
      throw err;
    });
}
