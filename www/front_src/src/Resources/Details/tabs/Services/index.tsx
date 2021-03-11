import * as React from 'react';

import { useTranslation } from 'react-i18next';
import { isNil, path, pathOr } from 'ramda';

import { makeStyles } from '@material-ui/core';
import GraphIcon from '@material-ui/icons/BarChart';
import ListIcon from '@material-ui/icons/List';

import { useRequest, IconButton, ListingModel } from '@centreon/ui';

import { TabProps, detailsTabId } from '..';
import { ResourceContext, useResourceContext } from '../../../Context';
import {
  labelSwitchToGraph,
  labelSwitchToList,
} from '../../../translatedLabels';
import { listResources } from '../../../Listing/api';
import { Resource } from '../../../models';
import InfiniteScroll from '../../InfiniteScroll';
import memoizeComponent from '../../../memoizedComponent';
import useTimePeriod from '../../../Graph/Performance/TimePeriods/useTimePeriod';
import TimePeriodButtonGroup from '../../../Graph/Performance/TimePeriods';

import ServiceGraphs from './Graphs';
import ServiceList from './List';
import LoadingSkeleton from './LoadingSkeleton';

const useStyles = makeStyles((theme) => ({
  services: {
    display: 'grid',
    gridGap: theme.spacing(1),
  },
  serviceDetails: {
    display: 'grid',
    gridAutoFlow: 'columns',
    gridTemplateColumns: 'auto 1fr auto',
    gridGap: theme.spacing(2),
    alignItems: 'center',
  },
  serviceCard: {
    padding: theme.spacing(1),
  },
  noResultContainer: {
    padding: theme.spacing(1),
  },
}));

type ServicesTabContentProps = TabProps &
  Pick<
    ResourceContext,
    | 'setSelectedResourceId'
    | 'setSelectedResourceType'
    | 'setSelectedResourceParentId'
    | 'setSelectedResourceParentType'
    | 'setOpenDetailsTabId'
    | 'tabParameters'
    | 'setServicesTabParameters'
  >;

const ServicesTabContent = ({
  details,
  setSelectedResourceId,
  setSelectedResourceType,
  setSelectedResourceParentId,
  setSelectedResourceParentType,
  setOpenDetailsTabId,
  tabParameters,
  setServicesTabParameters,
}: ServicesTabContentProps): JSX.Element => {
  const { t } = useTranslation();

  const [graphMode, setGraphMode] = React.useState<boolean>(
    tabParameters.services?.graphMode || false,
  );

  const [canDisplayGraphs, setCanDisplayGraphs] = React.useState(false);

  const {
    selectedTimePeriod,
    changeSelectedTimePeriod,
    periodQueryParameters,
    getIntervalDates,
    customTimePeriod,
    changeCustomTimePeriod,
  } = useTimePeriod({
    defaultSelectedTimePeriodId: path(
      ['services', 'graphTimePeriod', 'selectedTimePeriodId'],
      tabParameters,
    ),
    defaultSelectedCustomTimePeriod: path(
      ['services', 'graphTimePeriod', 'selectedCustomTimePeriod'],
      tabParameters,
    ),
    onTimePeriodChange: (graphTimePeriod) => {
      setServicesTabParameters({
        graphMode,
        graphTimePeriod,
      });
    },
  });

  const { sendRequest, sending } = useRequest({
    request: listResources,
  });

  const limit = graphMode ? 6 : 30;

  const sendListingRequest = ({
    atPage,
  }: {
    atPage?: number;
  }): Promise<ListingModel<Resource>> => {
    return sendRequest({
      limit,
      page: atPage,
      resourceTypes: ['service'],
      onlyWithPerformanceData: graphMode ? true : undefined,
      search: {
        conditions: [
          {
            field: 'h.name',
            values: {
              $eq: details?.name,
            },
          },
        ],
      },
    });
  };

  const selectService = (serviceId): void => {
    setOpenDetailsTabId(detailsTabId);
    setSelectedResourceParentType('host');
    setSelectedResourceParentId(details?.id);
    setSelectedResourceId(serviceId);
    setSelectedResourceType('service');
  };

  const switchMode = (): void => {
    setCanDisplayGraphs(false);
    const mode = !graphMode;

    setGraphMode(mode);

    setServicesTabParameters({
      graphTimePeriod: pathOr(
        {},
        ['services', 'graphTimePeriod'],
        tabParameters,
      ),
      graphMode: mode,
    });
  };

  React.useEffect(() => {
    // To make sure that graphs are not displayed until 'entities' are reset
    setCanDisplayGraphs(true);
  }, [graphMode]);

  const labelSwitch = graphMode ? labelSwitchToList : labelSwitchToGraph;
  const switchIcon = graphMode ? <ListIcon /> : <GraphIcon />;

  const loading = isNil(details) || sending;

  return (
    <>
      <IconButton
        title={t(labelSwitch)}
        ariaLabel={t(labelSwitch)}
        disabled={loading}
        onClick={switchMode}
      >
        {switchIcon}
      </IconButton>
      <InfiniteScroll<Resource>
        preventReloadWhen={details?.type === 'service'}
        sendListingRequest={sendListingRequest}
        details={details}
        loadingSkeleton={<LoadingSkeleton />}
        filter={
          graphMode ? (
            <TimePeriodButtonGroup
              selectedTimePeriodId={selectedTimePeriod?.id}
              onChange={changeSelectedTimePeriod}
              disabled={loading}
              customTimePeriod={customTimePeriod}
              changeCustomTimePeriod={changeCustomTimePeriod}
            />
          ) : undefined
        }
        reloadDependencies={[graphMode]}
        loading={sending}
        limit={limit}
      >
        {({ infiniteScrollTriggerRef, entities }): JSX.Element => {
          const displayGraphs = graphMode && canDisplayGraphs;

          return displayGraphs ? (
            <ServiceGraphs
              services={entities}
              infiniteScrollTriggerRef={infiniteScrollTriggerRef}
              periodQueryParameters={periodQueryParameters}
              getIntervalDates={getIntervalDates}
              selectedTimePeriod={selectedTimePeriod}
              customTimePeriod={customTimePeriod}
            />
          ) : (
            <ServiceList
              services={entities}
              onSelectService={selectService}
              infiniteScrollTriggerRef={infiniteScrollTriggerRef}
            />
          );
        }}
      </InfiniteScroll>
    </>
  );
};

const MemoizedServiceTabContent = memoizeComponent<ServicesTabContentProps>({
  memoProps: ['details', 'tabParameters'],
  Component: ServicesTabContent,
});

const ServicesTab = ({ details }: TabProps): JSX.Element => {
  const {
    setSelectedResourceId,
    setSelectedResourceType,
    setSelectedResourceParentId,
    setSelectedResourceParentType,
    setOpenDetailsTabId,
    tabParameters,
    setServicesTabParameters,
  } = useResourceContext();

  return (
    <MemoizedServiceTabContent
      details={details}
      tabParameters={tabParameters}
      setSelectedResourceId={setSelectedResourceId}
      setSelectedResourceType={setSelectedResourceType}
      setSelectedResourceParentId={setSelectedResourceParentId}
      setSelectedResourceParentType={setSelectedResourceParentType}
      setOpenDetailsTabId={setOpenDetailsTabId}
      setServicesTabParameters={setServicesTabParameters}
    />
  );
};

export default ServicesTab;
export { useStyles };
