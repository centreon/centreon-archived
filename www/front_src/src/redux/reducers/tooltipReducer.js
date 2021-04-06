import * as actions from '../actions/tooltipActions';

const initialState = {
  label: '',
  toggled: false,
  x: 0,
  y: 0,
};

const tooltipReducer = (state = initialState, action) => {
  const { data } = action;
  switch (action.type) {
    case actions.UPDATE_TOOLTIP:
      return {
        ...state,
        ...data,
      };
    default:
      return state;
  }
};

export default tooltipReducer;
