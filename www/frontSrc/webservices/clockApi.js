import axios from 'axios'
import { requestClock, requestClockSuccess, requestClockFail } from '../Header/Actions/clockActions'

const clockUrl = './api/internal.php?object=centreon_topcounter&action=clock'

export function getClock() {
  return (dispatch) => {
    dispatch(requestClock())

    return axios.get(
      clockUrl
    )
      .then(
        res => {
          dispatch(requestClockSuccess(res))
        }
      )
      .catch(
        err => {
          dispatch(requestClockFail(err))
        }
      )
  }
}