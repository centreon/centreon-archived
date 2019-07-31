import * as actions from '../actions/refreshActions';

const initialState = {};

const refreshReducer = (state = initialState, action) => {
  switch (action.type) {
    case actions.SET_REFRESH_INTERVALS:
      return { ...state, ...action.intervals };
    default:
      return state;
  }
};

export default refreshReducer;
