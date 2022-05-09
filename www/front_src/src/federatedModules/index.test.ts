import { renderHook, act } from '@testing-library/react-hooks';
import axios from 'axios';
import { waitFor } from '@testing-library/dom';

import usePlatformVersions from '../Main/usePlatformVersions';

import useFederatedComponents from './useFederatedModules';
import { retrievedFederatedComponent } from './mocks';

const mockedAxios = axios as jest.Mocked<typeof axios>;

const retrievedWebVersions = {
  modules: {
    'centreon-bam-server': {
      version: '1.0.0',
    },
  },
  web: {
    version: '21.10.1',
  },
};

describe('external components', () => {
  beforeEach(() => {
    mockedAxios.get.mockReset();
    mockedAxios.get
      .mockResolvedValueOnce({ data: retrievedWebVersions })
      .mockResolvedValue({ data: retrievedFederatedComponent });
  });
  it('populates the federated components atom with the data retrieved from the API', async () => {
    const { result } = renderHook(() => ({
      ...useFederatedComponents(),
      ...usePlatformVersions(),
    }));

    expect(result.current.federatedComponents).toEqual(null);

    act(() => {
      result.current.getPlatformVersions();
    });

    await waitFor(() => {
      expect(result.current.getModules()).toEqual(['centreon-bam-server']);
    });

    act(() => {
      result.current.getFederatedComponents();
    });

    await waitFor(() => {
      expect(result.current.federatedComponents).toEqual([
        retrievedFederatedComponent,
      ]);
    });
  });
});
