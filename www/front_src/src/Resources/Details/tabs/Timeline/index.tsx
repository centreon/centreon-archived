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

  const [selectedTypes, setSelectedTypes] = React.useState<Array<Type>>(
    translatedTypes,
  );
  const limit = 30;

  const { sendRequest, sending } = useRequest<TimelineListing>({
    request: listTimelineEvents,
    decoder: listTimelineEventsDecoder,
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
        page: atPage,
        limit,
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
      sendListingRequest={listTimeline}
      loading={sending}
      limit={limit}
      loadingSkeleton={<LoadingSkeleton />}
      reloadDependencies={[selectedTypes]}
      filter={
        <Paper className={classes.filter}>
          <MultiAutocompleteField
            label={t(labelEvent)}
            onChange={changeSelectedTypes}
            value={selectedTypes}
            options={translatedTypes}
            fullWidth
            limitTags={3}
          />
        </Paper>
      }
    >
      {({ infiniteScrollTriggerRef, entities }): JSX.Element => {
        return (
          <Events
            timeline={entities}
            infiniteScrollTriggerRef={infiniteScrollTriggerRef}
          />
        );
      }}
    </InfiniteScroll>
  );
};

export default TimelineTab;
