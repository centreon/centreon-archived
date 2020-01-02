import * as actions from '../actions/refreshActions.ts';

const initialState = {};

const refreshReducer = (
  state: object = initialState,
  action: object,
): object => {
  // to be remplaced by ReduxState when when types definition will be included
  switch (action.type) {
    case actions.SET_REFRESH_INTERVALS:
      return { ...state, ...action.intervals };
    default:
      return state;
  }
};

export default refreshReducer;
