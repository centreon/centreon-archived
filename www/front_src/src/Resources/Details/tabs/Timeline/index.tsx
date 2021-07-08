import * as React from 'react';

import { useTranslation } from 'react-i18next';
import { prop, isEmpty, path } from 'ramda';

import { makeStyles, Paper } from '@material-ui/core';

import {
  useRequest,
  ListingModel,
  MultiAutocompleteField,
  SearchParameter,
} from '@centreon/ui';

import { labelEvent } from '../../../translatedLabels';
import { TabProps } from '..';
import InfiniteScroll from '../../InfiniteScroll';

import { types } from './Event';
import { TimelineEvent, Type } from './models';
import { listTimelineEventsDecoder } from './api/decoders';
import { listTimelineEvents } from './api';
import Events from './Events';
import LoadingSkeleton from './LoadingSkeleton';

type TimelineListing = ListingModel<TimelineEvent>;

const useStyles = makeStyles((theme) => ({
  filter: {
    padding: theme.spacing(2),
  },
}));

const TimelineTab = ({ details }: TabProps): JSX.Element => {
  const classes = useStyles();
  const { t } = useTranslation();

  const translatedTypes = types.map((type) => ({
    ...type,
    name: t(type.name),
  })) as Array<Type>;

  const [selectedTypes, setSelectedTypes] =
    React.useState<Array<Type>>(translatedTypes);
  const limit = 30;

  const { sendRequest, sending } = useRequest<TimelineListing>({
    decoder: listTimelineEventsDecoder,
    request: listTimelineEvents,
  });

  const getSearch = (): SearchParameter | undefined => {
    if (isEmpty(selectedTypes)) {
      return undefined;
    }

    return {
      lists: [
        {
          field: 'type',
          values: selectedTypes.map(prop('id')),
        },
      ],
    };
  };

  const timelineEndpoint = path(['links', 'endpoints', 'timeline'], details);

  const listTimeline = ({
    atPage,
  }: {
    atPage?: number;
  }): Promise<TimelineListing> => {
    return sendRequest({
      endpoint: timelineEndpoint,
      parameters: {
        limit,
        page: atPage,
        search: getSearch(),
      },
    });
  };

  const changeSelectedTypes = (_, typeIds): void => {
    setSelectedTypes(typeIds);
  };

  return (
    <InfiniteScroll
      details={details}
      filter={
        <Paper className={classes.filter}>
          <MultiAutocompleteField
            fullWidth
            label={t(labelEvent)}
            limitTags={3}
            options={translatedTypes}
            value={selectedTypes}
            onChange={changeSelectedTypes}
          />
        </Paper>
      }
      limit={limit}
      loading={sending}
      loadingSkeleton={<LoadingSkeleton />}
      reloadDependencies={[selectedTypes]}
      sendListingRequest={listTimeline}
    >
      {({ infiniteScrollTriggerRef, entities }): JSX.Element => {
        return (
          <Events
            infiniteScrollTriggerRef={infiniteScrollTriggerRef}
            timeline={entities}
          />
        );
      }}
    </InfiniteScroll>
  );
};

export default TimelineTab;
