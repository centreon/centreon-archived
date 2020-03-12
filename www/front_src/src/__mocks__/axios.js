const mockAxios = jest.genMockFromModule('axios');

mockAxios.create = jest.fn(() => mockAxios);

mockAxios.CancelToken = {
  // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
  source: () => ({
    token: {},
    cancel: jest.fn(),
  }),
};

export default mockAxios;
