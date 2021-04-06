import { reactRoutesSelector } from './reactRoutes';

describe('reactRoutesSelector', () => {
  it('returns react routes with their topology page', () => {
    const state = {
      navigation: {
        items: [
          {
            children: [
              {
                groups: [],
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

    const reactRoutes = reactRoutesSelector(state);

    expect(reactRoutes).toEqual({
      '/home/customViews': '103',
    });
  });
});
