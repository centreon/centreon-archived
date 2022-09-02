/* eslint-disable @typescript-eslint/naming-convention */
import Navigation from './models';

export const retrievedNavigationWithAnEmptySet: Navigation = {
  result: [],
  status: true,
};

export const retrievedNavigation: Navigation = {
  result: [
    {
      children: [
        {
          groups: [],
          is_react: true,
          label: 'Resources Status',
          options: null,
          page: '200',
          show: true,
          url: '/monitoring/resources',
        },
        {
          groups: [
            {
              children: [
                {
                  is_react: false,
                  label: 'Services Grid',
                  options: '&o=svcOV_pb',
                  page: '20204',
                  show: true,
                  url: './include/monitoring/status/monitoringService.php',
                },
                {
                  is_react: false,
                  label: 'Services by Hostgroup',
                  options: '&o=svcOVHG_pb',
                  page: '20209',
                  show: true,
                  url: './include/monitoring/status/monitoringService.php',
                },
              ],
              label: 'By Status',
            },
          ],
          is_react: false,
          label: 'Status Details',
          options: null,
          page: '202',
          show: true,
          url: null,
        },
      ],
      color: '85B446',
      icon: 'monitoring',
      is_react: false,
      label: 'Monitoring',
      menu_id: 'Monitoring',
      options: '',
      page: '2',
      show: true,
      url: null,
    },
    {
      children: [
        {
          groups: [
            {
              children: [
                {
                  is_react: false,
                  label: 'Hosts',
                  options: null,
                  page: '60101',
                  show: true,
                  url: './include/configuration/configObject/host/host.php',
                },
                {
                  is_react: false,
                  label: 'Host Groups',
                  options: null,
                  page: '60102',
                  show: true,
                  url: './include/configuration/configObject/hostgroup/hostGroup.php',
                },
                {
                  is_react: false,
                  label: 'Templates',
                  options: null,
                  page: '60103',
                  show: true,
                  url: './include/configuration/configObject/host_template_model/hostTemplateModel.php',
                },
                {
                  is_react: false,
                  label: 'Categories',
                  options: null,
                  page: '60104',
                  show: true,
                  url: './include/configuration/configObject/host_categories/hostCategories.php',
                },
              ],
              label: 'Hosts',
            },
          ],
          is_react: false,
          label: 'Hosts',
          options: null,
          page: '601',
          show: true,
          url: null,
        },
        {
          groups: [
            {
              children: [
                {
                  is_react: false,
                  label: 'Escalations',
                  options: null,
                  page: '60401',
                  show: true,
                  url: './include/configuration/configObject/escalation/escalation.php',
                },
              ],
              label: 'Escalations',
            },
            {
              children: [
                {
                  is_react: false,
                  label: 'Hosts',
                  options: null,
                  page: '60407',
                  show: true,
                  url: './include/configuration/configObject/host_dependency/hostDependency.php',
                },
                {
                  is_react: false,
                  label: 'Host Groups',
                  options: null,
                  page: '60408',
                  show: true,
                  url: './include/configuration/configObject/hostgroup_dependency/hostGroupDependency.php',
                },
                {
                  is_react: false,
                  label: 'Services',
                  options: null,
                  page: '60409',
                  show: true,
                  url: './include/configuration/configObject/service_dependency/serviceDependency.php',
                },
                {
                  is_react: false,
                  label: 'Service Groups',
                  options: null,
                  page: '60410',
                  show: true,
                  url: './include/configuration/configObject/servicegroup_dependency/serviceGroupDependency.php',
                },
                {
                  is_react: false,
                  label: 'Meta Services',
                  options: null,
                  page: '60411',
                  show: true,
                  url: './include/configuration/configObject/metaservice_dependency/MetaServiceDependency.php',
                },
              ],
              label: 'Dependencies',
            },
          ],
          is_react: false,
          label: 'Notifications',
          options: null,
          page: '604',
          show: true,
          url: null,
        },
      ],
      color: '319ED5',
      icon: 'configuration',
      is_react: false,
      label: 'Configuration',
      menu_id: 'Configuration',
      options: null,
      page: '6',
      show: true,
      url: null,
    },
  ],
  status: true,
};

export const allowedPages = [
  '/monitoring/resources',
  '20204',
  '20209',
  '202',
  '2',
  '60101',
  '60102',
  '60103',
  '60104',
  '601',
  '60401',
  '60407',
  '60408',
  '60409',
  '60410',
  '60411',
  '604',
  '6',
];

export const reactRoutes = {
  '/monitoring/resources': '200',
};
