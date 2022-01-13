import { renderHook, act } from '@testing-library/react-hooks';
import axios from 'axios';
import { waitFor } from '@testing-library/dom';

import useExternalComponents from './useExternalComponents';
import ExternalComponents from './models';

const mockedAxios = axios as jest.Mocked<typeof axios>;

const retrievedExternalComponents: ExternalComponents = {
  hooks: {
    '/bam/topcounter': {
      css: [],
      js: {
        bundle: './bundle.js',
        chunks: ['chunk.js'],
        commons: ['vendors.js', 'common.js'],
      },
    },
  },
  pages: {
    '/bam/page': {
      css: [],
      js: {
        bundle: './bundle.js',
        chunks: ['chunk.js'],
        commons: ['vendors.js', 'common.js'],
      },
    },
  },
};

describe('external components', () => {
  beforeEach(() => {
    mockedAxios.get.mockReset();
    mockedAxios.get.mockResolvedValue({ data: retrievedExternalComponents });
  });
  it('populates the external components atom with the data retrieved from the API', async () => {
    const { result } = renderHook(() => useExternalComponents());

    expect(result.current.externalComponents).toEqual(null);

    act(() => {
      result.current.getExternalComponents();
    });

    await waitFor(() => {
      expect(result.current.externalComponents).toEqual(
        retrievedExternalComponents,
      );
    });
  });
});
