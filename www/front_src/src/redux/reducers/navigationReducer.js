import * as actions from "../actions/navigationActions";

// by default, no one menu entry is allowed
const initialState = {
  items: [],
};

const navigationReducer = (state = initialState, action) => {
  switch (action.type) {
    case actions.FETCH_NAVIGATION_SUCCESS:
      return {
        ...state,
        items: action.items,
      };
    // navigated to another URL
    case "@@router/LOCATION_CHANGE":
      const event = document.createEvent('CustomEvent');
      event.initCustomEvent('react.href.update', false, false, { href: window.location.href });
      window.dispatchEvent(event);
      return state;
    default:
      return state;
  }
};

export default navigationReducer;
