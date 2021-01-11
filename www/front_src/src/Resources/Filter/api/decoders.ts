import { JsonDecoder } from 'ts.data.json';

import { buildListingDecoder } from '@centreon/ui';

import { CriteriaValue, RawCriteria, RawFilter } from '../models';
import { SortOrder } from '../../Listing/models';

const entityDecoder = JsonDecoder.object<RawFilter>(
  {
    id: JsonDecoder.number,
    name: JsonDecoder.string,
    criterias: JsonDecoder.array<RawCriteria>(
      JsonDecoder.object<RawCriteria>(
        {
          name: JsonDecoder.string,
          object_type: JsonDecoder.optional(JsonDecoder.string),
          type: JsonDecoder.string,
          value: JsonDecoder.optional(
            JsonDecoder.oneOf<
              string | boolean | Array<CriteriaValue> | [string, SortOrder]
            >(
              [
                JsonDecoder.string,
                JsonDecoder.boolean,
                JsonDecoder.array<CriteriaValue>(
                  JsonDecoder.object<CriteriaValue>(
                    {
                      id: JsonDecoder.oneOf<number | string>(
                        [JsonDecoder.number, JsonDecoder.string],
                        'FilterCriteriaMultiSelectId',
                      ),
                      name: JsonDecoder.string,
                    },
                    'FilterCriteriaMultiSelectValue',
                  ),
                  'FilterCriteriaValues',
                ),
                JsonDecoder.tuple(
                  [
                    JsonDecoder.string,
                    JsonDecoder.oneOf<'asc' | 'desc'>(
                      [
                        JsonDecoder.isExactly('asc'),
                        JsonDecoder.isExactly('desc'),
                      ],
                      'FilterCriteriaSortOrder',
                    ),
                  ],
                  'FilterCriteriaTuple',
                ),
              ],
              'FilterCriteriaValue',
            ),
          ),
        },
        'FilterCriterias',
      ),
      'FilterCriterias',
    ),
  },
  'CustomFilter',
);

const listCustomFiltersDecoder = buildListingDecoder<RawFilter>({
  entityDecoder,
  entityDecoderName: 'CustomFilter',
  listingDecoderName: 'CustomFilters',
});

export { listCustomFiltersDecoder };
