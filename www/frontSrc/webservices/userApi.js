import axios from 'axios'
import { requestUser, requestUserSuccess, requestUserFail } from '../Header/Actions/userActions'

const userUrl = './api/internal.php?object=centreon_topcounter&action=user'

export function getUser() {
  return (dispatch) => {
    dispatch(requestUser())

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
}