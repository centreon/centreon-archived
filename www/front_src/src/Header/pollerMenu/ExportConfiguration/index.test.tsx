import * as React from 'react';

import axios from 'axios';
import { render, waitFor, RenderResult, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';

import { withSnackbar } from '@centreon/ui';
import { useUserContext } from '@centreon/ui-context';

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

jest.mock('@centreon/centreon-frontend/packages/ui-context', () => ({
  ...(jest.requireActual('@centreon/ui-context') as jest.Mocked<unknown>),
  useUserContext: jest.fn(),
}));

const mockedUserContext = useUserContext as jest.Mock;

const mockUserContext = {
  isExportButtonEnabled: true,
  locale: 'en',
  refreshInterval: 60,
  timezone: 'Europe/Paris',
};

const ExportConfigurationButton = (): JSX.Element => (
  <ExportConfiguration setIsExportingConfiguration={jest.fn} />
);

const ExportConfigurationWithSnackbar = withSnackbar({
  Component: ExportConfigurationButton,
});

const renderExportConfiguration = (): RenderResult =>
  render(<ExportConfigurationWithSnackbar />);

describe(ExportConfiguration, () => {
  beforeEach(() => {
    mockedUserContext.mockReturnValue(mockUserContext);

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

    expect(
      screen.getByText(labelExportingAndReloadingTheConfiguration),
    ).toBeInTheDocument();
    expect(
      screen.getByText(labelConfigurationExportedAndReloaded),
    ).toBeInTheDocument();
  });
});
