import axios from "axios"
import { requestPollers, requestPollersSuccess, requestPollersFail } from '../Header/Actions/pollerActions'

const pollerUrl = './api/internal.php?object=centreon_topcounter&action=pollers_status'

export function getPollers() {
    return (dispatch) => {
      dispatch(requestPollers())
      return axios.get(pollerUrl)
        .then(
          res => {
            dispatch(requestPollersSuccess(res))
          }
        )
        .catch(
          err => {
            dispatch(requestPollersFail(err))
          }
        )
    }
}