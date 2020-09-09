import * as React from 'react';

import {
  useRequest,
  ListingModel,
  MultiAutocompleteField,
  SearchParameter,
} from '@centreon/ui';

import {
  makeStyles,
  Paper,
  Typography,
  CircularProgress,
} from '@material-ui/core';
import { prop, isEmpty, cond, always, T, isNil, concat } from 'ramda';

import { ResourceLinks } from '../../../models';
import { types } from './Event';
import { TimelineEvent, Type } from './models';
import { listTimelineEventsDecoder } from './api/decoders';
import { listTimelineEvents } from './api';
import { labelEvent, labelNoResultsFound } from '../../../translatedLabels';
import { useResourceContext } from '../../../Context';
import Events from './Events';
import LoadingSkeleton from './LoadingSkeleton';

interface Props {
  links: ResourceLinks;
}

type TimelineListing = ListingModel<TimelineEvent>;

const useStyles = makeStyles((theme) => ({
  container: {
    width: '100%',
    height: '100%',
    display: 'grid',
    alignItems: 'center',
    justifyItems: 'center',
    alignContent: 'flex-start',
    gridGap: theme.spacing(1),
  },
  filterContainer: {
    width: '100%',
  },
  filterAutocomplete: {
    margin: theme.spacing(2),
  },
  noResultContainer: {
    padding: theme.spacing(1),
  },
  events: {
    display: 'grid',
    gridAutoFlow: 'row',
    gridGap: theme.spacing(1),
    width: '100%',
  },
}));

const TimelineTab = ({ links }: Props): JSX.Element => {
  const classes = useStyles();

  const [timeline, setTimeline] = React.useState<Array<TimelineEvent>>();
  const [selectedTypes, setSelectedTypes] = React.useState<Array<Type>>(types);
  const [page, setPage] = React.useState(1);
  const [total, setTotal] = React.useState(0);
  const [limit] = React.useState(30);
  const [loadingMoreEvents, setLoadingMoreEvents] = React.useState(false);

  const { endpoints } = links;
  const { timeline: timelineEndpoint } = endpoints;
  const { listing } = useResourceContext();

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

  const listTimeline = (): Promise<TimelineListing> => {
    return sendRequest({
      endpoint: timelineEndpoint,
      parameters: {
        page,
        limit,
        search: getSearch(),
      },
    })
      .then((retrievedListing) => {
        const { meta } = retrievedListing;
        setTotal(meta.total);

        return retrievedListing;
      })
      .finally(() => {
        setLoadingMoreEvents(false);
      });
  };

  React.useEffect(() => {
    if (isNil(timeline) || page !== 1) {
      return;
    }

    listTimeline().then(({ result }) => {
      setTimeline(result);
    });
  }, [listing]);

  React.useEffect(() => {
    if (isNil(timeline)) {
      return;
    }

    listTimeline().then(({ result }) => {
      setTimeline(concat(timeline, result));
    });
  }, [page]);

  React.useEffect(() => {
    setPage(1);
    setTimeline(undefined);
    listTimeline().then(({ result }) => {
      setTimeline(result);
    });
  }, [endpoints, selectedTypes]);

  const changeSelectedTypes = (_, typeIds): void => {
    setSelectedTypes(typeIds);
  };

  const loadMoreEvents = (): void => {
    setLoadingMoreEvents(true);
    setPage(page + 1);
  };

  return (
    <div className={classes.container}>
      <Paper className={classes.filterContainer}>
        <div className={classes.filterAutocomplete}>
          <MultiAutocompleteField
            label={labelEvent}
            onChange={changeSelectedTypes}
            value={selectedTypes}
            options={types}
            fullWidth
            limitTags={3}
          />
        </div>
      </Paper>
      <div className={classes.events}>
        {cond([
          [always(isNil(timeline)), always(<LoadingSkeleton />)],
          [
            isEmpty,
            always(
              <Paper className={classes.noResultContainer}>
                <Typography align="center" variant="body1">
                  {labelNoResultsFound}
                </Typography>
              </Paper>,
            ),
          ],
          [
            T,
            always(
              <Events
                timeline={timeline as Array<TimelineEvent>}
                total={total}
                limit={limit}
                page={page}
                loading={sending}
                onLoadMore={loadMoreEvents}
              />,
            ),
          ],
        ])(timeline)}
      </div>
      {loadingMoreEvents && <CircularProgress />}
    </div>
  );
};

export default TimelineTab;
