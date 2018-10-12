import * as actions from "../actions/navigationActions";

// by default, no one menu entry is allowed
const initialState = {
  entries: []
};

const navigationReducer = (state = initialState, action) => {
  switch (action.type) {
    case actions.SET_NAVIGATION_DATA:
      return {
        ...state,
        entries: action.navigationData
      }
    default:
      return state;
  }
};

export default navigationReducer;
