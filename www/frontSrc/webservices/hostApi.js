import axios from "axios"
import { requestHosts, requestHostsSuccess, requestHostsFail } from '../Header/Actions/hostActions'

const hostUrl = './api/internal.php?object=centreon_topcounter&action=hosts_status'

export function getHosts() {
  return (dispatch) => {
    dispatch(requestHosts())

    return axios.get(
      hostUrl
    )
      .then(
        res => {
          dispatch(requestHostsSuccess(res))
        }
      )
      .catch(
        err => {
          dispatch(requestHostsFail(err))
        }
      )
  }
}