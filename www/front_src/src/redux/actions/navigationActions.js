import axios from "../../axios";

export const FETCH_NAVIGATION_BEGIN = "FETCH_NAVIGATION_BEGIN";
export const FETCH_NAVIGATION_SUCCESS = "FETCH_NAVIGATION_SUCCESS";
export const FETCH_NAVIGATION_FAILURE = "FETCH_NAVIGATION_FAILURE";

export const FETCH_REACT_ROUTES_BEGIN = "FETCH_REACT_ROUTES_BEGIN";
export const FETCH_REACT_ROUTES_SUCCESS = "FETCH_REACT_ROUTES_SUCCESS";
export const FETCH_REACT_ROUTES_FAILURE = "FETCH_REACT_ROUTES_FAILURE";

export const fetchReactRoutesData = () => {
  return async dispatch => {
    try {
      const { data } = await axios(
        "internal.php?object=centreon_topology&action=menulist&reactOnly=1&forActive=1"
      ).get();

      dispatch(
        fetchReactRoutesSuccess(data.result)
      );
    } catch (err) {
      console.error(err);
    }
  };
};

const fetchReactRoutesSuccess = reactRoutes => ({
  type: FETCH_REACT_ROUTES_SUCCESS,
  reactRoutes
});

export const fetchNavigationData = () => {
  return async dispatch => {
    // Initiate loading state
    dispatch(fetchNavigationBegin());

    try {
      // Call the API
      const { data } = await axios(
        "internal.php?object=centreon_topology&action=menulist"
      ).get();

      let pageIds = [];
      for(let levelOne of data){
        pageIds.push(levelOne.page);
        for(let levelTwo of levelOne.children){
          pageIds.push(levelTwo.page);
          for(let group of levelTwo.groups){
            for(let levelThree of group.children){
              pageIds.push(levelThree.page)
            }
          }
        }
      }

      // Update payload in reducer on success
      dispatch(fetchNavigationSuccess(pageIds, data.result));
    } catch (err) {
      // Update error in reducer on failure
      dispatch(fetchNavigationFailure(err));
    }
  };
};

const fetchNavigationBegin = () => ({
  type: FETCH_NAVIGATION_BEGIN
});

const fetchNavigationSuccess = (entries, menuItems) => ({
  type: FETCH_NAVIGATION_SUCCESS,
  entries,
  menuItems
});

const fetchNavigationFailure = error => ({
  type: FETCH_NAVIGATION_FAILURE,
  error
});

export const SET_NAVIGATION_DATA = "SET_NAVIGATION_DATA";
export const GET_NAVIGATION_DATA = "GET_NAVIGATION_DATA";

export const setNavigation = data => {
  // store allowed topologies in an array
  // eg : ["3","301","30102","6","602"]
  let navigationData = [];
  for (let [levelOneKey, levelOneProps] of Object.entries(data)) {
    navigationData.push(levelOneKey.slice(1));
    for (let [levelTwoKey, levelTwoProps] of Object.entries(
      levelOneProps.children
    )) {
      navigationData.push(levelTwoKey.slice(1));
      for (let levelThreeProps of Object.values(levelTwoProps.children)) {
        for (let levelFourKey of Object.keys(levelThreeProps)) {
          navigationData.push(levelFourKey.slice(1));
        }
      }
    }
  }

  return {
    type: SET_NAVIGATION_DATA,
    navigationData
  };
};

/**
 * Manage acl routes
 */

export const FETCH_ACL_ROUTES_BEGIN = "FETCH_ACL_ROUTES_BEGIN";
export const FETCH_ACL_ROUTES_SUCCESS = "FETCH_ACL_ROUTES_SUCCESS";
export const FETCH_ACL_ROUTES_FAILURE = "FETCH_ACL_ROUTES_FAILURE";

export const fetchAclRoutes = () => {
  return async dispatch => {
    // Initiate loading state
    dispatch(fetchAclRoutesBegin());

    try {
      // Call the API
      const { data } = await axios(
        "internal.php?object=centreon_acl_webservice&action=getCurrentAcl"
      ).get();

      // Update payload in reducer on success
      dispatch(fetchAclRoutesSuccess(data));
    } catch (err) {
      // Update error in reducer on failure
      dispatch(fetchAclRoutesFailure(err));
    }
  };
};

const fetchAclRoutesBegin = () => ({
  type: FETCH_ACL_ROUTES_BEGIN
});

const fetchAclRoutesSuccess = data => ({
  type: FETCH_ACL_ROUTES_SUCCESS,
  data
});

const fetchAclRoutesFailure = error => ({
  type: FETCH_ACL_ROUTES_FAILURE,
  error
});
