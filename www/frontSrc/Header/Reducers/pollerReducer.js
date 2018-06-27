import {
  REQUEST_POLLERS_STATUS,
  REQUEST_POLLERS_STATUS_SUCCESS,
  REQUEST_POLLERS_STATUS_FAIL,
  REQUEST_POLLERS_LIST,
  REQUEST_POLLERS_LIST_SUCCESS,
  REQUEST_POLLERS_LIST_FAIL,
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
    case REQUEST_POLLERS_STATUS:
      return {
        ...state,
        dataFetched: false,
        isFetching: true,
      }
    case REQUEST_POLLERS_STATUS_SUCCESS:
      return {
        ...state,
        ...action.data,
        database: {
          ...action.data.database,
          critical: {
            total: action.data.database.critical,
            message: 'All database poller updates are not active'
          },
          warning: {
            total: action.data.database.warning,
            message: 'Some database poller updates are not active'
          },
        },
        stability: {
          ...action.data.stability,
          critical: {
            total: action.data.stability.critical,
            message: 'Pollers are not running'
          },
          warning: {
            total: action.data.stability.warning,
            message: 'Some Pollers are not running'
          },
        },
        latency: {
          ...action.data.latency,
          critical: {
            total: action.data.latency.critical,
            message: 'Latency is strongly detected'
          },
          warning: {
            total: action.data.latency.warning,
            message: 'Latency is detected'
          },
        },
        dataFetched: true,
        isFetching: false,
        error: false,
        refreshTime: action.data.refreshTime * 1000,
      }
    case REQUEST_POLLERS_STATUS_FAIL:
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