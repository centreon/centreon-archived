import {
  buildListingEndpoint,
  getData,
  ListingModel,
  postData,
  patchData,
} from '@centreon/ui';

import { baseEndpoint } from '../../api/endpoint';
import { RawFilter } from '../models';
import { toRawFilter } from './adapters';

const filterEndpoint = `${baseEndpoint}/users/filters/events-view`;

// const filterEndpoint = 'http://localhost:5000/mock/filters';

const buildListCustomFiltersEndpoint = (params): string =>
  buildListingEndpoint({
    baseEndpoint: filterEndpoint,
    params,
  });

const listCustomFilters = (cancelToken) => (): Promise<
  ListingModel<RawFilter>
> =>
  getData<ListingModel<RawFilter>>(cancelToken)(
    buildListCustomFiltersEndpoint({ limit: 100, page: 1 }),
  );

const createFilter = (cancelToken) => (params): Promise<number> => {
  return postData<Omit<RawFilter, 'id'>, { id: number }>(cancelToken)({
    endpoint: filterEndpoint,
    data: toRawFilter(params),
  }).then(({ id }) => id);
};

const updateFilter = (cancelToken) => (params): Promise<void> =>
  patchData<Omit<RawFilter, 'id'>, void>(cancelToken)({
    endpoint: `${filterEndpoint}/${params.id}`,
    data: toRawFilter(params),
  });

export {
  listCustomFilters,
  buildListCustomFiltersEndpoint,
  createFilter,
  updateFilter,
};
