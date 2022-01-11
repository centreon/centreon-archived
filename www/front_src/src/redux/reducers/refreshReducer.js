/* eslint-disable default-param-last */

import * as actions from '../actions/refreshActions';

const refreshReducer = (state = {}, action) => {
  switch (action.type) {
    case actions.SET_REFRESH_INTERVALS:
      return { ...state, ...action.intervals };
    default:
      return state;
  }
};

export default refreshReducer;
