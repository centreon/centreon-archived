import * as React from 'react';

import { useTranslation } from 'react-i18next';

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
  } = useResourceContext();

  const [graphMode, setGraphMode] = React.useState<boolean>(false);

  const {
    selectedTimePeriod,
    changeSelectedTimePeriod,
    periodQueryParameters,
  } = useTimePeriod();

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

  const labelSwitch = graphMode ? labelSwitchToList : labelSwitchToGraph;
  const switchIcon = graphMode ? <ListIcon /> : <GraphIcon />;

  return (
    <>
      <IconButton
        title={t(labelSwitch)}
        ariaLabel={t(labelSwitch)}
        onClick={(): void => {
          setGraphMode(!graphMode);
        }}
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
            />
          ) : undefined
        }
        reloadDependencies={[]}
        loading={sending}
        limit={limit}
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
