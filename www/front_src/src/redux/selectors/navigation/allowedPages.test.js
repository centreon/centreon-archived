import { allowedPagesSelector } from './allowedPages';

describe('allowedPagesSelector', () => {
  it('returns allowed react routes and legacy topology pages', () => {
    const state = {
      navigation: {
        items: [
          {
            children: [
              {
                groups: [
                  {
                    children: [
                      {
                        is_react: false,
                        label: 'Custom view page 1',
                        options: null,
                        page: '10301',
                        show: true,
                        url: 'page1.php',
                      },
                      {
                        is_react: true,
                        label: 'Custom view page 2',
                        options: null,
                        page: '10302',
                        show: false,
                        url: '/home/customViews/2',
                      },
                    ],
                    label: 'Main Menu',
                  },
                ],
                is_react: true,
                label: 'Custom Views',
                options: null,
                page: '103',
                url: '/home/customViews',
              },
            ],
            is_react: false,
            label: 'Home',
            options: null,
            page: '1',
            url: './include/home/home.php',
          },
        ],
      },
    };

    const reactRoutes = allowedPagesSelector(state);

    expect(reactRoutes).toEqual([
      '10301',
      '/home/customViews/2',
      '/home/customViews',
      '1',
    ]);
  });
});
