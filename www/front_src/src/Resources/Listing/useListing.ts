import * as React from 'react';

import { ifElse, pathEq, always, pathOr } from 'ramda';

import { useRequest } from '@centreon/ui';

import { ResourceListing } from '../models';
import { defaultSortOrder, defaultSortField } from './columns';
import { listResources } from '../api';
import ApiNotFoundMessage from './ApiNotFoundMessage';
import { labelSomethingWentWrong } from '../translatedLabels';

type SortOrder = 'asc' | 'desc';

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
  page: number;
  setPage: ListingDispatch<number>;
  enabledAutorefresh: boolean;
  setEnabledAutorefresh: ListingDispatch<boolean>;
  sendRequest: (params) => Promise<ResourceListing>;
  sending: boolean;
}

const useListing = (): ListingState => {
  const [listing, setListing] = React.useState<ResourceListing>();
  const [sorto, setSorto] = React.useState<SortOrder>(defaultSortOrder);
  const [sortf, setSortf] = React.useState<string>(defaultSortField);
  const [limit, setLimit] = React.useState<number>(30);
  const [page, setPage] = React.useState<number>(1);
  const [enabledAutorefresh, setEnabledAutorefresh] = React.useState(true);

  const { sendRequest, sending } = useRequest<ResourceListing>({
    request: listResources,
    getErrorMessage: ifElse(
      pathEq(['response', 'status'], 404),
      always(ApiNotFoundMessage),
      pathOr(labelSomethingWentWrong, ['response', 'data', 'message']),
    ),
  });

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
