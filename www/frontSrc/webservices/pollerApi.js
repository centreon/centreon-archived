import axios from "axios"
import {
  requestPollersStatus,
  requestPollersStatusSuccess,
  requestPollersStatusFail,
  requestPollersList,
  requestPollersListSuccess,
  requestPollersListFail
} from '../Header/Actions/pollerActions'

const pollerStatusUrl = './api/internal.php?object=centreon_topcounter&action=pollersStatus'
const pollersListUrl = './api/internal.php?object=centreon_topcounter&action=pollersList'

export function getPollersStatus() {
    return (dispatch) => {
      dispatch(requestPollersStatus())
      return axios.get(pollerStatusUrl)
        .then(
          res => {
            dispatch(requestPollersStatusSuccess(res))
          }
        )
        .catch(
          err => {
            dispatch(requestPollersStatusFail(err))
          }
        )
    }
}

export function getPollersList() {
  return (dispatch) => {
    dispatch(requestPollersList())
    return axios.get(pollersListUrl)
      .then(
        res => {
          dispatch(requestPollersListSuccess(res))
        }
      )
      .catch(
        err => {
          dispatch(requestPollersListFail(err))
        }
      )
  }
}