import axios from "axios"
import { requestServices, requestServicesSuccess, requestServicesFail } from '../Header/Actions/serviceActions'

const serviceUrl = './api/internal.php?object=centreon_topcounter&action=servicesStatus'

export function getServices() {
  return (dispatch) => {
    dispatch(requestServices())

    return axios.get(
      serviceUrl
    )
      .then(
        res => {
          dispatch(requestServicesSuccess(res))
        }
      )
      .catch(
        err => {
          dispatch(requestServicesFail(err))
        }
      )
  }
}