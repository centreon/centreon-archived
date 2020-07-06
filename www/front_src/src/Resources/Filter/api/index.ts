import { JsonDecoder } from 'ts.data.json';

import {
  buildListingEndpoint,
  buildListingDecoder,
  getData,
  ListingModel,
  postData,
  patchData,
} from '@centreon/ui';

import { baseEndpoint } from '../../api/endpoint';
import { RawFilter, RawCriteria, CriteriaValue, Filter } from '../models';
import { toRawFilter } from '../adapters';

const filterEndpoint = `${baseEndpoint}/users/filters/events-view`;

const entityDecoder = JsonDecoder.object<RawFilter>(
  {
    id: JsonDecoder.number,
    name: JsonDecoder.string,
    criterias: JsonDecoder.array<RawCriteria>(
      JsonDecoder.object<RawCriteria>(
        {
          name: JsonDecoder.string,
          objectType: JsonDecoder.optional(JsonDecoder.string),
          type: JsonDecoder.string,
          value: JsonDecoder.oneOf<string | boolean | Array<CriteriaValue>>(
            [
              JsonDecoder.string,
              JsonDecoder.boolean,
              JsonDecoder.array<CriteriaValue>(
                JsonDecoder.object<CriteriaValue>(
                  {
                    id: JsonDecoder.oneOf<number | string>(
                      [JsonDecoder.number, JsonDecoder.string],
                      'string | id ',
                    ),
                    name: JsonDecoder.optional(JsonDecoder.string),
                  },
                  'FilterCriteriaValue',
                ),
                'FilterCriteriaValues',
              ),
            ],
            'CriteriaIdValue',
          ),
        },
        'FilterCriterias',
        { objectType: 'object_type' },
      ),
      'FilterCriterias',
    ),
  },
  'CustomFilter',
);

const listCustomFiltersDecoder = buildListingDecoder({
  entityDecoder,
  entityDecoderName: 'CustomFilter',
  listingDecoderName: 'CustomFilters',
});

const buildListCustomFiltersEndpoint = (params): string =>
  buildListingEndpoint({
    baseEndpoint: 'http://localhost:5000/mock/filters',
    params,
  });

const listCustomFilters = (cancelToken) => (
  params,
): Promise<ListingModel<RawFilter>> =>
  getData<ListingModel<RawFilter>>(cancelToken)(
    buildListCustomFiltersEndpoint(params),
  );

const createFilter = (cancelToken) => (params): Promise<number> =>
  postData<Omit<RawFilter, 'id'>, number>(cancelToken)({
    endpoint: filterEndpoint,
    data: toRawFilter(params),
  });

const updateFilter = (cancelToken) => (params): Promise<void> =>
  patchData<Filter, void>(cancelToken)({
    endpoint: `${filterEndpoint}/${params.id}`,
    data: params,
  });

export {
  listCustomFilters,
  buildListCustomFiltersEndpoint,
  listCustomFiltersDecoder,
  createFilter,
  updateFilter,
};
