import { allowedPagesSelector } from './allowedPages';

describe('allowedPagesSelector', () => {
  it('returns allowed react routes and legacy topology pages', () => {
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
                groups: [
                  {
                    label: 'Main Menu',
                    children: [
                      {
                        page: '10301',
                        label: 'Custom view page 1',
                        is_react: false,
                        show: true,
                        url: 'page1.php',
                        options: null,
                      },
                      {
                        page: '10302',
                        label: 'Custom view page 2',
                        is_react: true,
                        show: false,
                        url: '/home/customViews/2',
                        options: null,
                      },
                    ]
                  }
                ],
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

    const reactRoutes = allowedPagesSelector(state);

    expect(reactRoutes).toEqual(['10301', '/home/customViews/2', '/home/customViews', '1']);
  });
});