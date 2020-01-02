import * as actions from '../actions/refreshActions';
import { ReduxState } from '.';

const initialState = {};

const refreshReducer = (
  state: object = initialState,
  action: object,
): ReduxState => {
  switch (action.type) {
    case actions.SET_REFRESH_INTERVALS:
      return { ...state, ...action.intervals };
    default:
      return state;
  }
};

export default refreshReducer;
