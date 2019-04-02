export const SET_NAVIGATION_DATA = "SET_NAVIGATION_DATA";
export const GET_NAVIGATION_DATA = "GET_NAVIGATION_DATA";

export const setNavigation = data => {

  // store allowed topologies in an array
  // eg : ["3","301","30102","6","602"]
  let navigationData = []
  for (let [levelOneKey, levelOneProps] of Object.entries(data)) {
    navigationData.push(levelOneKey.slice(1))
    for (let [levelTwoKey, levelTwoProps] of Object.entries(levelOneProps.children)) {
      navigationData.push(levelTwoKey.slice(1))
      for (let levelThreeProps of Object.values(levelTwoProps.children)) {
        for (let levelFourKey of Object.keys(levelThreeProps)) {
          navigationData.push(levelFourKey.slice(1))
        }
      }
    }
  }

  return {
    type: SET_NAVIGATION_DATA,
    navigationData
  }
};
