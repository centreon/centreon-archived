import * as actions from '../actions/externalComponentsActions';

// by default, no one external page and hook
const initialState = {
  pages: [],
  hooks: [],
  fetched: false,
};

const externalComponentsReducer = (state = initialState, action) => {
  switch (action.type) {
    case actions.FETCH_EXTERNAL_COMPONENTS_SUCCESS:
      return {
        ...state,
        pages: action.data.pages,
        hooks: action.data.hooks,
        fetched: true,
      };
    default:
      return state;
  }
};

export default externalComponentsReducer;
