import * as React from 'react';

import { isNil } from 'ramda';
import { useAtomValue } from 'jotai/utils';

import { ListingPage, useMemoComponent, WithPanel } from '@centreon/ui';

import Context from './Context';
import Filter from './Filter';
import Listing from './Listing';
import Details from './Details';
import useFilter from './Filter/useFilter';
import EditFiltersPanel from './Filter/Edit';
import { selectedResourceIdAtom } from './Details/detailsAtoms';
import useDetails from './Details/useDetails';
import { editPanelOpenAtom } from './Filter/filterAtoms';

const ResourcesPage = (): JSX.Element => {
  const selectedResourceId = useAtomValue(selectedResourceIdAtom);
  const editPanelOpen = useAtomValue(editPanelOpenAtom);

  return useMemoComponent({
    Component: (
      <WithPanel open={editPanelOpen} panel={<EditFiltersPanel />}>
        <ListingPage
          filter={<Filter />}
          listing={<Listing />}
          panel={<Details />}
          panelOpen={!isNil(selectedResourceId)}
        />
      </WithPanel>
    ),
    memoProps: [selectedResourceId, editPanelOpen],
  });
};

const Resources = (): JSX.Element => {
  const filterContext = useFilter();

  useDetails();

  return (
    <Context.Provider
      value={{
        ...filterContext,
      }}
    >
      <ResourcesPage />
    </Context.Provider>
  );
};

export default Resources;
