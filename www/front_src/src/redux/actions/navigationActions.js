/* eslint-disable no-use-before-define */
/* eslint-disable no-restricted-syntax */
import axios from 'axios';

export const FETCH_NAVIGATION_BEGIN = 'FETCH_NAVIGATION_BEGIN';
export const FETCH_NAVIGATION_SUCCESS = 'FETCH_NAVIGATION_SUCCESS';
export const FETCH_NAVIGATION_FAILURE = 'FETCH_NAVIGATION_FAILURE';

export const fetchNavigationData = () => {
  return async (dispatch) => {
    // Initiate loading state
    dispatch(fetchNavigationBegin());

    try {
      // Call the API
      const { data } = await axios.get(
        './api/internal.php?object=centreon_topology&action=navigationList',
      );

      // Update payload in reducer on success
      dispatch(fetchNavigationSuccess(data.result));
    } catch (err) {
      // Update error in reducer on failure
      dispatch(fetchNavigationFailure(err));
    }
  };
};

const fetchNavigationBegin = () => ({
  type: FETCH_NAVIGATION_BEGIN,
});

const fetchNavigationSuccess = (items) => ({
  items,
  type: FETCH_NAVIGATION_SUCCESS,
});

const fetchNavigationFailure = (error) => ({
  error,
  type: FETCH_NAVIGATION_FAILURE,
});
