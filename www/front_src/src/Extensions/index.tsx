import { lazy } from 'react';

import { ListingPage, useMemoComponent } from '@centreon/ui';

const Filter = lazy(() => import('./Filter'));
const Listing = lazy(() => import('./Listing'));

const Extensions = (): JSX.Element => {
  return useMemoComponent({
    Component: <ListingPage filter={<Filter />} listing={<Listing />} />,
    memoProps: []
  });
};

export default Extensions;
