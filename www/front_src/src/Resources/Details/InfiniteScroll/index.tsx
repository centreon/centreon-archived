import * as React from 'react';

import { useTranslation } from 'react-i18next';
import { always, isNil, isEmpty, cond, T, concat } from 'ramda';

import { CircularProgress, Button, makeStyles } from '@material-ui/core';
import IconRefresh from '@material-ui/icons/Refresh';

import { useIntersectionObserver, ListingModel } from '@centreon/ui';

import { labelRefresh } from '../../translatedLabels';
import NoResultsMessage from '../NoResultsMessage';
import { ResourceDetails } from '../models';
import { ResourceContext, useResourceContext } from '../../Context';
import memoizeComponent from '../../memoizedComponent';

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
  entities: {
    display: 'grid',
    gridAutoFlow: 'row',
    gridGap: theme.spacing(1),
    gridTemplateColumns: 'repeat(auto-fit, minmax(320px, 1fr))',
    width: '100%',
  },
  filter: {
    width: '100%',
  },
}));

interface Props<TEntity> {
  children: (props) => JSX.Element;
  details?: ResourceDetails;
  filter?: JSX.Element;
  limit: number;
  loading: boolean;
  loadingSkeleton: JSX.Element;
  preventReloadWhen?: boolean;
  reloadDependencies?: Array<unknown>;
  sendListingRequest: (parameters: {
    atPage?: number;
  }) => Promise<ListingModel<TEntity>>;
}

type InfiniteScrollContentProps<TEntity> = Props<TEntity> &
  Pick<ResourceContext, 'selectedResourceId'>;

const InfiniteScrollContent = <TEntity extends { id: number }>({
  limit,
  filter,
  details,
  reloadDependencies = [],
  loadingSkeleton,
  loading,
  preventReloadWhen = false,
  selectedResourceId,
  sendListingRequest,
  children,
}: InfiniteScrollContentProps<TEntity>): JSX.Element => {
  const { t } = useTranslation();
  const classes = useStyles();

  const [entities, setEntities] = React.useState<Array<TEntity>>();
  const [page, setPage] = React.useState(1);
  const [total, setTotal] = React.useState(0);
  const [loadingMoreEvents, setLoadingMoreEvents] = React.useState(false);

  const listEntities = (
    { atPage } = {
      atPage: page,
    },
  ): Promise<ListingModel<TEntity>> => {
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

    if (page !== 1 || isNil(details) || preventReloadWhen) {
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

  React.useEffect(() => {
    if (selectedResourceId !== details?.id) {
      setEntities(undefined);
      setPage(1);
    }
  }, [selectedResourceId]);

  const maxPage = Math.ceil(total / limit);

  const loadMoreEvents = (): void => {
    setLoadingMoreEvents(true);
    setPage(page + 1);
  };

  const infiniteScrollTriggerRef = useIntersectionObserver({
    action: loadMoreEvents,
    loading,
    maxPage,
    page,
  });

  return (
    <div className={classes.container}>
      <div className={classes.filter}>{filter}</div>
      {page > 1 && (
        <Button
          color="primary"
          size="small"
          startIcon={<IconRefresh />}
          variant="contained"
          onClick={reload}
        >
          {t(labelRefresh)}
        </Button>
      )}
      <div className={classes.entities}>
        {cond([
          [always(isNil(entities)), always(loadingSkeleton)],
          [isEmpty, always(<NoResultsMessage />)],
          [T, always(<>{children({ entities, infiniteScrollTriggerRef })}</>)],
        ])(entities)}
      </div>
      {loadingMoreEvents && <CircularProgress />}
    </div>
  );
};

const MemoizedInfiniteScrollContent = memoizeComponent({
  Component: InfiniteScrollContent,
  memoProps: [
    'selectedResourceId',
    'limit',
    'details',
    'reloadDependencies',
    'loading',
    'preventReloadWhen',
    'filter',
  ],
}) as typeof InfiniteScrollContent;

const InfiniteScroll = <TEntity extends { id: number }>(
  props: Props<TEntity>,
): JSX.Element => {
  const { selectedResourceId } = useResourceContext();

  return (
    <MemoizedInfiniteScrollContent
      selectedResourceId={selectedResourceId}
      {...props}
    />
  );
};

export default InfiniteScroll;
