import * as React from 'react';

import { useTranslation } from 'react-i18next';

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

import { prop, isEmpty, cond, always, T, isNil, concat, path } from 'ramda';

import { types } from './Event';
import { TimelineEvent, Type } from './models';
import { listTimelineEventsDecoder } from './api/decoders';
import { listTimelineEvents } from './api';
import {
  labelEvent,
  labelNoResultsFound,
  labelRefresh,
} from '../../../translatedLabels';
import { useResourceContext } from '../../../Context';
import Events from './Events';
import LoadingSkeleton from './LoadingSkeleton';
import { TabProps } from '..';

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

const TimelineTab = ({ details }: TabProps): JSX.Element => {
  const classes = useStyles();

  const { t } = useTranslation();

  const translatedTypes = types.map((type) => ({
    ...type,
    name: t(type.name),
  })) as Array<Type>;

  const [timeline, setTimeline] = React.useState<Array<TimelineEvent>>();
  const [selectedTypes, setSelectedTypes] = React.useState<Array<Type>>(
    translatedTypes,
  );
  const [page, setPage] = React.useState(1);
  const [total, setTotal] = React.useState(0);
  const [limit] = React.useState(30);
  const [loadingMoreEvents, setLoadingMoreEvents] = React.useState(false);

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

  const timelineEndpoint = path(['links', 'endpoints', 'timeline'], details);

  const listTimeline = (
    { atPage } = {
      atPage: page,
    },
  ): Promise<TimelineListing> => {
    return sendRequest({
      endpoint: timelineEndpoint,
      parameters: {
        page: atPage,
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

  const reload = (): void => {
    setPage(1);
    setTimeline(undefined);
    listTimeline({ atPage: 1 }).then(({ result }) => {
      setTimeline(result);
    });
  };

  React.useEffect(() => {
    reload();
  }, [details, selectedTypes]);

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
            label={t(labelEvent)}
            onChange={changeSelectedTypes}
            value={selectedTypes}
            options={translatedTypes}
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
                    variant="contained"
                    color="primary"
                    size="small"
                    startIcon={<IconRefresh />}
                    onClick={reload}
                  >
                    {labelRefresh}
                  </Button>
                )}
                <Events
                  timeline={timeline as Array<TimelineEvent>}
                  total={total}
                  limit={limit}
                  page={page}
                  loading={sending}
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
