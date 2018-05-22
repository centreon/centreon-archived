import {
  REQUEST_CLOCK,
  REQUEST_CLOCK_SUCCESS,
  REQUEST_CLOCK_FAIL,
} from '../Actions/clockActions'

export default function clockReducer (
  state = {
    data: {}
  },
  action
) {
  switch (action.type) {
    case REQUEST_CLOCK:
      return {
        ...state
      }
    case REQUEST_CLOCK_SUCCESS:
      return {
        ...state,
        data: action.data
      }
    default:
      return state
  }
}