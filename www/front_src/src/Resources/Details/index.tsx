import * as React from 'react';

import { isNil, isEmpty, pipe, not, defaultTo } from 'ramda';

import { getData, useRequest, Panel } from '@centreon/ui';

import { Tab, useTheme, fade } from '@material-ui/core';

import Header from './Header';
import { ResourceDetails } from './models';
import { ResourceEndpoints } from '../models';
import { useResourceContext } from '../Context';
import { tabs, TabById } from './tabs';
import { rowColorConditions } from '../colors';

export interface DetailsSectionProps {
  details?: ResourceDetails;
}

const Details = (): JSX.Element | null => {
  const theme = useTheme();
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

  const getHeaderBackgroundColor = (): string | undefined => {
    const { downtimes, acknowledgement } = details || {};

    const foundColorCondition = rowColorConditions(theme).find(
      ({ condition }) =>
        condition({
          in_downtime: pipe(defaultTo([]), isEmpty, not)(downtimes),
          acknowledged: !isNil(acknowledgement),
        }),
    );

    if (isNil(foundColorCondition)) {
      return theme.palette.common.white;
    }

    return fade(foundColorCondition.color || '#nnn', 0.8);
  };

  return (
    <Panel
      onClose={clearSelectedResource}
      header={<Header details={details} />}
      headerBackgroundColor={getHeaderBackgroundColor()}
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
