import { renderHook, act } from '@testing-library/react-hooks';
import axios from 'axios';
import { waitFor } from '@testing-library/dom';

import useExternalComponents from './useExternalComponents';
import { retrievedExternalComponents } from './mocks';

const mockedAxios = axios as jest.Mocked<typeof axios>;

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
