import { menuSelector } from './menu';

describe('menuSelector', () => {
  it('returns formatted menu', () => {
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
                        label: 'Services by host',
                        options: null,
                        page: '60201',
                        show: true,
                        url: 'serviceByHost.php',
                      },
                      {
                        is_react: false,
                        label: 'Hidden entry',
                        options: null,
                        page: '60202',
                        show: false,
                        url: 'hiddenEntry.php',
                      },
                    ],
                    label: 'Main Menu',
                  },
                ],
                is_react: false,
                label: 'Services',
                options: null,
                page: '602',
                show: true,
                url: null,
              },
            ],
            is_react: false,
            label: 'Configuration',
            options: null,
            page: '6',
            show: true,
            url: './configuration.php',
          },
        ],
      },
    };

    const expectedState = [...state.navigation.items];
    expectedState[0].children[0].groups[0].children.pop(); // remove hidden entry

    const menu = menuSelector(state);

    expect(menu).toEqual(expectedState);
  });
});
