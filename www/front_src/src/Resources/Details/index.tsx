import * as React from 'react';

import { isNil, isEmpty, pipe, not, defaultTo, propEq, findIndex } from 'ramda';

import { getData, useRequest, Panel } from '@centreon/ui';

import { Tab, useTheme, fade } from '@material-ui/core';

import Header from './Header';
import { ResourceDetails } from './models';
import { useResourceContext } from '../Context';
import { TabById, getVisibleTabs, TabId } from './tabs';
import { rowColorConditions } from '../colors';
import { ResourceLinks } from '../models';

export interface DetailsSectionProps {
  details?: ResourceDetails;
}

const Details = (): JSX.Element | null => {
  const theme = useTheme();
  const [details, setDetails] = React.useState<ResourceDetails>();

  const {
    openDetailsTabId,
    setOpenDetailsTabId,
    selectedDetailsLinks,
    setSelectedDetailsLinks,
    listing,
  } = useResourceContext();

  const links = selectedDetailsLinks as ResourceLinks;
  const { details: detailsEndpoint } = links.endpoints;

  const clearSelectedResource = (): void => {
    setSelectedDetailsLinks(undefined);
  };

  const { sendRequest } = useRequest<ResourceDetails>({
    request: getData,
  });

  const visibleTabs = getVisibleTabs(links);

  React.useEffect(() => {
    if (details !== undefined) {
      setDetails(undefined);
    }

    sendRequest(detailsEndpoint).then((retrievedDetails) =>
      setDetails(retrievedDetails),
    );
  }, [detailsEndpoint, listing]);

  const getTabIndex = (tabId: TabId): number => {
    return findIndex(propEq('id', tabId), visibleTabs);
  };

  const changeSelectedTabId = (tabId: TabId) => (): void => {
    setOpenDetailsTabId(tabId);
  };

  const getHeaderBackgroundColor = (): string | undefined => {
    const { downtimes, acknowledgement } = details || {};

    const foundColorCondition = rowColorConditions(theme).find(
      ({ condition }) =>
        condition({
          acknowledged: !isNil(acknowledgement),
          in_downtime: pipe(defaultTo([]), isEmpty, not)(downtimes),
        }),
    );

    if (isNil(foundColorCondition)) {
      return theme.palette.common.white;
    }

    return fade(foundColorCondition.color, 0.8);
  };

  return (
    <Panel
      header={<Header details={details} />}
      headerBackgroundColor={getHeaderBackgroundColor()}
      selectedTab={
        <TabById details={details} id={openDetailsTabId} links={links} />
      }
      selectedTabId={getTabIndex(openDetailsTabId)}
      tabs={visibleTabs.map(({ id, title }) => (
        <Tab
          disabled={isNil(details)}
          key={id}
          label={title}
          style={{ minWidth: 'unset' }}
          onClick={changeSelectedTabId(id)}
        />
      ))}
      onClose={clearSelectedResource}
    />
  );
};

export default Details;
