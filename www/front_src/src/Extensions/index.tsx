import * as React from 'react';

import { ListingPage, useMemoComponent } from '@centreon/ui';

const Filter = React.lazy(() => import('./Filter'));
const Listing = React.lazy(() => import('./Listing'));

const Extensions = (): JSX.Element => {
  return useMemoComponent({
    Component: <ListingPage filter={<Filter />} listing={<Listing />} />,
    memoProps: [],
  });
};

export default Extensions;
