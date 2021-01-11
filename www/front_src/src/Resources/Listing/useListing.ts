import * as React from 'react';

import { ifElse, pathEq, always, pathOr } from 'ramda';

import {
  useRequest,
  setUrlQueryParameters,
  getUrlQueryParameters,
} from '@centreon/ui';

import { ResourceListing, SortOrder } from '../models';
import { labelSomethingWentWrong } from '../translatedLabels';
import { getStoredOrDefaultFilter, storeFilter } from '../Filter/storedFilter';
import useFilterModels from '../Filter/useFilterModels';

import { defaultSortOrder, defaultSortField } from './columns';
import ApiNotFoundMessage from './ApiNotFoundMessage';
import { listResources } from './api';

type ListingDispatch<T> = React.Dispatch<React.SetStateAction<T>>;

export interface ListingState {
  listing?: ResourceListing;
  setListing: ListingDispatch<ResourceListing | undefined>;
  sorto: SortOrder;
  sortf: string;
  setSortf: ListingDispatch<string>;
  setSorto: ListingDispatch<SortOrder>;
  limit: number;
  setLimit: ListingDispatch<number>;
  page?: number;
  setPage: ListingDispatch<number | undefined>;
  enabledAutorefresh: boolean;
  setEnabledAutorefresh: ListingDispatch<boolean>;
  sendRequest: (params) => Promise<ResourceListing>;
  sending: boolean;
}

const useListing = (): ListingState => {
  const [listing, setListing] = React.useState<ResourceListing>();

  const sortOrderFromQueryParameters = getUrlQueryParameters()
    .sorto as SortOrder;
  const sortFieldFromQueryParameters = getUrlQueryParameters().sortf as string;

  const [sorto, setSorto] = React.useState<SortOrder>(
    sortOrderFromQueryParameters || defaultSortOrder,
  );
  const [sortf, setSortf] = React.useState<string>(
    sortFieldFromQueryParameters || defaultSortField,
  );
  const [limit, setLimit] = React.useState<number>(30);
  const [page, setPage] = React.useState<number>();
  const [enabledAutorefresh, setEnabledAutorefresh] = React.useState(true);

  const { unhandledProblemsFilter } = useFilterModels();

  const { sendRequest, sending } = useRequest<ResourceListing>({
    request: listResources,
    getErrorMessage: ifElse(
      pathEq(['response', 'status'], 404),
      always(ApiNotFoundMessage),
      pathOr(labelSomethingWentWrong, ['response', 'data', 'message']),
    ),
  });

  React.useEffect(() => {
    const storedFilter = getStoredOrDefaultFilter(unhandledProblemsFilter);
    storeFilter({ ...storedFilter, sort: [sortf, sorto] });

    setUrlQueryParameters([
      {
        name: 'sorto',
        value: sorto,
      },
      {
        name: 'sortf',
        value: sortf,
      },
    ]);
  }, [sorto, sortf]);

  return {
    listing,
    setListing,
    sorto,
    setSorto,
    sortf,
    setSortf,
    limit,
    setLimit,
    page,
    setPage,
    enabledAutorefresh,
    setEnabledAutorefresh,
    sendRequest,
    sending,
  };
};

export default useListing;
