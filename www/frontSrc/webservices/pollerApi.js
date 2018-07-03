import axios from "axios"
import {
  requestPollersStatus,
  requestPollersStatusSuccess,
  requestPollersStatusFail,
  requestPollersListIssues,
  requestPollersListIssuesSuccess,
  requestPollersListIssuesFail
} from '../Header/Actions/pollerActions'

const pollerStatusUrl = './api/internal.php?object=centreon_topcounter&action=pollersStatus'
const pollersListUrl = './api/internal.php?object=centreon_topcounter&action=pollersList'
const pollersListIssuesUrl = './api/internal.php?object=centreon_topcounter&action=pollersListIssues'

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

export function getPollersListIssues() {
  return (dispatch) => {
    dispatch(requestPollersListIssues())
    return axios.get(pollersListIssuesUrl)
      .then(
        res => {
          dispatch(requestPollersListIssuesSuccess(res))
        }
      )
      .catch(
        err => {
          dispatch(requestPollersListIssuesFail(err))
        }
      )
  }
}