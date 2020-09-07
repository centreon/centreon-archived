import * as React from 'react';

import {
  isNil,
  isEmpty,
  pipe,
  not,
  defaultTo,
  propEq,
  findIndex,
  prop,
} from 'ramda';

import { getData, useRequest, Panel } from '@centreon/ui';

import { Tab, useTheme, fade } from '@material-ui/core';

import Header from './Header';
import { ResourceDetails } from './models';
import { useResourceContext } from '../Context';
import { TabById, getVisibleTabs, TabId, detailsTabId } from './tabs';
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
    selectedResourceId,
    getSelectedResourceDetailsEndpoint,
    clearSelectedResource,
    listing,
  } = useResourceContext();

  const { sendRequest } = useRequest<ResourceDetails>({
    request: getData,
  });

  const visibleTabs = getVisibleTabs(details);

  React.useEffect(() => {
    if (details !== undefined) {
      setDetails(undefined);
    }

    sendRequest(getSelectedResourceDetailsEndpoint()).then(
      (retrievedDetails) => {
        setDetails(retrievedDetails);

        const isOpenTabVisible = getVisibleTabs(details)
          .map(prop('id'))
          .includes(openDetailsTabId);

        if (!isOpenTabVisible) {
          setOpenDetailsTabId(detailsTabId);
        }
      },
    );
  }, [selectedResourceId, listing]);

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
          label={title}
          disabled={isNil(details)}
          onClick={changeSelectedTabId(id)}
        />
      ))}
      selectedTabId={getTabIndex(openDetailsTabId)}
      selectedTab={<TabById id={openDetailsTabId} details={details} />}
    />
  );
};

export default Details;
