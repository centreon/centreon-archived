export const REQUEST_NAV_ITEMS = 'REQUEST_NAV_ITEMS'
export const REQUEST_NAV_ITEMS_SUCCESS = 'REQUEST_NAV_ITEMS_SUCCESS'
export const REQUEST_NAV_ITEMS_FAIL = 'REQUEST_NAV_ITEMS_FAIL'

export function requestNavItems () {
  return {
    type: REQUEST_NAV_ITEMS,
  }
}

export function requestNavItemsSuccess (res) {
  return {
    type: REQUEST_NAV_ITEMS_SUCCESS,
    data: res.data,
  }
}

export function requestNavItemsFail (err) {
  return {
    type: REQUEST_NAV_ITEMS_FAIL,
    error: err.response,
  }
}