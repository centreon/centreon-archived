import { JsonDecoder } from 'ts.data.json';

import {
  buildListingEndpoint,
  buildListingDecoder,
  getData,
  ListingModel,
} from '@centreon/ui';

import { baseEndpoint } from '../../api/endpoint';

const filterEndpoint = `${baseEndpoint}/users/filters/events-view`;

interface CriteriaIdValue {
  id: number | string;
  name?: string;
}

interface Criteria {
  name: string;
  objectType?: string;
  value: Array<CriteriaIdValue> | string | boolean;
}

export interface CustomFilter {
  name: string;
  criterias: Array<Criteria>;
}

const entityDecoder = JsonDecoder.object<CustomFilter>(
  {
    name: JsonDecoder.string,
    criterias: JsonDecoder.array<Criteria>(
      JsonDecoder.object<Criteria>(
        {
          name: JsonDecoder.string,
          objectType: JsonDecoder.optional(JsonDecoder.string),
          value: JsonDecoder.oneOf<string | boolean | Array<CriteriaIdValue>>(
            [
              JsonDecoder.string,
              JsonDecoder.boolean,
              JsonDecoder.array<CriteriaIdValue>(
                JsonDecoder.object<CriteriaIdValue>(
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
): Promise<ListingModel<CustomFilter>> =>
  getData<ListingModel<CustomFilter>>(cancelToken)(
    buildListCustomFiltersEndpoint(params),
  );

export {
  listCustomFilters,
  buildListCustomFiltersEndpoint,
  listCustomFiltersDecoder,
};
