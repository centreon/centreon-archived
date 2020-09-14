import * as React from 'react';

import { isNil, isEmpty, pipe, not, defaultTo, propEq, findIndex } from 'ramda';
import { useTranslation } from 'react-i18next';

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
  const { t } = useTranslation();
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

  const visibleTabs = getVisibleTabs(selectedDetailsLinks);

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
          in_downtime: pipe(defaultTo([]), isEmpty, not)(downtimes),
          acknowledged: !isNil(acknowledgement),
        }),
    );

    if (isNil(foundColorCondition)) {
      return theme.palette.common.white;
    }

    return fade(foundColorCondition.color, 0.8);
  };

  return (
    <Panel
      onClose={clearSelectedResource}
      header={<Header details={details} />}
      headerBackgroundColor={getHeaderBackgroundColor()}
      tabs={visibleTabs.map(({ id, title }) => (
        <Tab
          style={{ minWidth: 'unset' }}
          key={id}
          label={t(title)}
          disabled={isNil(details)}
          onClick={changeSelectedTabId(id)}
        />
      ))}
      selectedTabId={getTabIndex(openDetailsTabId)}
      selectedTab={
        <TabById id={openDetailsTabId} details={details} links={links} />
      }
    />
  );
};

export default Details;
