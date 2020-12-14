import * as React from 'react';

import { isNil, isEmpty, always, ifElse, equals } from 'ramda';
import { useTranslation } from 'react-i18next';

import { makeStyles, Paper, Typography, List } from '@material-ui/core';
import GraphIcon from '@material-ui/icons/BarChart';
import ListIcon from '@material-ui/icons/List';

import { useRequest, IconButton } from '@centreon/ui';

import { TabProps, detailsTabId } from '..';
import { useResourceContext } from '../../../Context';
import {
  labelNoResultsFound,
  labelSwitchToGraph,
  labelSwitchToList,
} from '../../../translatedLabels';
import { listResources } from '../../../Listing/api';
import { Resource } from '../../../models';
import InfiniteScroll from '../../InfiniteScroll';
import useTimePeriod from '../../../Graph/Performance/TimePeriodSelect/useTimePeriod';
import TimePeriodSelect from '../../../Graph/Performance/TimePeriodSelect';

import ServiceGraphs from './Graphs';
import ServiceList from './List';
import Listing from './Listing';
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
  const classes = useStyles();
  const { t } = useTranslation();

  const {
    setSelectedResourceId,
    setSelectedResourceType,
    setSelectedResourceParentId,
    setSelectedResourceParentType,
    setOpenDetailsTabId,
    selectedResourceId,
  } = useResourceContext();

  const [services, setServices] = React.useState<Array<Resource>>();
  const [graphMode, setGraphMode] = React.useState<boolean>(false);

  const {
    selectedTimePeriod,
    changeSelectedTimePeriod,
    periodQueryParameters,
  } = useTimePeriod();

  const { sendRequest, sending } = useRequest({
    request: listResources,
  });

  const sendListingRequest = ({ atPage }) => {
    return sendRequest({
      limit: 100,
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

  React.useEffect(() => {
    if (selectedResourceId !== details?.id) {
      setServices(undefined);
    }
  }, [selectedResourceId]);

  const selectService = (serviceId): void => {
    setOpenDetailsTabId(detailsTabId);
    setSelectedResourceParentType('host');
    setSelectedResourceParentId(details?.id);
    setSelectedResourceId(serviceId);
    setSelectedResourceType('service');
  };

  return (
    <>
      <IconButton
        title={t(graphMode ? labelSwitchToGraph : labelSwitchToList)}
        ariaLabel={t(graphMode ? labelSwitchToGraph : labelSwitchToList)}
        onClick={(): void => {
          setGraphMode(!graphMode);
        }}
      >
        {graphMode ? <GraphIcon /> : <ListIcon />}
      </IconButton>
      <InfiniteScroll
        sendListingRequest={sendListingRequest}
        details={details}
        loadingSkeleton={<LoadingSkeleton />}
        filter={
          graphMode ? (
            <TimePeriodSelect
              selectedTimePeriodId={selectedTimePeriod.id}
              onChange={changeSelectedTimePeriod}
            />
          ) : undefined
        }
        reloadDependencies={[]}
        loading={sending}
        limit={2}
      >
        {({ infiniteScrollTriggerRef, entities }): JSX.Element => {
          return graphMode ? (
            <ServiceGraphs
              services={entities}
              infiniteScrollTriggerRef={infiniteScrollTriggerRef}
              periodQueryParameters={periodQueryParameters}
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
