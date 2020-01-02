import * as actions from '../actions/externalComponentsActions.ts';

interface ExternalComponentsState {
  pages: Array;
  hooks: Array;
  fetched: boolean;
}

// by default, no one external page and hook
const initialState = {
  pages: [],
  hooks: [],
  fetched: false,
};

const externalComponentsReducer = (
  state: ExternalComponentsState = initialState,
  action: object,
): object => {
  // to be remplaced by ReduxState when when types definition will be included
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
