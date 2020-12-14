import * as React from 'react';

import { useTranslation } from 'react-i18next';
import { always, isNil, isEmpty, cond, T, concat } from 'ramda';

import { CircularProgress, Button, makeStyles } from '@material-ui/core';
import IconRefresh from '@material-ui/icons/Refresh';

import { useIntersectionObserver } from '@centreon/ui';

import { labelRefresh } from '../../translatedLabels';
import NoResultsMessage from '../NoResultsMessage';
import { ResourceDetails } from '../models';

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
  entities: {
    display: 'grid',
    gridAutoFlow: 'row',
    gridGap: theme.spacing(1),
    width: '100%',
  },
}));

interface Props {
  limit: number;
  filter?: JSX.Element;
  details?: ResourceDetails;
  reloadDependencies?: Array<unknown>;
  sendListingRequest: (parameters: { atPage?: number }) => Promise<unknown>;
  loadingSkeleton: JSX.Element;
  loading: boolean;
  children: (props) => JSX.Element;
}

const InfiniteScroll = ({
  limit,
  details,
  filter,
  reloadDependencies,
  sendListingRequest,
  loadingSkeleton,
  loading,
  children,
}: Props): JSX.Element => {
  const { t } = useTranslation();
  const classes = useStyles();

  const [entities, setEntities] = React.useState([]);
  const [page, setPage] = React.useState(1);
  const [total, setTotal] = React.useState(0);
  const [loadingMoreEvents, setLoadingMoreEvents] = React.useState(false);

  const listEntities = (
    { atPage } = {
      atPage: page,
    },
  ) => {
    return sendListingRequest({ atPage })
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
    listEntities({ atPage: 1 }).then(({ result }) => {
      setEntities(result);
    });
  };

  React.useEffect(() => {
    if (isNil(details)) {
      setEntities(undefined);
    }

    if (page !== 1 || isNil(details)) {
      return;
    }

    reload();
  }, [details]);

  React.useEffect(() => {
    if (isNil(entities) || page === 1) {
      return;
    }

    listEntities().then(({ result }) => {
      setEntities(concat(entities, result));
    });
  }, [page]);

  React.useEffect(() => {
    if (isNil(details) || isNil(entities)) {
      return;
    }

    setEntities(undefined);

    reload();
  }, reloadDependencies);

  const maxPage = Math.ceil(total / limit);

  const loadMoreEvents = (): void => {
    setLoadingMoreEvents(true);
    setPage(page + 1);
  };

  const infiniteScrollTriggerRef = useIntersectionObserver({
    maxPage,
    page,
    loading,
    action: loadMoreEvents,
  });

  return (
    <div className={classes.container}>
      {filter}
      <div className={classes.entities}>
        {cond([
          [always(isNil(entities)), always(loadingSkeleton)],
          [isEmpty, always(<NoResultsMessage />)],
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
                    {t(labelRefresh)}
                  </Button>
                )}

                {children({ infiniteScrollTriggerRef, entities })}
              </>,
            ),
          ],
        ])(entities)}
      </div>
      {loadingMoreEvents && <CircularProgress />}
    </div>
  );
};

export default InfiniteScroll;
