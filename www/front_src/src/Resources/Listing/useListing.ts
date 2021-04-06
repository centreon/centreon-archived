import * as React from 'react';

import { ifElse, pathEq, always, pathOr } from 'ramda';

import { useRequest } from '@centreon/ui';

import { ResourceListing } from '../models';
import { labelSomethingWentWrong } from '../translatedLabels';

import ApiNotFoundMessage from './ApiNotFoundMessage';
import { listResources } from './api';
import { defaultSelectedColumnIds } from './columns';
import {
  clearCachedColumnIds,
  getStoredOrDefaultColumnIds,
  storeColumnIds,
} from './columns/storedColumnIds';

type ListingDispatch<T> = React.Dispatch<React.SetStateAction<T>>;

export interface ListingState {
  enabledAutorefresh: boolean;
  limit: number;
  listing?: ResourceListing;
  page?: number;
  selectedColumnIds: Array<string>;
  sendRequest: (params) => Promise<ResourceListing>;
  sending: boolean;
  setEnabledAutorefresh: ListingDispatch<boolean>;
  setLimit: ListingDispatch<number>;
  setListing: ListingDispatch<ResourceListing | undefined>;
  setPage: ListingDispatch<number | undefined>;
  setSelectedColumnIds: (columnIds: Array<string>) => void;
}

const useListing = (): ListingState => {
  const [listing, setListing] = React.useState<ResourceListing>();
  const [limit, setLimit] = React.useState<number>(30);
  const [page, setPage] = React.useState<number>();
  const [enabledAutorefresh, setEnabledAutorefresh] = React.useState(true);
  const [selectedColumnIds, setSelectedColumnIds] = React.useState(
    getStoredOrDefaultColumnIds(defaultSelectedColumnIds),
  );

  React.useEffect(() => {
    storeColumnIds(selectedColumnIds);
  }, [selectedColumnIds]);

  React.useEffect(() => {
    return (): void => {
      clearCachedColumnIds();
    };
  });

  const { sendRequest, sending } = useRequest<ResourceListing>({
    getErrorMessage: ifElse(
      pathEq(['response', 'status'], 404),
      always(ApiNotFoundMessage),
      pathOr(labelSomethingWentWrong, ['response', 'data', 'message']),
    ),
    request: listResources,
  });

  return {
    enabledAutorefresh,
    limit,
    listing,
    page,
    selectedColumnIds,
    sendRequest,
    sending,
    setEnabledAutorefresh,
    setLimit,
    setListing,
    setPage,
    setSelectedColumnIds,
  };
};

export default useListing;
