/* eslint-disable @typescript-eslint/ban-ts-ignore */

const mockAxios = jest.genMockFromModule('axios');

// @ts-ignore
mockAxios.create = jest.fn(() => mockAxios);

// @ts-ignore
mockAxios.CancelToken = {
  source: () => ({
    token: {},
    cancel: jest.fn(),
  }),
};

export default mockAxios;
