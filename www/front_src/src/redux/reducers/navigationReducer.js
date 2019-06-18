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
        menuItems: action.menuItems
      };
    case actions.FETCH_REACT_ROUTES_SUCCESS:
      return {
        ...state,
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
    // navigated to another URL
    case "@@router/LOCATION_CHANGE":
        let event = new CustomEvent('react.href.update', {
          detail: {
            href: window.location.href
          }
        });
        window.dispatchEvent(event);
      return state;
    default:
      return state;
  }
};

export default navigationReducer;
