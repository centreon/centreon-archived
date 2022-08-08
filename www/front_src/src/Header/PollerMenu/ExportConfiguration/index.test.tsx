import axios from 'axios';
import userEvent from '@testing-library/user-event';
import { Provider } from 'jotai';

import {
  render,
  waitFor,
  RenderResult,
  screen,
  SnackbarProvider,
} from '@centreon/ui';
import { refreshIntervalAtom, userAtom } from '@centreon/ui-context';

import { cancelTokenRequestParam } from '../../../Resources/testUtils';
import {
  labelConfigurationExportedAndReloaded,
  labelExportAndReload,
  labelExportConfiguration,
  labelExportingAndReloadingTheConfiguration,
} from '../translatedLabels';

import { exportAndReloadConfigurationEndpoint } from './api/endpoints';

import ExportConfiguration from '.';

const mockedAxios = axios as jest.Mocked<typeof axios>;

const mockUser = {
  isExportButtonEnabled: true,
  locale: 'en',
  timezone: 'Europe/Paris',
};
const mockRefreshInterval = 60;
const toggleDetailedView = jest.fn();

const ExportConfigurationButton = (): JSX.Element => (
  <ExportConfiguration
    setIsExportingConfiguration={jest.fn}
    toggleDetailedView={toggleDetailedView}
  />
);

const renderExportConfiguration = (): RenderResult =>
  render(
    <SnackbarProvider maxSnackbars={2}>
      <Provider
        initialValues={[
          [userAtom, mockUser],
          [refreshIntervalAtom, mockRefreshInterval],
        ]}
      >
        <ExportConfigurationButton />
      </Provider>
    </SnackbarProvider>,
  );

describe(ExportConfiguration, () => {
  beforeEach(() => {
    mockedAxios.get.mockReset();
    mockedAxios.get.mockResolvedValueOnce({
      data: {},
    });
  });

  it('exports and reloads the configuration when the "Export the configuration" button is clicked', async () => {
    renderExportConfiguration();

    userEvent.click(screen.getByText(labelExportConfiguration));

    userEvent.click(screen.getByText(labelExportAndReload));

    await waitFor(() => {
      expect(mockedAxios.get).toHaveBeenCalledWith(
        exportAndReloadConfigurationEndpoint,
        cancelTokenRequestParam,
      );
    });

    expect(toggleDetailedView).toHaveBeenCalled();

    expect(
      screen.getByText(labelExportingAndReloadingTheConfiguration),
    ).toBeInTheDocument();

    await waitFor(() => {
      expect(
        screen.getByText(labelConfigurationExportedAndReloaded),
      ).toBeInTheDocument();
    });
  });
});
