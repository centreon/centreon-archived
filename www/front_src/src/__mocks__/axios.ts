/* eslint-disable @typescript-eslint/ban-ts-ignore */

const mockAxios = jest.genMockFromModule('axios');

// @ts-ignore
mockAxios.create = jest.fn(() => mockAxios);

// @ts-ignore
mockAxios.CancelToken = {
  // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
  source: () => ({
    token: {},
    cancel: jest.fn(),
  }),
};

export default mockAxios;
