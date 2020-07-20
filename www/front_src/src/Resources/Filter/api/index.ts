import {
  buildListingEndpoint,
  getData,
  ListingModel,
  postData,
  putData,
} from '@centreon/ui';

import { baseEndpoint } from '../../api/endpoint';
import { RawFilter, Filter } from '../models';
import { toRawFilter, toFilter } from './adapters';

const filterEndpoint = `${baseEndpoint}/users/filters/events-view`;

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

const createFilter = (cancelToken) => (params): Promise<Filter> => {
  return postData<Omit<RawFilter, 'id'>, RawFilter>(cancelToken)({
    endpoint: filterEndpoint,
    data: toRawFilter(params),
  }).then(toFilter);
};

const updateFilter = (cancelToken) => (params): Promise<void> =>
  putData<Omit<RawFilter, 'id'>, void>(cancelToken)({
    endpoint: `${filterEndpoint}/${params.id}`,
    data: toRawFilter(params),
  });

export {
  filterEndpoint,
  listCustomFilters,
  buildListCustomFiltersEndpoint,
  createFilter,
  updateFilter,
};
