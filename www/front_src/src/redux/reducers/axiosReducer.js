import * as actions from '../actions/axiosActions';

const initialState = {};

const axiosReducer = (state = initialState, action) => {
  switch (action.type) {
    case actions.SET_AXIOS_DATA:
      return {...state, [action.propKey]:action.data};
    default:
      return state;
  }
};

export default axiosReducer;