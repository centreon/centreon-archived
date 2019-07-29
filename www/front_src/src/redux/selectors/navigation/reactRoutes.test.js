import { reactRoutesSelector } from './menu';

describe('reactRoutesSelector', () => {
  it('returns react routes with their topology page', () => {
    const state = {
      navigation: {
        menuItems: [
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

    const reactRoutes = breadcrumbsSelector(state);

    expect(reactRoutes).toEqual({
      '/home/customViews': '103',
    });
  });
});