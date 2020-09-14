import { JsonDecoder } from 'ts.data.json';

import { buildListingDecoder } from '@centreon/ui';

import { CriteriaValue, RawCriteria, RawFilter } from '../models';

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
            JsonDecoder.oneOf<string | boolean | Array<CriteriaValue>>(
              [
                JsonDecoder.string,
                JsonDecoder.boolean,
                JsonDecoder.array<CriteriaValue>(
                  JsonDecoder.object<CriteriaValue>(
                    {
                      id: JsonDecoder.oneOf<number | string>(
                        [JsonDecoder.number, JsonDecoder.string],
                        'FilterCriteriaMilteSelectId',
                      ),
                      name: JsonDecoder.string,
                    },
                    'FilterCriteriaMultiSelectValue',
                  ),
                  'FilterCriteriaValues',
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
