import axios from "axios"
import {
  requestPollersStatus,
  requestPollersStatusSuccess,
  requestPollersStatusFail,
  requestPollersListIssues,
  requestPollersListIssuesSuccess,
  requestPollersListIssuesFail
} from '../Header/Actions/pollerActions'

const pollersListIssuesUrl = './api/internal.php?object=centreon_topcounter&action=pollersListIssues'


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