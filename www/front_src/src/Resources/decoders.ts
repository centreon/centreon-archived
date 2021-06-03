import { JsonDecoder } from 'ts.data.json';

import {
  ResourceCalculationMethod,
  Icon,
  Notes,
  Parent,
  Resource,
  ResourceAdditionals,
  ResourceEndpoints,
  ResourceExternals,
  ResourceLinks,
  ResourceShortType,
  ResourceType,
  ResourceUris,
  Status,
} from './models';

const statusDecoder = JsonDecoder.object<Status>(
  {
    name: JsonDecoder.string,
    severity_code: JsonDecoder.number,
  },
  'Status',
);

const commonDecoders = {
  acknowledged: JsonDecoder.optional(JsonDecoder.boolean),
  active_checks: JsonDecoder.optional(JsonDecoder.boolean),
  additionals: JsonDecoder.optional(
    JsonDecoder.object<ResourceAdditionals>(
      {
        calculation_method: JsonDecoder.enumeration<ResourceCalculationMethod>(
          ResourceCalculationMethod,
          'Resource Calculation Method',
        ),
        calculation_ratio_mode: JsonDecoder.optional(JsonDecoder.string),
        health: JsonDecoder.optional(JsonDecoder.number),
      },
      'ResourceAdditionals',
    ),
  ),
  duration: JsonDecoder.optional(JsonDecoder.string),
  icon: JsonDecoder.optional(
    JsonDecoder.object<Icon>(
      {
        name: JsonDecoder.string,
        url: JsonDecoder.string,
      },
      'ResourceIcon',
    ),
  ),
  id: JsonDecoder.number,
  in_downtime: JsonDecoder.optional(JsonDecoder.boolean),
  information: JsonDecoder.optional(JsonDecoder.string),
  last_check: JsonDecoder.optional(JsonDecoder.string),
  links: JsonDecoder.optional(
    JsonDecoder.object<ResourceLinks>(
      {
        endpoints: JsonDecoder.object<ResourceEndpoints>(
          {
            acknowledgement: JsonDecoder.optional(JsonDecoder.string),
            details: JsonDecoder.optional(JsonDecoder.string),
            downtime: JsonDecoder.optional(JsonDecoder.string),
            metrics: JsonDecoder.optional(JsonDecoder.string),
            performance_graph: JsonDecoder.optional(JsonDecoder.string),
            status_graph: JsonDecoder.optional(JsonDecoder.string),
            timeline: JsonDecoder.optional(JsonDecoder.string),
          },
          'ResourceLinksEndpoints',
        ),
        externals: JsonDecoder.optional(
          JsonDecoder.object<ResourceExternals>(
            {
              action_url: JsonDecoder.optional(JsonDecoder.string),
              notes: JsonDecoder.optional(
                JsonDecoder.object<Notes>(
                  {
                    label: JsonDecoder.optional(JsonDecoder.string),
                    url: JsonDecoder.string,
                  },
                  'ResourceLinksExternalNotes',
                ),
              ),
            },
            'ResourceLinksExternals',
          ),
        ),
        uris: JsonDecoder.object<ResourceUris>(
          {
            configuration: JsonDecoder.optional(JsonDecoder.string),
            logs: JsonDecoder.optional(JsonDecoder.string),
            reporting: JsonDecoder.optional(JsonDecoder.string),
          },
          'ResourceLinksUris',
        ),
      },
      'ResourceLinks',
    ),
  ),
  name: JsonDecoder.string,
  notification_enabled: JsonDecoder.optional(JsonDecoder.boolean),
  passive_checks: JsonDecoder.optional(JsonDecoder.boolean),
  severity_level: JsonDecoder.optional(JsonDecoder.number),
  short_type: JsonDecoder.oneOf<ResourceShortType>(
    [
      JsonDecoder.isExactly('h'),
      JsonDecoder.isExactly('m'),
      JsonDecoder.isExactly('s'),
      JsonDecoder.isExactly('ba'),
    ],
    'ResourceShortType',
  ),
  status: JsonDecoder.optional(statusDecoder),
  tries: JsonDecoder.optional(JsonDecoder.string),
  type: JsonDecoder.oneOf<ResourceType>(
    [
      JsonDecoder.isExactly('host'),
      JsonDecoder.isExactly('metaservice'),
      JsonDecoder.isExactly('service'),
      JsonDecoder.isExactly('business-activity'),
    ],
    'ResourceType',
  ),
  uuid: JsonDecoder.string,
};

const resourceDecoder = JsonDecoder.object<Resource>(
  {
    ...commonDecoders,
    parent: JsonDecoder.optional(
      JsonDecoder.object<Parent>(commonDecoders, 'ResourceParent'),
    ),
  },
  'Resource',
);

export { statusDecoder, resourceDecoder };
