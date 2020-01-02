import * as actions from '../actions/axiosActions';
import { ReduxState } from '.';

interface AxiosState {
  fileUploadProgress: object;
}

const initialState = {
  fileUploadProgress: {},
};

const axiosReducer = (
  state: State = initialState,
  action: object,
): ReduxState => {
  switch (action.type) {
    case actions.SET_AXIOS_DATA:
      return { ...state, [action.propKey]: action.data };
    case actions.FILE_UPLOAD_PROGRESS:
      if (action.data.reset) {
        return {
          ...state,
          fileUploadProgress: {},
        };
      }
      return {
        ...state,
        fileUploadProgress: {
          ...state.fileUploadProgress,
          ...action.data,
        },
      };

    default:
      return state;
  }
};

export default axiosReducer;
