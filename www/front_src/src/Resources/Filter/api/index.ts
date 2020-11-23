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

interface ListCustomFiltersProps {
  limit: number;
  page: number;
}

const buildListCustomFiltersEndpoint = (
  parameters: ListCustomFiltersProps,
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
  rawFilter: RawFilterWithoutId,
): Promise<RawFilter> => {
  return postData<RawFilterWithoutId, RawFilter>(cancelToken)({
    endpoint: filterEndpoint,
    data: rawFilter,
  });
};

interface UpdateFilterProps {
  id: number;
  rawFilter: RawFilterWithoutId;
}

const updateFilter = (cancelToken: CancelToken) => (
  parameters: UpdateFilterProps,
): Promise<RawFilter> => {
  return putData<RawFilterWithoutId, RawFilter>(cancelToken)({
    endpoint: `${filterEndpoint}/${parameters.id}`,
    data: parameters.rawFilter,
  });
};

interface PatchFilterProps {
  id?: number;
  order: number;
}

const patchFilter = (cancelToken: CancelToken) => (
  parameters: PatchFilterProps,
): Promise<Filter> => {
  return patchData<PatchFilterProps, Filter>(cancelToken)({
    endpoint: `${filterEndpoint}/${parameters.id}`,
    data: { order: parameters.order },
  });
};

const deleteFilter = (cancelToken: CancelToken) => (parameters: {
  id: number;
}): Promise<void> => {
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
