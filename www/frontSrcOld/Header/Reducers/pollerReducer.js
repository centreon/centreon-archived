import {
  REQUEST_POLLERS_STATUS,
  REQUEST_POLLERS_STATUS_SUCCESS,
  REQUEST_POLLERS_STATUS_FAIL,
  REQUEST_POLLERS_LIST_ISSUES,
  REQUEST_POLLERS_LIST_ISSUES_SUCCESS,
  REQUEST_POLLERS_LIST_ISSUES_FAIL,
} from '../Actions/pollerActions'

export default function pollerReducer (
  state = {
    dataFetched: false,
    isFetching: false,
    error: null,
    refreshTime: 30000
  },action
) {
  switch (action.type) {
    case REQUEST_POLLERS_LIST_ISSUES:
      return {
        ...state,
        dataFetched: false,
        isFetching: true,
      }
    case REQUEST_POLLERS_LIST_ISSUES_SUCCESS:

      return {
        ...state,
        ...action.data,
        dataFetched: true,
        isFetching: false,
        error: false,
        refreshTime: action.data.refreshTime * 1000,
      }

    case REQUEST_POLLERS_LIST_ISSUES_FAIL:

      return {
        ...state,
        isFetching: false,
        dataFetched: false,
        status: action.error.status,
        statusText: action.error.statusText,
        error: true,
      }
    default:
      return state
  }
}