import axios from "axios"
import { requestNavItems, requestNavItemsSuccess, requestNavItemsFail } from '../Header/Actions/navActions'

const navUrl = './api/internal.php?object=centreon_menu&action=menu'

export function getNavItems() {
  return (dispatch) => {
    dispatch(requestNavItems())

    return axios.get(
      navUrl
    )
      .then(
        res => {
          dispatch(requestNavItemsSuccess(res))
        }
      )
      .catch(
        err => {
          dispatch(requestNavItemsFail(err))
        }
      )
  }
}