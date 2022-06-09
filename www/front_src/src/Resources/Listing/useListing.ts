import * as React from 'react';

import { useRequest } from '@centreon/ui';

import { ResourceListing } from '../models';

import { defaultSortOrder, defaultSortField } from './columns';
import { listResources } from './api';

type SortOrder = 'asc' | 'desc';

type ListingDispatch<T> = React.Dispatch<React.SetStateAction<T>>;

export interface ListingState {
  enabledAutorefresh: boolean;
  limit: number;
  listing?: ResourceListing;
  page?: number;
  sendRequest: (params) => Promise<ResourceListing>;
  sending: boolean;
  setEnabledAutorefresh: ListingDispatch<boolean>;
  setLimit: ListingDispatch<number>;
  setListing: ListingDispatch<ResourceListing | undefined>;
  setPage: ListingDispatch<number | undefined>;
  setSortf: ListingDispatch<string>;
  setSorto: ListingDispatch<SortOrder>;
  sortf: string;
  sorto: SortOrder;
}

const useListing = (): ListingState => {
  const [listing, setListing] = React.useState<ResourceListing>();
  const [sorto, setSorto] = React.useState<SortOrder>(defaultSortOrder);
  const [sortf, setSortf] = React.useState<string>(defaultSortField);
  const [limit, setLimit] = React.useState<number>(30);
  const [page, setPage] = React.useState<number>();
  const [enabledAutorefresh, setEnabledAutorefresh] = React.useState(true);

  const { sendRequest, sending } = useRequest<ResourceListing>({
    request: listResources,
  });

  return {
    enabledAutorefresh,
    limit,
    listing,
    page,
    sendRequest,
    sending,
    setEnabledAutorefresh,
    setLimit,
    setListing,
    setPage,
    setSortf,
    setSorto,
    sortf,
    sorto,
  };
};

export default useListing;
