import axios from 'axios'
import { requestUser, requestUserSuccess, requestUserFail } from '../Header/Actions/UserActions'

const userUrl = 'http://10.30.2.138/centreon/api/user.json'

export function getUser() {
  return (dispatch) => {
    dispatch(requestUser())
  }
  return axios.get(
    userUrl
  )
    .then(
      res => {
        dispatch(requestUserSuccess(res))
      }
    )
    .catch(
      err => {
        dispatch(requestUserFail(err))
      }
    )
}