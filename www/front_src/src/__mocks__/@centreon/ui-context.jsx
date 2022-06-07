jest.mock('@centreon/ui-context', () => ({
  ...jest.requireActual('centreon-frontend/packages/ui-context'),
  ThemeMode: {
    light: 'light',
  },
}));
