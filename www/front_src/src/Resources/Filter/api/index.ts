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

const filterEndpoint = `${baseEndpoint}/users/filters/events-view`;

const buildListCustomFiltersEndpoint = (parameters): string =>
  buildListingEndpoint({
    baseEndpoint: filterEndpoint,
    parameters,
  });

const listCustomFilters = (cancelToken) => (): Promise<
  ListingModel<RawFilter>
> =>
  getData<ListingModel<RawFilter>>(cancelToken)(
    buildListCustomFiltersEndpoint({ limit: 100, page: 1 }),
  );

type RawFilterWithoutId = Omit<RawFilter, 'id'>;

const createFilter = (cancelToken) => (parameters): Promise<Filter> => {
  return postData<RawFilterWithoutId, RawFilter>(cancelToken)({
    endpoint: filterEndpoint,
    data: parameters,
  });
};

const updateFilter = (cancelToken) => (parameters): Promise<Filter> => {
  return putData<RawFilterWithoutId, Filter>(cancelToken)({
    endpoint: `${filterEndpoint}/${parameters.id}`,
    data: parameters,
  });
};

interface PatchFilterProps {
  order: number;
}

const patchFilter = (cancelToken) => (parameters): Promise<Filter> => {
  return patchData<PatchFilterProps, Filter>(cancelToken)({
    endpoint: `${filterEndpoint}/${parameters.id}`,
    data: { order: parameters.order },
  });
};

const deleteFilter = (cancelToken) => (parameters): Promise<void> => {
  return deleteData<void>(cancelToken)(`${filterEndpoint}/${parameters.id}`);
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
