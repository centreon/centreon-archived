import React, { useState } from 'react';

function BreadcrumbWrapper({ path, children }) {

  /*
  const navigationSelector = (state) => state.navigation;
  const breadcrumbSelector = createSelector(
    navigationSelector,
    items => items.reduce((acc, item) => acc + item.value, 0)
  )
  */

  return (
    <>
      {children}
    </>
  );
}

export default BreadcrumbWrapper;