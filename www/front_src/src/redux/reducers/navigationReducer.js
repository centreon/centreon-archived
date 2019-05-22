import * as actions from "../actions/navigationActions";

// by default, no one menu entry is allowed
const initialState = {
  entries: [],
  menuItems: [],
  acl: {
    routes: [],
    loaded: false
  }
};

const navigationReducer = (state = initialState, action) => {
  switch (action.type) {
    case actions.FETCH_NAVIGATION_SUCCESS:
      return {
        ...state,
        entries: action.entries,
        menuItems: action.menuItems,
        reactRoutes: action.reactRoutes
      };
    case actions.SET_NAVIGATION_DATA:
      return {
        ...state,
        entries: action.navigationData
      };
    case actions.FETCH_ACL_ROUTES_SUCCESS:
      return {
        ...state,
        acl: {
          routes: action.data,
          loaded: true
        }
      };
    case actions.FETCH_ACL_ROUTES_FAILURE:
      return {
        ...state,
        acl: {
          routes: [],
          loaded: true
        }
      };
    default:
      return state;
  }
};

export default navigationReducer;
