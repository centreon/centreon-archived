import * as React from 'react';

import { useTranslation } from 'react-i18next';
import { isNil, path } from 'ramda';

import { makeStyles } from '@material-ui/core';
import GraphIcon from '@material-ui/icons/BarChart';
import ListIcon from '@material-ui/icons/List';

import { useRequest, IconButton, ListingModel } from '@centreon/ui';

import { TabProps, detailsTabId } from '..';
import { useResourceContext } from '../../../Context';
import {
  labelSwitchToGraph,
  labelSwitchToList,
} from '../../../translatedLabels';
import { listResources } from '../../../Listing/api';
import { Resource } from '../../../models';
import InfiniteScroll from '../../InfiniteScroll';
import useTimePeriod from '../../../Graph/Performance/TimePeriodSelect/useTimePeriod';
import TimePeriodSelect from '../../../Graph/Performance/TimePeriodSelect';
import { TimePeriodId } from '../Graph/models';

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

const ServicesTab = ({ details }: TabProps): JSX.Element => {
  const { t } = useTranslation();

  const {
    setSelectedResourceId,
    setSelectedResourceType,
    setSelectedResourceParentId,
    setSelectedResourceParentType,
    setOpenDetailsTabId,
    tabParameters,
    setServicesTabParameters,
  } = useResourceContext();

  const [graphMode, setGraphMode] = React.useState<boolean>(
    tabParameters.services?.graphMode || false,
  );

  const [canDisplayGraphs, setCanDisplayGraphs] = React.useState(false);

  const {
    selectedTimePeriod,
    changeSelectedTimePeriod,
    periodQueryParameters,
    getIntervalDates,
  } = useTimePeriod({
    defaultSelectedTimePeriodId: path(
      ['services', 'selectedTimePeriodId'],
      tabParameters,
    ),
    onTimePeriodChange: (timePeriodId: TimePeriodId) => {
      setServicesTabParameters({
        graphMode,
        selectedTimePeriodId: timePeriodId,
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
      graphMode: mode,
      selectedTimePeriodId: selectedTimePeriod.id,
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
            <TimePeriodSelect
              selectedTimePeriodId={selectedTimePeriod.id}
              onChange={changeSelectedTimePeriod}
              disabled={loading}
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

export default ServicesTab;
export { useStyles };
