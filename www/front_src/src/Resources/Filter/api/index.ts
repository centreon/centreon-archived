import {
  buildListingEndpoint,
  getData,
  ListingModel,
  postData,
  putData,
  deleteData,
  patchData,
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

type RawFilterWithoutId = Omit<RawFilter, 'id'>;

const createFilter = (cancelToken) => (params): Promise<Filter> => {
  return postData<RawFilterWithoutId, RawFilter>(cancelToken)({
    endpoint: filterEndpoint,
    data: toRawFilter(params),
  }).then(toFilter);
};

const updateFilter = (cancelToken) => (params): Promise<Filter> => {
  return putData<RawFilterWithoutId, Filter>(cancelToken)({
    endpoint: `${filterEndpoint}/${params.id}`,
    data: toRawFilter(params),
  });
};

interface PatchFilterProps {
  order: number;
}

const patchFilter = (cancelToken) => (params): Promise<Filter> => {
  return patchData<PatchFilterProps, Filter>(cancelToken)({
    endpoint: `${filterEndpoint}/${params.id}`,
    data: { order: params.order },
  });
};

const deleteFilter = (cancelToken) => (params): Promise<void> => {
  return deleteData<void>(cancelToken)(`${filterEndpoint}/${params.id}`);
};

export {
  filterEndpoint,
  listCustomFilters,
  buildListCustomFiltersEndpoint,
  createFilter,
  updateFilter,
  patchFilter,
  deleteFilter,
};
