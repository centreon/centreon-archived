import { JsonDecoder } from 'ts.data.json';

import {
  Icon,
  Notes,
  Parent,
  Resource,
  ResourceEndpoints,
  ResourceExternals,
  ResourceLinks,
  ResourceType,
  ResourceUris,
  Status,
} from './models';

const statusDecoder = JsonDecoder.object<Status>(
  {
    severity_code: JsonDecoder.number,
    name: JsonDecoder.string,
  },
  'Status',
);

const commonDecoders = {
  status: JsonDecoder.optional(statusDecoder),
  id: JsonDecoder.number,
  uuid: JsonDecoder.string,
  name: JsonDecoder.string,
  type: JsonDecoder.oneOf<ResourceType>(
    [
      JsonDecoder.isExactly('host'),
      JsonDecoder.isExactly('metaservice'),
      JsonDecoder.isExactly('service'),
    ],
    'ResourceType',
  ),
  icon: JsonDecoder.optional(
    JsonDecoder.object<Icon>(
      {
        name: JsonDecoder.string,
        url: JsonDecoder.string,
      },
      'ResourceIcon',
    ),
  ),
};

const resourceDecoder = JsonDecoder.object<Resource>(
  {
    ...commonDecoders,
    parent: JsonDecoder.optional(
      JsonDecoder.object<Parent>(
        {
          ...commonDecoders,
          links: JsonDecoder.optional(
            JsonDecoder.object<Pick<ResourceLinks, 'uris'>>(
              {
                uris: JsonDecoder.object<ResourceUris>(
                  {
                    configuration: JsonDecoder.optional(JsonDecoder.string),
                    logs: JsonDecoder.optional(JsonDecoder.string),
                    reporting: JsonDecoder.optional(JsonDecoder.string),
                  },
                  'ResourceParentLinksUris',
                ),
              },
              'ResourceParentLinks',
            ),
          ),
        },
        'ResourceParent',
      ),
    ),

    links: JsonDecoder.optional(
      JsonDecoder.object<ResourceLinks>(
        {
          endpoints: JsonDecoder.object<ResourceEndpoints>(
            {
              details: JsonDecoder.optional(JsonDecoder.string),
              performance_graph: JsonDecoder.optional(JsonDecoder.string),
              status_graph: JsonDecoder.optional(JsonDecoder.string),
              timeline: JsonDecoder.optional(JsonDecoder.string),
              acknowledgement: JsonDecoder.optional(JsonDecoder.string),
              downtime: JsonDecoder.optional(JsonDecoder.string),
              metrics: JsonDecoder.optional(JsonDecoder.string),
            },
            'ResourceLinksEndpoints',
          ),
          uris: JsonDecoder.object<ResourceUris>(
            {
              configuration: JsonDecoder.optional(JsonDecoder.string),
              logs: JsonDecoder.optional(JsonDecoder.string),
              reporting: JsonDecoder.optional(JsonDecoder.string),
            },
            'ResourceLinksUris',
          ),
          externals: JsonDecoder.object<ResourceExternals>(
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
        },
        'ResourceLinks',
      ),
    ),
    acknowledged: JsonDecoder.optional(JsonDecoder.boolean),
    in_downtime: JsonDecoder.optional(JsonDecoder.boolean),
    duration: JsonDecoder.optional(JsonDecoder.string),
    tries: JsonDecoder.optional(JsonDecoder.string),
    last_check: JsonDecoder.optional(JsonDecoder.string),
    information: JsonDecoder.optional(JsonDecoder.string),
    severity_level: JsonDecoder.optional(JsonDecoder.number),
    passive_checks: JsonDecoder.optional(JsonDecoder.boolean),
    short_type: JsonDecoder.oneOf(
      [
        JsonDecoder.isExactly('h'),
        JsonDecoder.isExactly('m'),
        JsonDecoder.isExactly('s'),
      ],
      'ResourceShortType',
    ),
  },
  'Resource',
);

export { statusDecoder, resourceDecoder };
