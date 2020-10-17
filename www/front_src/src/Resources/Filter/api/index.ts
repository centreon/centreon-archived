import { CancelToken } from 'axios';

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

interface CustomFiltersParams {
  limit: number;
  page: number;
}

const buildListCustomFiltersEndpoint = (
  parameters: CustomFiltersParams,
): string =>
  buildListingEndpoint({
    baseEndpoint: filterEndpoint,
    parameters,
  });

const listCustomFilters = (cancelToken: CancelToken) => (): Promise<
  ListingModel<RawFilter>
> =>
  getData<ListingModel<RawFilter>>(cancelToken)(
    buildListCustomFiltersEndpoint({ limit: 100, page: 1 }),
  );

type RawFilterWithoutId = Omit<RawFilter, 'id'>;

const createFilter = (cancelToken: CancelToken) => (
  parameters: Pick<RawFilter, 'name' | 'criterias'>,
): Promise<RawFilter> => {
  return postData<RawFilterWithoutId, RawFilter>(cancelToken)({
    endpoint: filterEndpoint,
    data: parameters,
  });
};

interface FilterParams {
  id: number | string;
}

interface UpdateFilterParams extends FilterParams {
  rawFilter: Pick<RawFilter, 'name' | 'criterias'>;
}

const updateFilter = (cancelToken: CancelToken) => (
  parameters: UpdateFilterParams,
): Promise<RawFilter> => {
  return putData<RawFilterWithoutId, RawFilter>(cancelToken)({
    endpoint: `${filterEndpoint}/${parameters.id}`,
    data: parameters.rawFilter,
  });
};

interface PatchFilterProps {
  order: number;
}

interface PatchFilterParams extends FilterParams {
  order: number;
}

const patchFilter = (cancelToken: CancelToken) => (
  parameters: PatchFilterParams,
): Promise<Filter> => {
  return patchData<PatchFilterProps, Filter>(cancelToken)({
    endpoint: `${filterEndpoint}/${parameters.id}`,
    data: { order: parameters.order },
  });
};

const deleteFilter = (cancelToken: CancelToken) => (
  parameters: FilterParams,
): Promise<void> => {
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
