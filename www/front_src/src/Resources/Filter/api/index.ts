import { JsonDecoder } from 'ts.data.json';

import {
  buildListingEndpoint,
  buildListingDecoder,
  getData,
  ListingModel,
} from '@centreon/ui';

import { baseEndpoint } from '../../api/endpoint';
import { RawFilter, RowCriteria, CriteriaValue } from '../models';

const filterEndpoint = `${baseEndpoint}/users/filters/events-view`;

const entityDecoder = JsonDecoder.object<RawFilter>(
  {
    id: JsonDecoder.number,
    name: JsonDecoder.string,
    criterias: JsonDecoder.array<RowCriteria>(
      JsonDecoder.object<RowCriteria>(
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

export {
  listCustomFilters,
  buildListCustomFiltersEndpoint,
  listCustomFiltersDecoder,
};
