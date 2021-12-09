import { renderHook, act } from '@testing-library/react-hooks';
import axios from 'axios';
import { waitFor } from '@testing-library/dom';

import useNavigation from './useNavigation';
import { retrievedNavigation, allowedPages, reactRoutes } from './mocks';

const mockedAxios = axios as jest.Mocked<typeof axios>;

describe('navigation', () => {
  beforeEach(() => {
    mockedAxios.get.mockReset();
    mockedAxios.get.mockResolvedValue({ data: retrievedNavigation });
  });
  it('gets the allowed pages with the navigation data retrieved from the API', async () => {
    const { result } = renderHook(() => useNavigation());

    act(() => {
      result.current.getNavigation();
    });

    await waitFor(() => {
      expect(result.current.allowedPages).toEqual(allowedPages);
    });
  });

  it('gets the menu with the navigation data retrieved from the API', async () => {
    const { result } = renderHook(() => useNavigation());

    act(() => {
      result.current.getNavigation();
    });

    await waitFor(() => {
      expect(result.current.menu).toEqual(retrievedNavigation.result);
    });
  });

  it('gets the react routes with the navigation data retrieved from the API', async () => {
    const { result } = renderHook(() => useNavigation());

    act(() => {
      result.current.getNavigation();
    });

    await waitFor(() => {
      expect(result.current.reactRoutes).toEqual(reactRoutes);
    });
  });
});
