import {
  REQUEST_USER,
  REQUEST_USER_SUCCESS,
  REQUEST_USER_FAIL,
} from '../Actions/UserActions'

export default function authReducer (
  state = {
    data: {}
  },
  action
) {
  switch (action.type) {
    case REQUEST_USER:
      return {
        ...state
      }
    case REQUEST_USER_SUCCESS:
      console.log(action)
      return {
        ...state,
        data: action.data
      }
    default:
      return state
  }
}