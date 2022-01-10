/* eslint-disable default-param-last */
/* eslint-disable no-case-declarations */

import * as actions from '../actions/navigationActions';

// by default, no one menu entry is allowed
const initialState = {
  fetched: false,
  items: undefined,
};

const navigationReducer = (state = initialState, action) => {
  switch (action.type) {
    case actions.FETCH_NAVIGATION_SUCCESS:
      return {
        ...state,
        fetched: true,
        items: action.items,
      };
    default:
      return state;
  }
};

export default navigationReducer;
