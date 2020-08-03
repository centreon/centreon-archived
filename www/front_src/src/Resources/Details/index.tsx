import * as React from 'react';

import { isNil } from 'ramda';

import { getData, useRequest, SlidePanel } from '@centreon/ui';

import { Tab } from '@material-ui/core';

import Header from './Header';
import { ResourceDetails } from './models';
import { ResourceEndpoints } from '../models';
import { useResourceContext } from '../Context';
import { tabs, TabById } from './tabs';

export interface DetailsSectionProps {
  details?: ResourceDetails;
}

const Details = (): JSX.Element | null => {
  const [details, setDetails] = React.useState<ResourceDetails>();

  const {
    detailsTabIdToOpen,
    setDefaultDetailsTabIdToOpen,
    selectedDetailsEndpoints,
    setSelectedDetailsEndpoints,
    listing,
  } = useResourceContext();

  const {
    details: detailsEndpoint,
  } = selectedDetailsEndpoints as ResourceEndpoints;

  const clearSelectedResource = (): void => {
    setSelectedDetailsEndpoints(null);
  };

  const { sendRequest } = useRequest<ResourceDetails>({
    request: getData,
  });

  React.useEffect(() => {
    if (details !== undefined) {
      setDetails(undefined);
    }

    sendRequest(detailsEndpoint).then((retrievedDetails) =>
      setDetails(retrievedDetails),
    );
  }, [detailsEndpoint, listing]);

  const changeSelectedTabId = (_, id): void => {
    setDefaultDetailsTabIdToOpen(id);
  };

  return (
    <SlidePanel
      header={<Header details={details} onClickClose={clearSelectedResource} />}
      tabs={tabs
        .filter(({ visible }) => visible(selectedDetailsEndpoints))
        .map(({ id, title }) => (
          <Tab key={id} label={title} disabled={isNil(details)} />
        ))}
      selectedTabId={detailsTabIdToOpen}
      onTabSelect={changeSelectedTabId}
      selectedTab={
        <TabById
          id={detailsTabIdToOpen}
          details={details}
          endpoints={selectedDetailsEndpoints as ResourceEndpoints}
        />
      }
    />
  );
};

export default Details;
