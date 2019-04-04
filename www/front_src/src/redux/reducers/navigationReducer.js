import * as actions from "../actions/navigationActions";

// by default, no one menu entry is allowed
const initialState = {
  entries: [],
  menuItems: {}
};

const navigationReducer = (state = initialState, action) => {
  switch (action.type) {
    case actions.FETCH_NAVIGATION_SUCCESS:
      return {
        ...state,
        entries: action.entries,
        menuItems: action.menuItems
      }
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
