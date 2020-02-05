import breadcrumbsSelector from './breadcrumbs';

describe('breadcrumbsSelector', () => {
  it('returns formatted breadcrumbs', () => {
    const state = {
      navigation: {
        items: [
          {
            page: '1',
            label: 'Home',
            is_react: false,
            url: './include/home/home.php',
            options: null,
            children: [
              {
                groups: [],
                page: '103',
                label: 'Custom Views',
                is_react: true,
                url: '/home/customViews',
                options: null,
              },
            ],
          },
        ],
      },
    };
    const breadcrumbs = breadcrumbsSelector(state);

    expect(breadcrumbs).toEqual({
      '/main.php?p=1': [{ label: 'Home', link: '/main.php?p=1' }],
      '/home/customViews': [
        { label: 'Home', link: '/main.php?p=1' },
        { label: 'Custom Views', link: '/home/customViews' },
      ],
    });
  });

  it('returns first url found in parent level', () => {
    const state = {
      navigation: {
        items: [
          {
            page: '2',
            label: 'Configuration',
            is_react: false,
            url: './include/home/home.php',
            options: null,
            children: [
              {
                page: '201',
                label: 'Hosts',
                is_react: false,
                url: null,
                options: null,
                groups: [
                  {
                    label: 'hosts',
                    children: [
                      {
                        page: '20101',
                        label: 'Hosts',
                        is_react: true,
                        url: '/configuration/hosts',
                        options: null,
                      },
                    ],
                  },
                ],
              },
              {
                page: '202',
                label: 'Services',
                is_react: false,
                url: null,
                options: null,
                groups: [
                  {
                    label: 'services',
                    children: [
                      {
                        page: '20102',
                        label: 'Services',
                        is_react: true,
                        url: '/configuration/services',
                        options: null,
                      },
                    ],
                  },
                ],
              },
            ],
          },
        ],
      },
    };
    const breadcrumbs = breadcrumbsSelector(state);

    expect(breadcrumbs).toEqual({
      '/main.php?p=2': [{ label: 'Configuration', link: '/main.php?p=2' }],
      '/configuration/hosts': [
        { label: 'Configuration', link: '/main.php?p=2' },
        { label: 'Hosts', link: '/configuration/hosts' },
        { label: 'Hosts', link: '/configuration/hosts' },
      ],
      '/configuration/services': [
        { label: 'Configuration', link: '/main.php?p=2' },
        { label: 'Services', link: '/configuration/services' },
        { label: 'Services', link: '/configuration/services' },
      ],
    });
  });
});
