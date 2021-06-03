import { JsonDecoder } from 'ts.data.json';

import { buildListingDecoder, SelectEntry } from '@centreon/ui';

import { Filter } from '../models';
import { Criteria } from '../Criterias/models';
import { SortOrder } from '../../models';

const entityDecoder = JsonDecoder.object<Filter>(
  {
    criterias: JsonDecoder.array<Criteria>(
      JsonDecoder.object<Criteria>(
        {
          name: JsonDecoder.string,
          object_type: JsonDecoder.nullable(JsonDecoder.string),
          type: JsonDecoder.string,
          value: JsonDecoder.optional(
            JsonDecoder.oneOf<
              | string
              | Array<Pick<SelectEntry, 'id' | 'name'>>
              | [string, SortOrder]
            >(
              [
                JsonDecoder.string,
                JsonDecoder.array<Pick<SelectEntry, 'id' | 'name'>>(
                  JsonDecoder.object<Pick<SelectEntry, 'id' | 'name'>>(
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
    id: JsonDecoder.number,
    name: JsonDecoder.string,
  },
  'CustomFilter',
);

const listCustomFiltersDecoder = buildListingDecoder<Filter>({
  entityDecoder,
  entityDecoderName: 'CustomFilter',
  listingDecoderName: 'CustomFilters',
});

export { listCustomFiltersDecoder };
