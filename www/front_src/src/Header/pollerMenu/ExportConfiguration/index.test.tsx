import * as React from 'react';

import axios from 'axios';
import { render, waitFor, RenderResult, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';

import { withSnackbar } from '@centreon/ui';

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

const ExportConfigurationWithSnackbar = withSnackbar({
  Component: ExportConfiguration,
});

const renderExportConfiguration = (): RenderResult =>
  render(
    <ExportConfigurationWithSnackbar setIsExportingConfiguration={jest.fn} />,
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

    expect(
      screen.getByText(labelExportingAndReloadingTheConfiguration),
    ).toBeInTheDocument();
    expect(
      screen.getByText(labelConfigurationExportedAndReloaded),
    ).toBeInTheDocument();
  });
});
