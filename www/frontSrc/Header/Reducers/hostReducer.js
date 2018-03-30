import {
  REQUEST_HOSTS,
  REQUEST_HOSTS_SUCCESS,
  REQUEST_HOSTS_FAIL,
} from '../Actions/hostActions'

export default function hostReducer (
  state = {
    dataFetched: false,
    isFetching: false,
    error: null,
    refreshTime: 30000
  },action) {
  switch (action.type) {
    case REQUEST_HOSTS:
      return {
        ...state,
        dataFetched: false,
        isFetching: true,
      }
    case REQUEST_HOSTS_SUCCESS:
      return {
        ...state,
          ...action.data,
          url: './main.php?p=20202&o=h&search=',
          down: {
            ...action.data.down,
            classe: 'error',
            url: './main.php?p=20202&o=h_down&search='
          },
          unreachable: {
            ...action.data.unreachable,
            classe: 'unreachable',
            url: './main.php?p=20202&o=h_unreachable&search='
          },
          ok: {
            total: action.data.ok,
            classe: 'success',
            url: './main.php?p=20202&o=h_up&search='
          },
          pending: {
            total: action.data.pending,
            classe: 'pending',
            url: './main.php?p=20202&o=h_pending&search='
          },
        dataFetched: true,
        isFetching: false,
        error: false,
        refreshTime: action.data.refreshTime * 1000,
        }
    case REQUEST_HOSTS_FAIL:
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