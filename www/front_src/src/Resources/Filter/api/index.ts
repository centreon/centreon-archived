import {
  buildListingEndpoint,
  getData,
  ListingModel,
  postData,
  putData,
  deleteData,
  patchData,
  ListingParameters,
} from '@centreon/ui';
import { CancelToken } from 'axios';

import { baseEndpoint } from '../../api/endpoint';
import { RawFilter, Filter } from '../models';
import { toRawFilter, toFilter } from './adapters';

const filterEndpoint = `${baseEndpoint}/users/filters/events-view`;

const buildListCustomFiltersEndpoint = (
  parameters: ListingParameters,
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
  (parameters: Filter): Promise<Filter> => {
    return postData<RawFilterWithoutId, RawFilter>(cancelToken)({
      data: toRawFilter(parameters),
      endpoint: filterEndpoint,
    }).then(toFilter);
  };

const updateFilter =
  (cancelToken: CancelToken) =>
  (parameters: Filter): Promise<Filter> => {
    return putData<RawFilterWithoutId, RawFilter>(cancelToken)({
      data: toRawFilter(parameters),
      endpoint: `${filterEndpoint}/${parameters.id}`,
    }).then(toFilter);
  };

interface PatchFilterProps {
  order: number;
}

const patchFilter =
  (cancelToken: CancelToken) =>
  (parameters: PatchFilterProps & { id: number }): Promise<Filter> => {
    return patchData<PatchFilterProps, Filter>(cancelToken)({
      data: { order: parameters.order },
      endpoint: `${filterEndpoint}/${parameters.id}`,
    });
  };

const deleteFilter =
  (cancelToken: CancelToken) =>
  (parameters: { id: number }): Promise<void> => {
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
