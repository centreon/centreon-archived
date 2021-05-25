import * as React from 'react';

import { useTranslation } from 'react-i18next';
import { prop, isEmpty, cond, always, T, isNil, concat, path } from 'ramda';

import {
  makeStyles,
  Paper,
  Typography,
  CircularProgress,
  Button,
} from '@material-ui/core';
import IconRefresh from '@material-ui/icons/Refresh';

import {
  useRequest,
  ListingModel,
  MultiAutocompleteField,
  SearchParameter,
} from '@centreon/ui';

import {
  labelEvent,
  labelNoResultsFound,
  labelRefresh,
} from '../../../translatedLabels';
import { TabProps } from '..';

import { types } from './Event';
import { TimelineEvent, Type } from './models';
import { listTimelineEventsDecoder } from './api/decoders';
import { listTimelineEvents } from './api';
import Events from './Events';
import LoadingSkeleton from './LoadingSkeleton';

type TimelineListing = ListingModel<TimelineEvent>;

const useStyles = makeStyles((theme) => ({
  container: {
    alignContent: 'flex-start',
    alignItems: 'center',
    display: 'grid',
    gridGap: theme.spacing(1),
    height: '100%',
    justifyItems: 'center',
    width: '100%',
  },
  events: {
    display: 'grid',
    gridAutoFlow: 'row',
    gridGap: theme.spacing(1),
    width: '100%',
  },
  filterAutocomplete: {
    margin: theme.spacing(2),
  },
  filterContainer: {
    width: '100%',
  },
  noResultContainer: {
    padding: theme.spacing(1),
  },
}));

const TimelineTab = ({ details }: TabProps): JSX.Element => {
  const classes = useStyles();

  const { t } = useTranslation();

  const translatedTypes = types.map((type) => ({
    ...type,
    name: t(type.name),
  })) as Array<Type>;

  const [timeline, setTimeline] = React.useState<Array<TimelineEvent>>();
  const [selectedTypes, setSelectedTypes] =
    React.useState<Array<Type>>(translatedTypes);
  const [page, setPage] = React.useState(1);
  const [total, setTotal] = React.useState(0);
  const [limit] = React.useState(30);
  const [loadingMoreEvents, setLoadingMoreEvents] = React.useState(false);

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

  const listTimeline = (
    { atPage } = {
      atPage: page,
    },
  ): Promise<TimelineListing> => {
    return sendRequest({
      endpoint: timelineEndpoint,
      parameters: {
        limit,
        page: atPage,
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

  const reload = (): void => {
    setPage(1);
    listTimeline({ atPage: 1 }).then(({ result }) => {
      setTimeline(result);
    });
  };

  React.useEffect(() => {
    if (isNil(details)) {
      setTimeline(undefined);
    }

    if (page !== 1 || isNil(details)) {
      return;
    }

    reload();
  }, [details]);

  React.useEffect(() => {
    if (isNil(timeline) || page === 1) {
      return;
    }

    listTimeline().then(({ result }) => {
      setTimeline(concat(timeline, result));
    });
  }, [page]);

  React.useEffect(() => {
    if (isNil(details) || isNil(timeline)) {
      return;
    }

    setTimeline(undefined);

    reload();
  }, [selectedTypes]);

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
            fullWidth
            label={t(labelEvent)}
            limitTags={3}
            options={translatedTypes}
            value={selectedTypes}
            onChange={changeSelectedTypes}
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
                  {t(labelNoResultsFound)}
                </Typography>
              </Paper>,
            ),
          ],
          [
            T,
            always(
              <>
                {page > 1 && (
                  <Button
                    color="primary"
                    size="small"
                    startIcon={<IconRefresh />}
                    variant="contained"
                    onClick={reload}
                  >
                    {labelRefresh}
                  </Button>
                )}
                <Events
                  limit={limit}
                  loading={sending}
                  page={page}
                  timeline={timeline as Array<TimelineEvent>}
                  total={total}
                  onLoadMore={loadMoreEvents}
                />
              </>,
            ),
          ],
        ])(timeline)}
      </div>
      {loadingMoreEvents && <CircularProgress />}
    </div>
  );
};

export default TimelineTab;
