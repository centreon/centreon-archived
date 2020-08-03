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

const createFilter = (cancelToken) => (params): Promise<RawFilter> => {
  return postData<RawFilterWithoutId, RawFilter>(cancelToken)({
    endpoint: filterEndpoint,
    data: params,
  });
};

interface UpdateFilterParams {
  id: number;
  rawFilter: RawFilter;
}

const updateFilter = (cancelToken) => ({
  id,
  rawFilter,
}: UpdateFilterParams): Promise<RawFilter> => {
  return putData<RawFilterWithoutId, RawFilter>(cancelToken)({
    endpoint: `${filterEndpoint}/${id}`,
    data: rawFilter,
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
