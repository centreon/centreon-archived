import * as actions from '../actions/tooltipActions';

const initialState = {
  toggled: false,
  label: "",
  x: 0,
  y: 0
}

const tooltipReducer = (state = initialState, action) => {
  if (action.type === actions.UPDATE_TOOLTIP) {
    const {data} = action;
    return {...state, ...data};
  } else {
    return state;
  }
}

export default tooltipReducer;