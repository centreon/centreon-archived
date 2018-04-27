import {
  REQUEST_CLOCK,
  REQUEST_CLOCK_SUCCESS,
  REQUEST_CLOCK_FAIL,
  TIMER_START
} from '../Actions/clockActions'

export default function clockReducer (
  state = {
    data: {
      refreshTime: 120000,
      dataFetched: false,
      isFetching: false,
      date: null
    },
  },
  action
) {
  switch (action.type) {
    case REQUEST_CLOCK:
      return {
        ...state,
        dataFetched: false,
        isFetching: true,
      }
    case REQUEST_CLOCK_SUCCESS:
      return {
        ...state,
        ...action.data,
        dataFetched: true,
        isFetching: false,
        date: action.data.time
      }
    case REQUEST_CLOCK_FAIL:
      return {
        ...state,
        isFetching: false,
        dataFetched: false,
        status: action.error.status,
        statusText: action.error.statusText,
        error: true,
      }
    case TIMER_START:
      return {
        ...state,
        date: action.time
      }
    default:
      return state
  }
}