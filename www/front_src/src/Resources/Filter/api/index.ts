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

const listCustomFilters =
  (cancelToken: CancelToken) => (): Promise<ListingModel<RawFilter>> =>
    getData<ListingModel<RawFilter>>(cancelToken)(
      buildListCustomFiltersEndpoint({ limit: 100, page: 1 }),
    );

type RawFilterWithoutId = Omit<RawFilter, 'id'>;

const createFilter =
  (cancelToken: CancelToken) =>
  (parameters: RawFilter): Promise<RawFilter> => {
    return postData<RawFilterWithoutId, RawFilter>(cancelToken)({
      data: parameters,
      endpoint: filterEndpoint,
    });
  };

interface UpdateFilterProps {
  id: number;
  rawFilter: RawFilter;
}

const updateFilter =
  (cancelToken: CancelToken) =>
  (parameters: UpdateFilterProps): Promise<RawFilter> => {
    return putData<RawFilterWithoutId, RawFilter>(cancelToken)({
      data: parameters.rawFilter,
      endpoint: `${filterEndpoint}/${parameters.id}`,
    });
  };

interface PatchFilterProps {
  id?: number;
  order: number;
}

const patchFilter =
  (cancelToken: CancelToken) =>
  (parameters: PatchFilterProps): Promise<Filter> => {
    return patchData<PatchFilterProps, Filter>(cancelToken)({
      data: { order: parameters.order },
      endpoint: `${filterEndpoint}/${parameters.id}`,
    });
  };

const deleteFilter =
  (cancelToken: CancelToken) =>
  (parameters: PatchFilterProps): Promise<void> => {
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
