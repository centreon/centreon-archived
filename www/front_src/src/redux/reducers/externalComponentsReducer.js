/* eslint-disable default-param-last */

import * as actions from '../actions/externalComponentsActions';

const initialState = {
  fetched: false,
  hooks: {},
  pages: {},
};

const externalComponentsReducer = (state = initialState, action) => {
  switch (action.type) {
    case actions.FETCH_EXTERNAL_COMPONENTS_SUCCESS:
      return {
        ...state,
        fetched: true,
        hooks: action.data.hooks,
        pages: action.data.pages,
      };
    default:
      return state;
  }
};

export default externalComponentsReducer;
